<?php











namespace Composer\Downloader;

use Composer\Config;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionGuesser;
use Composer\Package\Version\VersionParser;
use Composer\Util\ProcessExecutor;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;




abstract class VcsDownloader implements DownloaderInterface, ChangeReportInterface, VcsCapableDownloaderInterface
{

protected $io;

protected $config;

protected $process;

protected $filesystem;

public function __construct(IOInterface $io, Config $config, ProcessExecutor $process = null, Filesystem $fs = null)
{
$this->io = $io;
$this->config = $config;
$this->process = $process ?: new ProcessExecutor($io);
$this->filesystem = $fs ?: new Filesystem($this->process);
}




public function getInstallationSource()
{
return 'source';
}




public function download(PackageInterface $package, $path)
{
if (!$package->getSourceReference()) {
throw new \InvalidArgumentException('Package '.$package->getPrettyName().' is missing reference information');
}

$this->io->writeError("  - Installing <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>): ", false);
$this->filesystem->emptyDirectory($path);

$urls = $package->getSourceUrls();
while ($url = array_shift($urls)) {
try {
if (Filesystem::isLocalPath($url)) {

 
 $needle = 'file://';
$isFileProtocol = false;
if (0 === strpos($url, $needle)) {
$url = substr($url, strlen($needle));
$isFileProtocol = true;
}


 if (false !== strpos($url, '%')) {
$url = rawurldecode($url);
}

$url = realpath($url);

if ($isFileProtocol) {
$url = $needle . $url;
}
}
$this->doDownload($package, $path, $url);
break;
} catch (\Exception $e) {

 if ($e instanceof \PHPUnit_Framework_Exception) {
throw $e;
}
if ($this->io->isDebug()) {
$this->io->writeError('Failed: ['.get_class($e).'] '.$e->getMessage());
} elseif (count($urls)) {
$this->io->writeError('    Failed, trying the next URL');
}
if (!count($urls)) {
throw $e;
}
}
}
}




public function update(PackageInterface $initial, PackageInterface $target, $path)
{
if (!$target->getSourceReference()) {
throw new \InvalidArgumentException('Package '.$target->getPrettyName().' is missing reference information');
}

$name = $target->getName();
if ($initial->getPrettyVersion() == $target->getPrettyVersion()) {
if ($target->getSourceType() === 'svn') {
$from = $initial->getSourceReference();
$to = $target->getSourceReference();
} else {
$from = substr($initial->getSourceReference(), 0, 7);
$to = substr($target->getSourceReference(), 0, 7);
}
$name .= ' '.$initial->getPrettyVersion();
} else {
$from = $initial->getFullPrettyVersion();
$to = $target->getFullPrettyVersion();
}

$this->io->writeError("  - Updating <info>" . $name . "</info> (<comment>" . $from . "</comment> => <comment>" . $to . "</comment>): ", false);

$this->cleanChanges($initial, $path, true);
$urls = $target->getSourceUrls();

$exception = null;
while ($url = array_shift($urls)) {
try {
if (Filesystem::isLocalPath($url)) {
$url = realpath($url);
}
$this->doUpdate($initial, $target, $path, $url);

$exception = null;
break;
} catch (\Exception $exception) {

 if ($exception instanceof \PHPUnit_Framework_Exception) {
throw $exception;
}
if ($this->io->isDebug()) {
$this->io->writeError('Failed: ['.get_class($exception).'] '.$exception->getMessage());
} elseif (count($urls)) {
$this->io->writeError('    Failed, trying the next URL');
}
}
}

$this->reapplyChanges($path);


 
 if (!$exception && $this->io->isVerbose() && $this->hasMetadataRepository($path)) {
$message = 'Pulling in changes:';
$logs = $this->getCommitLogs($initial->getSourceReference(), $target->getSourceReference(), $path);

if (!trim($logs)) {
$message = 'Rolling back changes:';
$logs = $this->getCommitLogs($target->getSourceReference(), $initial->getSourceReference(), $path);
}

if (trim($logs)) {
$logs = implode("\n", array_map(function ($line) {
return '      ' . $line;
}, explode("\n", $logs)));


 $logs = str_replace('<', '\<', $logs);

$this->io->writeError('    '.$message);
$this->io->writeError($logs);
}
}

if (!$urls && $exception) {
throw $exception;
}
}




public function remove(PackageInterface $package, $path)
{
$this->io->writeError("  - Removing <info>" . $package->getName() . "</info> (<comment>" . $package->getPrettyVersion() . "</comment>)");
$this->cleanChanges($package, $path, false);
if (!$this->filesystem->removeDirectory($path)) {
throw new \RuntimeException('Could not completely delete '.$path.', aborting.');
}
}





public function setOutputProgress($outputProgress)
{
return $this;
}




public function getVcsReference(PackageInterface $package, $path)
{
$parser = new VersionParser;
$guesser = new VersionGuesser($this->config, $this->process, $parser);
$dumper = new ArrayDumper;

$packageConfig = $dumper->dump($package);
if ($packageVersion = $guesser->guessVersion($packageConfig, $path)) {
return $packageVersion['commit'];
}
}










protected function cleanChanges(PackageInterface $package, $path, $update)
{

 if (null !== $this->getLocalChanges($package, $path)) {
throw new \RuntimeException('Source directory ' . $path . ' has uncommitted changes.');
}
}







protected function reapplyChanges($path)
{
}








abstract protected function doDownload(PackageInterface $package, $path, $url);









abstract protected function doUpdate(PackageInterface $initial, PackageInterface $target, $path, $url);









abstract protected function getCommitLogs($fromReference, $toReference, $path);








abstract protected function hasMetadataRepository($path);
}
