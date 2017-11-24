<?php











namespace Composer;

use Composer\Autoload\AutoloadGenerator;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\PolicyInterface;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Rule;
use Composer\DependencyResolver\Solver;
use Composer\DependencyResolver\SolverProblemsException;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer\InstallationManager;
use Composer\Installer\InstallerEvents;
use Composer\Installer\NoopInstaller;
use Composer\Installer\SuggestedPackagesReporter;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\CompletePackage;
use Composer\Package\Link;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Semver\Constraint\Constraint;
use Composer\Package\Locker;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledArrayRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Script\ScriptEvents;







class Installer
{



protected $io;




protected $config;




protected $package;




protected $downloadManager;




protected $repositoryManager;




protected $locker;




protected $installationManager;




protected $eventDispatcher;




protected $autoloadGenerator;

protected $preferSource = false;
protected $preferDist = false;
protected $optimizeAutoloader = false;
protected $classMapAuthoritative = false;
protected $apcuAutoloader = false;
protected $devMode = false;
protected $dryRun = false;
protected $verbose = false;
protected $update = false;
protected $dumpAutoloader = true;
protected $runScripts = true;
protected $ignorePlatformReqs = false;
protected $preferStable = false;
protected $preferLowest = false;
protected $skipSuggest = false;
protected $writeLock = true;
protected $executeOperations = true;






protected $updateWhitelist = null;
protected $whitelistDependencies = false;




protected $suggestedPackagesReporter;




protected $additionalInstalledRepository;














public function __construct(IOInterface $io, Config $config, RootPackageInterface $package, DownloadManager $downloadManager, RepositoryManager $repositoryManager, Locker $locker, InstallationManager $installationManager, EventDispatcher $eventDispatcher, AutoloadGenerator $autoloadGenerator)
{
$this->io = $io;
$this->config = $config;
$this->package = $package;
$this->downloadManager = $downloadManager;
$this->repositoryManager = $repositoryManager;
$this->locker = $locker;
$this->installationManager = $installationManager;
$this->eventDispatcher = $eventDispatcher;
$this->autoloadGenerator = $autoloadGenerator;
}







public function run()
{

 
 
 
 gc_collect_cycles();
gc_disable();


 if (!$this->update && !$this->locker->isLocked()) {
$this->update = true;
}

if ($this->dryRun) {
$this->verbose = true;
$this->runScripts = false;
$this->executeOperations = false;
$this->writeLock = false;
$this->dumpAutoloader = false;
$this->installationManager->addInstaller(new NoopInstaller);
$this->mockLocalRepositories($this->repositoryManager);
}

if ($this->runScripts) {

 $eventName = $this->update ? ScriptEvents::PRE_UPDATE_CMD : ScriptEvents::PRE_INSTALL_CMD;
$this->eventDispatcher->dispatchScript($eventName, $this->devMode);
}

$this->downloadManager->setPreferSource($this->preferSource);
$this->downloadManager->setPreferDist($this->preferDist);


 $localRepo = $this->repositoryManager->getLocalRepository();
if ($this->update) {
$platformOverrides = $this->config->get('platform') ?: array();
} else {
$platformOverrides = $this->locker->getPlatformOverrides();
}
$platformRepo = new PlatformRepository(array(), $platformOverrides);
$installedRepo = $this->createInstalledRepo($localRepo, $platformRepo);

$aliases = $this->getRootAliases();
$this->aliasPlatformPackages($platformRepo, $aliases);

if (!$this->suggestedPackagesReporter) {
$this->suggestedPackagesReporter = new SuggestedPackagesReporter($this->io);
}

try {
list($res, $devPackages) = $this->doInstall($localRepo, $installedRepo, $platformRepo, $aliases);
if ($res !== 0) {
return $res;
}
} catch (\Exception $e) {
if ($this->executeOperations) {
$this->installationManager->notifyInstalls($this->io);
}

throw $e;
}
if ($this->executeOperations) {
$this->installationManager->notifyInstalls($this->io);
}


 if ($this->devMode && !$this->skipSuggest) {
$this->suggestedPackagesReporter->output($installedRepo);
}


 foreach ($localRepo->getPackages() as $package) {
if (!$package instanceof CompletePackage || !$package->isAbandoned()) {
continue;
}

$replacement = (is_string($package->getReplacementPackage()))
? 'Use ' . $package->getReplacementPackage() . ' instead'
: 'No replacement was suggested';

$this->io->writeError(
sprintf(
"<warning>Package %s is abandoned, you should avoid using it. %s.</warning>",
$package->getPrettyName(),
$replacement
)
);
}


 if ($this->update && $this->writeLock) {
$localRepo->reload();

$platformReqs = $this->extractPlatformRequirements($this->package->getRequires());
$platformDevReqs = $this->extractPlatformRequirements($this->package->getDevRequires());

$updatedLock = $this->locker->setLockData(
array_diff($localRepo->getCanonicalPackages(), $devPackages),
$devPackages,
$platformReqs,
$platformDevReqs,
$aliases,
$this->package->getMinimumStability(),
$this->package->getStabilityFlags(),
$this->preferStable || $this->package->getPreferStable(),
$this->preferLowest,
$this->config->get('platform') ?: array()
);
if ($updatedLock) {
$this->io->writeError('<info>Writing lock file</info>');
}
}

if ($this->dumpAutoloader) {

 if ($this->optimizeAutoloader) {
$this->io->writeError('<info>Generating optimized autoload files</info>');
} else {
$this->io->writeError('<info>Generating autoload files</info>');
}

$this->autoloadGenerator->setDevMode($this->devMode);
$this->autoloadGenerator->setClassMapAuthoritative($this->classMapAuthoritative);
$this->autoloadGenerator->setApcu($this->apcuAutoloader);
$this->autoloadGenerator->setRunScripts($this->runScripts);
$this->autoloadGenerator->dump($this->config, $localRepo, $this->package, $this->installationManager, 'composer', $this->optimizeAutoloader);
}

if ($this->runScripts) {
$devMode = (int) $this->devMode;
putenv("COMPOSER_DEV_MODE=$devMode");


 $eventName = $this->update ? ScriptEvents::POST_UPDATE_CMD : ScriptEvents::POST_INSTALL_CMD;
$this->eventDispatcher->dispatchScript($eventName, $this->devMode);
}

if ($this->executeOperations) {

 foreach ($localRepo->getPackages() as $package) {
$this->installationManager->ensureBinariesPresence($package);
}

$vendorDir = $this->config->get('vendor-dir');
if (is_dir($vendorDir)) {

 
 @touch($vendorDir);
}
}


 if (!defined('HHVM_VERSION')) {
gc_enable();
}

return 0;
}








protected function doInstall($localRepo, $installedRepo, $platformRepo, $aliases)
{

 $lockedRepository = null;
$repositories = null;


 
 
 if (!$this->update || (!empty($this->updateWhitelist) && $this->locker->isLocked())) {
try {
$lockedRepository = $this->locker->getLockedRepository($this->devMode);
} catch (\RuntimeException $e) {

 if ($this->package->getDevRequires()) {
throw $e;
}

 $lockedRepository = $this->locker->getLockedRepository();
}
}

$this->whitelistUpdateDependencies(
$lockedRepository ?: $localRepo,
$this->package->getRequires(),
$this->package->getDevRequires()
);

$this->io->writeError('<info>Loading composer repositories with package information</info>');


 $policy = $this->createPolicy();
$pool = $this->createPool($this->update ? null : $lockedRepository);
$pool->addRepository($installedRepo, $aliases);
if ($this->update) {
$repositories = $this->repositoryManager->getRepositories();
foreach ($repositories as $repository) {
$pool->addRepository($repository, $aliases);
}
}

 
 
 if ($lockedRepository) {
$pool->addRepository($lockedRepository, $aliases);
}


 $request = $this->createRequest($this->package, $platformRepo);

if ($this->update) {

 $removedUnstablePackages = array();
foreach ($localRepo->getPackages() as $package) {
if (
!$pool->isPackageAcceptable($package->getNames(), $package->getStability())
&& $this->installationManager->isPackageInstalled($localRepo, $package)
) {
$removedUnstablePackages[$package->getName()] = true;
$request->remove($package->getName(), new Constraint('=', $package->getVersion()));
}
}

$this->io->writeError('<info>Updating dependencies'.($this->devMode ? ' (including require-dev)' : '').'</info>');

$request->updateAll();

$links = array_merge($this->package->getRequires(), $this->package->getDevRequires());

foreach ($links as $link) {
$request->install($link->getTarget(), $link->getConstraint());
}


 
 if ($this->updateWhitelist) {
$currentPackages = $this->getCurrentPackages($installedRepo);


 $candidates = array();
foreach ($links as $link) {
$candidates[$link->getTarget()] = true;
$rootRequires[$link->getTarget()] = $link;
}
foreach ($currentPackages as $package) {
$candidates[$package->getName()] = true;
}


 foreach ($candidates as $candidate => $dummy) {
foreach ($currentPackages as $curPackage) {
if ($curPackage->getName() === $candidate) {
if (!$this->isUpdateable($curPackage) && !isset($removedUnstablePackages[$curPackage->getName()])) {
$constraint = new Constraint('=', $curPackage->getVersion());
$description = $this->locker->isLocked() ? '(locked at' : '(installed at';
$requiredAt = isset($rootRequires[$candidate]) ? ', required as ' . $rootRequires[$candidate]->getPrettyConstraint() : '';
$constraint->setPrettyString($description . ' ' . $curPackage->getPrettyVersion() . $requiredAt . ')');
$request->install($curPackage->getName(), $constraint);
}
break;
}
}
}
}
} else {
$this->io->writeError('<info>Installing dependencies'.($this->devMode ? ' (including require-dev)' : '').' from lock file</info>');

if (!$this->locker->isFresh()) {
$this->io->writeError('<warning>Warning: The lock file is not up to date with the latest changes in composer.json. You may be getting outdated dependencies. Run update to update them.</warning>', true, IOInterface::QUIET);
}

foreach ($lockedRepository->getPackages() as $package) {
$version = $package->getVersion();
if (isset($aliases[$package->getName()][$version])) {
$version = $aliases[$package->getName()][$version]['alias_normalized'];
}
$constraint = new Constraint('=', $version);
$constraint->setPrettyString($package->getPrettyVersion());
$request->install($package->getName(), $constraint);
}

foreach ($this->locker->getPlatformRequirements($this->devMode) as $link) {
$request->install($link->getTarget(), $link->getConstraint());
}
}


 $this->processDevPackages($localRepo, $pool, $policy, $repositories, $installedRepo, $lockedRepository, 'force-links');


 $this->eventDispatcher->dispatchInstallerEvent(InstallerEvents::PRE_DEPENDENCIES_SOLVING, $this->devMode, $policy, $pool, $installedRepo, $request);
$solver = new Solver($policy, $pool, $installedRepo, $this->io);
try {
$operations = $solver->solve($request, $this->ignorePlatformReqs);
} catch (SolverProblemsException $e) {
$this->io->writeError('<error>Your requirements could not be resolved to an installable set of packages.</error>', true, IOInterface::QUIET);
$this->io->writeError($e->getMessage());
if ($this->update && !$this->devMode) {
$this->io->writeError('<warning>Running update with --no-dev does not mean require-dev is ignored, it just means the packages will not be installed. If dev requirements are blocking the update you have to resolve those problems.</warning>', true, IOInterface::QUIET);
}

return array(max(1, $e->getCode()), array());
}


 $operations = $this->processDevPackages($localRepo, $pool, $policy, $repositories, $installedRepo, $lockedRepository, 'force-updates', $operations);

$this->eventDispatcher->dispatchInstallerEvent(InstallerEvents::POST_DEPENDENCIES_SOLVING, $this->devMode, $policy, $pool, $installedRepo, $request, $operations);

$this->io->writeError("Analyzed ".count($pool)." packages to resolve dependencies", true, IOInterface::VERBOSE);
$this->io->writeError("Analyzed ".$solver->getRuleSetSize()." rules to resolve dependencies", true, IOInterface::VERBOSE);


 if (!$operations) {
$this->io->writeError('Nothing to install or update');
}

$operations = $this->movePluginsToFront($operations);
$operations = $this->moveUninstallsToFront($operations);


 
 if ($this->update) {
$devPackages = $this->extractDevPackages($operations, $localRepo, $platformRepo, $aliases);
if (!$this->devMode) {
$operations = $this->filterDevPackageOperations($devPackages, $operations, $localRepo);
}
} else {
$devPackages = null;
}

if ($operations) {
$installs = $updates = $uninstalls = array();
foreach ($operations as $operation) {
if ($operation instanceof InstallOperation) {
$installs[] = $operation->getPackage()->getPrettyName().':'.$operation->getPackage()->getFullPrettyVersion();
} elseif ($operation instanceof UpdateOperation) {
$updates[] = $operation->getTargetPackage()->getPrettyName().':'.$operation->getTargetPackage()->getFullPrettyVersion();
} elseif ($operation instanceof UninstallOperation) {
$uninstalls[] = $operation->getPackage()->getPrettyName();
}
}

$this->io->writeError(
sprintf("<info>Package operations: %d install%s, %d update%s, %d removal%s</info>",
count($installs),
1 === count($installs) ? '' : 's',
count($updates),
1 === count($updates) ? '' : 's',
count($uninstalls),
1 === count($uninstalls) ? '' : 's')
);
if ($installs) {
$this->io->writeError("Installs: ".implode(', ', $installs), true, IOInterface::VERBOSE);
}
if ($updates) {
$this->io->writeError("Updates: ".implode(', ', $updates), true, IOInterface::VERBOSE);
}
if ($uninstalls) {
$this->io->writeError("Removals: ".implode(', ', $uninstalls), true, IOInterface::VERBOSE);
}
}

foreach ($operations as $operation) {

 if ('install' === $operation->getJobType()) {
$this->suggestedPackagesReporter->addSuggestionsFromPackage($operation->getPackage());
}


 if ($this->update) {
$package = null;
if ('update' === $operation->getJobType()) {
$package = $operation->getTargetPackage();
} elseif ('install' === $operation->getJobType()) {
$package = $operation->getPackage();
}
if ($package && $package->isDev()) {
$references = $this->package->getReferences();
if (isset($references[$package->getName()])) {
$this->updateInstallReferences($package, $references[$package->getName()]);
}
}
if ('update' === $operation->getJobType()
&& $operation->getTargetPackage()->isDev()
&& $operation->getTargetPackage()->getVersion() === $operation->getInitialPackage()->getVersion()
&& (!$operation->getTargetPackage()->getSourceReference() || $operation->getTargetPackage()->getSourceReference() === $operation->getInitialPackage()->getSourceReference())
&& (!$operation->getTargetPackage()->getDistReference() || $operation->getTargetPackage()->getDistReference() === $operation->getInitialPackage()->getDistReference())
) {
$this->io->writeError('  - Skipping update of '. $operation->getTargetPackage()->getPrettyName().' to the same reference-locked version', true, IOInterface::DEBUG);
$this->io->writeError('', true, IOInterface::DEBUG);

continue;
}
}

$event = 'Composer\Installer\PackageEvents::PRE_PACKAGE_'.strtoupper($operation->getJobType());
if (defined($event) && $this->runScripts) {
$this->eventDispatcher->dispatchPackageEvent(constant($event), $this->devMode, $policy, $pool, $installedRepo, $request, $operations, $operation);
}


 if (!$this->executeOperations && false === strpos($operation->getJobType(), 'Alias')) {
$this->io->writeError('  - ' . $operation);
} elseif ($this->io->isDebug() && false !== strpos($operation->getJobType(), 'Alias')) {
$this->io->writeError('  - ' . $operation);
}

$this->installationManager->execute($localRepo, $operation);


 if ($this->verbose && $this->io->isVeryVerbose() && in_array($operation->getJobType(), array('install', 'update'))) {
$reason = $operation->getReason();
if ($reason instanceof Rule) {
switch ($reason->getReason()) {
case Rule::RULE_JOB_INSTALL:
$this->io->writeError('    REASON: Required by the root package: '.$reason->getPrettyString($pool));
$this->io->writeError('');
break;
case Rule::RULE_PACKAGE_REQUIRES:
$this->io->writeError('    REASON: '.$reason->getPrettyString($pool));
$this->io->writeError('');
break;
}
}
}

$event = 'Composer\Installer\PackageEvents::POST_PACKAGE_'.strtoupper($operation->getJobType());
if (defined($event) && $this->runScripts) {
$this->eventDispatcher->dispatchPackageEvent(constant($event), $this->devMode, $policy, $pool, $installedRepo, $request, $operations, $operation);
}

if ($this->executeOperations || $this->writeLock) {
$localRepo->write();
}
}

if ($this->executeOperations) {

 $this->processPackageUrls($pool, $policy, $localRepo, $repositories);
$localRepo->write();
}

return array(0, $devPackages);
}











private function extractDevPackages(array $operations, RepositoryInterface $localRepo, PlatformRepository $platformRepo, array $aliases)
{
if (!$this->package->getDevRequires()) {
return array();
}


 $tempLocalRepo = clone $localRepo;
foreach ($operations as $operation) {
switch ($operation->getJobType()) {
case 'install':
case 'markAliasInstalled':
if (!$tempLocalRepo->hasPackage($operation->getPackage())) {
$tempLocalRepo->addPackage(clone $operation->getPackage());
}
break;

case 'uninstall':
case 'markAliasUninstalled':
$tempLocalRepo->removePackage($operation->getPackage());
break;

case 'update':
$tempLocalRepo->removePackage($operation->getInitialPackage());
if (!$tempLocalRepo->hasPackage($operation->getTargetPackage())) {
$tempLocalRepo->addPackage(clone $operation->getTargetPackage());
}
break;

default:
throw new \LogicException('Unknown type: '.$operation->getJobType());
}
}


 
 
 $localRepo = new InstalledArrayRepository(array());
$loader = new ArrayLoader(null, true);
$dumper = new ArrayDumper();
foreach ($tempLocalRepo->getCanonicalPackages() as $pkg) {
$localRepo->addPackage($loader->load($dumper->dump($pkg)));
}
unset($tempLocalRepo, $loader, $dumper);

$policy = $this->createPolicy();
$pool = $this->createPool();
$installedRepo = $this->createInstalledRepo($localRepo, $platformRepo);
$pool->addRepository($installedRepo, $aliases);


 $request = $this->createRequest($this->package, $platformRepo);
$request->updateAll();
foreach ($this->package->getRequires() as $link) {
$request->install($link->getTarget(), $link->getConstraint());
}


 $this->eventDispatcher->dispatchInstallerEvent(InstallerEvents::PRE_DEPENDENCIES_SOLVING, false, $policy, $pool, $installedRepo, $request);
$solver = new Solver($policy, $pool, $installedRepo, $this->io);
$ops = $solver->solve($request, $this->ignorePlatformReqs);
$this->eventDispatcher->dispatchInstallerEvent(InstallerEvents::POST_DEPENDENCIES_SOLVING, false, $policy, $pool, $installedRepo, $request, $ops);

$devPackages = array();
foreach ($ops as $op) {
if ($op->getJobType() === 'uninstall') {
$devPackages[] = $op->getPackage();
}
}

return $devPackages;
}




private function filterDevPackageOperations(array $devPackages, array $operations, RepositoryInterface $localRepo)
{
$finalOps = array();
$packagesToSkip = array();
foreach ($devPackages as $pkg) {
$packagesToSkip[$pkg->getName()] = true;
if ($installedDevPkg = $localRepo->findPackage($pkg->getName(), '*')) {
$finalOps[] = new UninstallOperation($installedDevPkg, 'non-dev install removing it');
}
}


 foreach ($operations as $op) {
$package = $op->getJobType() === 'update' ? $op->getTargetPackage() : $op->getPackage();
if (isset($packagesToSkip[$package->getName()])) {
continue;
}

$finalOps[] = $op;
}

return $finalOps;
}














private function movePluginsToFront(array $operations)
{
$pluginsNoDeps = array();
$pluginsWithDeps = array();
$pluginRequires = array();

foreach (array_reverse($operations, true) as $idx => $op) {
if ($op instanceof InstallOperation) {
$package = $op->getPackage();
} elseif ($op instanceof UpdateOperation) {
$package = $op->getTargetPackage();
} else {
continue;
}


 $isPlugin = $package->getType() === 'composer-plugin' || $package->getType() === 'composer-installer';


 if ($isPlugin || count(array_intersect($package->getNames(), $pluginRequires))) {

 $requires = array_filter(array_keys($package->getRequires()), function($req) {
return $req !== 'composer-plugin-api' && !preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $req);
});


 if ($isPlugin && !count($requires)) {

 array_unshift($pluginsNoDeps, $op);
} else {

 $pluginRequires = array_merge($pluginRequires, $requires);

 array_unshift($pluginsWithDeps, $op);
}

unset($operations[$idx]);
}
}

return array_merge($pluginsNoDeps, $pluginsWithDeps, $operations);
}








private function moveUninstallsToFront(array $operations)
{
$uninstOps = array();
foreach ($operations as $idx => $op) {
if ($op instanceof UninstallOperation) {
$uninstOps[] = $op;
unset($operations[$idx]);
}
}

return array_merge($uninstOps, $operations);
}




private function createInstalledRepo(RepositoryInterface $localRepo, PlatformRepository $platformRepo)
{

 
 
 $installedRootPackage = clone $this->package;
$installedRootPackage->setRequires(array());
$installedRootPackage->setDevRequires(array());

$repos = array(
$localRepo,
new InstalledArrayRepository(array($installedRootPackage)),
$platformRepo,
);
$installedRepo = new CompositeRepository($repos);
if ($this->additionalInstalledRepository) {
$installedRepo->addRepository($this->additionalInstalledRepository);
}

return $installedRepo;
}





private function createPool(RepositoryInterface $lockedRepository = null)
{
if ($this->update) {
$minimumStability = $this->package->getMinimumStability();
$stabilityFlags = $this->package->getStabilityFlags();

$requires = array_merge($this->package->getRequires(), $this->package->getDevRequires());
} else {
$minimumStability = $this->locker->getMinimumStability();
$stabilityFlags = $this->locker->getStabilityFlags();

$requires = array();
foreach ($lockedRepository->getPackages() as $package) {
$constraint = new Constraint('=', $package->getVersion());
$constraint->setPrettyString($package->getPrettyVersion());
$requires[$package->getName()] = $constraint;
}
}

$rootConstraints = array();
foreach ($requires as $req => $constraint) {

 if ($this->ignorePlatformReqs && preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $req)) {
continue;
}
if ($constraint instanceof Link) {
$rootConstraints[$req] = $constraint->getConstraint();
} else {
$rootConstraints[$req] = $constraint;
}
}

return new Pool($minimumStability, $stabilityFlags, $rootConstraints);
}




private function createPolicy()
{
$preferStable = null;
$preferLowest = null;
if (!$this->update) {
$preferStable = $this->locker->getPreferStable();
$preferLowest = $this->locker->getPreferLowest();
}

 
 if (null === $preferStable) {
$preferStable = $this->preferStable || $this->package->getPreferStable();
}
if (null === $preferLowest) {
$preferLowest = $this->preferLowest;
}

return new DefaultPolicy($preferStable, $preferLowest);
}






private function createRequest(RootPackageInterface $rootPackage, PlatformRepository $platformRepo)
{
$request = new Request();

$constraint = new Constraint('=', $rootPackage->getVersion());
$constraint->setPrettyString($rootPackage->getPrettyVersion());
$request->install($rootPackage->getName(), $constraint);

$fixedPackages = $platformRepo->getPackages();
if ($this->additionalInstalledRepository) {
$additionalFixedPackages = $this->additionalInstalledRepository->getPackages();
$fixedPackages = array_merge($fixedPackages, $additionalFixedPackages);
}


 
 $provided = $rootPackage->getProvides();
foreach ($fixedPackages as $package) {
$constraint = new Constraint('=', $package->getVersion());
$constraint->setPrettyString($package->getPrettyVersion());


 if ($package->getRepository() !== $platformRepo
|| !isset($provided[$package->getName()])
|| !$provided[$package->getName()]->getConstraint()->matches($constraint)
) {
$request->fix($package->getName(), $constraint);
}
}

return $request;
}












private function processDevPackages($localRepo, $pool, $policy, $repositories, $installedRepo, $lockedRepository, $task, array $operations = null)
{
if ($task === 'force-updates' && null === $operations) {
throw new \InvalidArgumentException('Missing operations argument');
}
if ($task === 'force-links') {
$operations = array();
}

if ($this->update && $this->updateWhitelist) {
$currentPackages = $this->getCurrentPackages($installedRepo);
}

foreach ($localRepo->getCanonicalPackages() as $package) {

 if (!$package->isDev()) {
continue;
}


 foreach ($operations as $operation) {
if (('update' === $operation->getJobType() && $operation->getInitialPackage()->equals($package))
|| ('uninstall' === $operation->getJobType() && $operation->getPackage()->equals($package))
) {
continue 2;
}
}

if ($this->update) {

 if ($this->updateWhitelist && !$this->isUpdateable($package)) {

 foreach ($currentPackages as $curPackage) {
if ($curPackage->isDev() && $curPackage->getName() === $package->getName() && $curPackage->getVersion() === $package->getVersion()) {
if ($task === 'force-links') {
$package->setRequires($curPackage->getRequires());
$package->setConflicts($curPackage->getConflicts());
$package->setProvides($curPackage->getProvides());
$package->setReplaces($curPackage->getReplaces());
} elseif ($task === 'force-updates') {
if (($curPackage->getSourceReference() && $curPackage->getSourceReference() !== $package->getSourceReference())
|| ($curPackage->getDistReference() && $curPackage->getDistReference() !== $package->getDistReference())
) {
$operations[] = new UpdateOperation($package, $curPackage);
}
}

break;
}
}

continue;
}


 $matches = $pool->whatProvides($package->getName(), new Constraint('=', $package->getVersion()));
foreach ($matches as $index => $match) {

 if (!in_array($match->getRepository(), $repositories, true)) {
unset($matches[$index]);
continue;
}


 if ($match->getName() !== $package->getName()) {
unset($matches[$index]);
continue;
}

$matches[$index] = $match->getId();
}


 if ($matches && $matches = $policy->selectPreferredPackages($pool, array(), $matches)) {
$newPackage = $pool->literalToPackage($matches[0]);

if ($task === 'force-links' && $newPackage) {
$package->setRequires($newPackage->getRequires());
$package->setConflicts($newPackage->getConflicts());
$package->setProvides($newPackage->getProvides());
$package->setReplaces($newPackage->getReplaces());
}

if ($task === 'force-updates' && $newPackage && (
(($newPackage->getSourceReference() && $newPackage->getSourceReference() !== $package->getSourceReference())
|| ($newPackage->getDistReference() && $newPackage->getDistReference() !== $package->getDistReference())
)
)) {
$operations[] = new UpdateOperation($package, $newPackage);

continue;
}
}

if ($task === 'force-updates') {

 $references = $this->package->getReferences();

if (isset($references[$package->getName()]) && $references[$package->getName()] !== $package->getSourceReference()) {

 $operations[] = new UpdateOperation($package, clone $package);
}
}
} else {

 foreach ($lockedRepository->findPackages($package->getName()) as $lockedPackage) {
if ($lockedPackage->isDev() && $lockedPackage->getVersion() === $package->getVersion()) {
if ($task === 'force-links') {
$package->setRequires($lockedPackage->getRequires());
$package->setConflicts($lockedPackage->getConflicts());
$package->setProvides($lockedPackage->getProvides());
$package->setReplaces($lockedPackage->getReplaces());
} elseif ($task === 'force-updates') {
if (($lockedPackage->getSourceReference() && $lockedPackage->getSourceReference() !== $package->getSourceReference())
|| ($lockedPackage->getDistReference() && $lockedPackage->getDistReference() !== $package->getDistReference())
) {
$operations[] = new UpdateOperation($package, $lockedPackage);
}
}

break;
}
}
}
}

return $operations;
}






private function getCurrentPackages($installedRepo)
{
if ($this->locker->isLocked()) {
try {
return $this->locker->getLockedRepository(true)->getPackages();
} catch (\RuntimeException $e) {

 return $this->locker->getLockedRepository()->getPackages();
}
}

return $installedRepo->getPackages();
}




private function getRootAliases()
{
if ($this->update) {
$aliases = $this->package->getAliases();
} else {
$aliases = $this->locker->getAliases();
}

$normalizedAliases = array();

foreach ($aliases as $alias) {
$normalizedAliases[$alias['package']][$alias['version']] = array(
'alias' => $alias['alias'],
'alias_normalized' => $alias['alias_normalized'],
);
}

return $normalizedAliases;
}







private function processPackageUrls($pool, $policy, $localRepo, $repositories)
{
if (!$this->update) {
return;
}

$rootRefs = $this->package->getReferences();

foreach ($localRepo->getCanonicalPackages() as $package) {

 $matches = $pool->whatProvides($package->getName(), new Constraint('=', $package->getVersion()));
foreach ($matches as $index => $match) {

 if (!in_array($match->getRepository(), $repositories, true)) {
unset($matches[$index]);
continue;
}


 if ($match->getName() !== $package->getName()) {
unset($matches[$index]);
continue;
}

$matches[$index] = $match->getId();
}


 if ($matches && $matches = $policy->selectPreferredPackages($pool, array(), $matches)) {
$newPackage = $pool->literalToPackage($matches[0]);


 $sourceUrl = $package->getSourceUrl();
$newSourceUrl = $newPackage->getSourceUrl();
$newReference = $newPackage->getSourceReference();

if ($package->isDev() && isset($rootRefs[$package->getName()]) && $package->getSourceReference() === $rootRefs[$package->getName()]) {
$newReference = $rootRefs[$package->getName()];
}

$this->updatePackageUrl($package, $newSourceUrl, $newPackage->getSourceType(), $newReference, $newPackage->getDistUrl());

if ($package instanceof CompletePackage && $newPackage instanceof CompletePackage) {
$package->setAbandoned($newPackage->getReplacementPackage() ?: $newPackage->isAbandoned());
}

$package->setDistMirrors($newPackage->getDistMirrors());
$package->setSourceMirrors($newPackage->getSourceMirrors());
}
}
}

private function updatePackageUrl(PackageInterface $package, $sourceUrl, $sourceType, $sourceReference, $distUrl)
{
$oldSourceRef = $package->getSourceReference();

if ($package->getSourceUrl() !== $sourceUrl) {
$package->setSourceType($sourceType);
$package->setSourceUrl($sourceUrl);
$package->setSourceReference($sourceReference);
}


 
 if (preg_match('{^https?://(?:(?:www\.)?bitbucket\.org|(api\.)?github\.com)/}i', $distUrl)) {
$package->setDistUrl($distUrl);
$this->updateInstallReferences($package, $sourceReference);
}

if ($this->updateWhitelist && !$this->isUpdateable($package)) {
$this->updateInstallReferences($package, $oldSourceRef);
}
}

private function updateInstallReferences(PackageInterface $package, $reference)
{
if (!$reference) {
return;
}

$package->setSourceReference($reference);

if (preg_match('{^https?://(?:(?:www\.)?bitbucket\.org|(api\.)?github\.com)/}i', $package->getDistUrl())) {
$package->setDistReference($reference);
$package->setDistUrl(preg_replace('{(?<=/)[a-f0-9]{40}(?=/|$)}i', $reference, $package->getDistUrl()));
} else if ($package->getDistReference()) { 
 $package->setDistReference($reference);
}
}





private function aliasPlatformPackages(PlatformRepository $platformRepo, $aliases)
{
foreach ($aliases as $package => $versions) {
foreach ($versions as $version => $alias) {
$packages = $platformRepo->findPackages($package, $version);
foreach ($packages as $package) {
$aliasPackage = new AliasPackage($package, $alias['alias_normalized'], $alias['alias']);
$aliasPackage->setRootPackageAlias(true);
$platformRepo->addPackage($aliasPackage);
}
}
}
}





private function isUpdateable(PackageInterface $package)
{
if (!$this->updateWhitelist) {
throw new \LogicException('isUpdateable should only be called when a whitelist is present');
}

foreach ($this->updateWhitelist as $whiteListedPattern => $void) {
$patternRegexp = $this->packageNameToRegexp($whiteListedPattern);
if (preg_match($patternRegexp, $package->getName())) {
return true;
}
}

return false;
}







private function packageNameToRegexp($whiteListedPattern)
{
$cleanedWhiteListedPattern = str_replace('\\*', '.*', preg_quote($whiteListedPattern));

return "{^" . $cleanedWhiteListedPattern . "$}i";
}





private function extractPlatformRequirements($links)
{
$platformReqs = array();
foreach ($links as $link) {
if (preg_match(PlatformRepository::PLATFORM_PACKAGE_REGEX, $link->getTarget())) {
$platformReqs[$link->getTarget()] = $link->getPrettyConstraint();
}
}

return $platformReqs;
}














private function whitelistUpdateDependencies($localOrLockRepo, array $rootRequires, array $rootDevRequires)
{
if (!$this->updateWhitelist) {
return;
}

$rootRequires = array_merge($rootRequires, $rootDevRequires);

$requiredPackageNames = array();
foreach ($rootRequires as $require) {
$requiredPackageNames[] = $require->getTarget();
}

$skipPackages = array();
foreach ($rootRequires as $require) {
$skipPackages[$require->getTarget()] = true;
}

$pool = new Pool('dev');
$pool->addRepository($localOrLockRepo);

$seen = array();

$rootRequiredPackageNames = array_keys($rootRequires);

foreach ($this->updateWhitelist as $packageName => $void) {
$packageQueue = new \SplQueue;

$depPackages = $pool->whatProvides($packageName);

$nameMatchesRequiredPackage = in_array($packageName, $requiredPackageNames, true);


 if (!$nameMatchesRequiredPackage) {
$whitelistPatternRegexp = $this->packageNameToRegexp($packageName);
foreach ($rootRequiredPackageNames as $rootRequiredPackageName) {
if (preg_match($whitelistPatternRegexp, $rootRequiredPackageName)) {
$nameMatchesRequiredPackage = true;
break;
}
}
}

if (count($depPackages) == 0 && !$nameMatchesRequiredPackage && !in_array($packageName, array('nothing', 'lock', 'mirrors'))) {
$this->io->writeError('<warning>Package "' . $packageName . '" listed for update is not installed. Ignoring.</warning>');
}

foreach ($depPackages as $depPackage) {
$packageQueue->enqueue($depPackage);
}

while (!$packageQueue->isEmpty()) {
$package = $packageQueue->dequeue();
if (isset($seen[$package->getId()])) {
continue;
}

$seen[$package->getId()] = true;
$this->updateWhitelist[$package->getName()] = true;

if (!$this->whitelistDependencies) {
continue;
}

$requires = $package->getRequires();

foreach ($requires as $require) {
$requirePackages = $pool->whatProvides($require->getTarget());

foreach ($requirePackages as $requirePackage) {
if (isset($this->updateWhitelist[$requirePackage->getName()])) {
continue;
}

if (isset($skipPackages[$requirePackage->getName()])) {
$this->io->writeError('<warning>Dependency "' . $requirePackage->getName() . '" is also a root requirement, but is not explicitly whitelisted. Ignoring.</warning>');
continue;
}

$packageQueue->enqueue($requirePackage);
}
}
}
}
}








private function mockLocalRepositories(RepositoryManager $rm)
{
$packages = array();
foreach ($rm->getLocalRepository()->getPackages() as $package) {
$packages[(string) $package] = clone $package;
}
foreach ($packages as $key => $package) {
if ($package instanceof AliasPackage) {
$alias = (string) $package->getAliasOf();
$packages[$key] = new AliasPackage($packages[$alias], $package->getVersion(), $package->getPrettyVersion());
}
}
$rm->setLocalRepository(
new InstalledArrayRepository($packages)
);
}








public static function create(IOInterface $io, Composer $composer)
{
return new static(
$io,
$composer->getConfig(),
$composer->getPackage(),
$composer->getDownloadManager(),
$composer->getRepositoryManager(),
$composer->getLocker(),
$composer->getInstallationManager(),
$composer->getEventDispatcher(),
$composer->getAutoloadGenerator()
);
}





public function setAdditionalInstalledRepository(RepositoryInterface $additionalInstalledRepository)
{
$this->additionalInstalledRepository = $additionalInstalledRepository;

return $this;
}







public function setDryRun($dryRun = true)
{
$this->dryRun = (bool) $dryRun;

return $this;
}






public function isDryRun()
{
return $this->dryRun;
}







public function setPreferSource($preferSource = true)
{
$this->preferSource = (bool) $preferSource;

return $this;
}







public function setPreferDist($preferDist = true)
{
$this->preferDist = (bool) $preferDist;

return $this;
}







public function setOptimizeAutoloader($optimizeAutoloader = false)
{
$this->optimizeAutoloader = (bool) $optimizeAutoloader;
if (!$this->optimizeAutoloader) {

 
 $this->setClassMapAuthoritative(false);
}

return $this;
}








public function setClassMapAuthoritative($classMapAuthoritative = false)
{
$this->classMapAuthoritative = (bool) $classMapAuthoritative;
if ($this->classMapAuthoritative) {

 $this->setOptimizeAutoloader(true);
}

return $this;
}







public function setApcuAutoloader($apcuAutoloader = false)
{
$this->apcuAutoloader = (bool) $apcuAutoloader;

return $this;
}







public function setUpdate($update = true)
{
$this->update = (bool) $update;

return $this;
}







public function setDevMode($devMode = true)
{
$this->devMode = (bool) $devMode;

return $this;
}









public function setDumpAutoloader($dumpAutoloader = true)
{
$this->dumpAutoloader = (bool) $dumpAutoloader;

return $this;
}









public function setRunScripts($runScripts = true)
{
$this->runScripts = (bool) $runScripts;

return $this;
}







public function setConfig(Config $config)
{
$this->config = $config;

return $this;
}







public function setVerbose($verbose = true)
{
$this->verbose = (bool) $verbose;

return $this;
}






public function isVerbose()
{
return $this->verbose;
}







public function setIgnorePlatformRequirements($ignorePlatformReqs = false)
{
$this->ignorePlatformReqs = (bool) $ignorePlatformReqs;

return $this;
}








public function setUpdateWhitelist(array $packages)
{
$this->updateWhitelist = array_flip(array_map('strtolower', $packages));

return $this;
}







public function setWhitelistDependencies($updateDependencies = true)
{
$this->whitelistDependencies = (bool) $updateDependencies;

return $this;
}







public function setPreferStable($preferStable = true)
{
$this->preferStable = (bool) $preferStable;

return $this;
}







public function setPreferLowest($preferLowest = true)
{
$this->preferLowest = (bool) $preferLowest;

return $this;
}









public function setWriteLock($writeLock = true)
{
$this->writeLock = (bool) $writeLock;

return $this;
}









public function setExecuteOperations($executeOperations = true)
{
$this->executeOperations = (bool) $executeOperations;

return $this;
}







public function setSkipSuggest($skipSuggest = true)
{
$this->skipSuggest = (bool) $skipSuggest;

return $this;
}










public function disablePlugins()
{
$this->installationManager->disablePlugins();

return $this;
}





public function setSuggestedPackagesReporter(SuggestedPackagesReporter $suggestedPackagesReporter)
{
$this->suggestedPackagesReporter = $suggestedPackagesReporter;

return $this;
}
}
