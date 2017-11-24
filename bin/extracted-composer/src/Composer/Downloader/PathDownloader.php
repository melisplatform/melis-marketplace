<?php











namespace Composer\Downloader;

use Composer\Package\Archiver\ArchivableFilesFinder;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionGuesser;
use Composer\Package\Version\VersionParser;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use Composer\Util\Filesystem as ComposerFilesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;







class PathDownloader extends FileDownloader implements VcsCapableDownloaderInterface
{
const STRATEGY_SYMLINK = 10;
const STRATEGY_MIRROR = 20;




public function download(PackageInterface $package, $path, $output = true)
{
$url = $package->getDistUrl();
$realUrl = realpath($url);
if (false === $realUrl || !file_exists($realUrl) || !is_dir($realUrl)) {
throw new \RuntimeException(sprintf(
'Source path "%s" is not found for package %s', $url, $package->getName()
));
}

if (strpos(realpath($path) . DIRECTORY_SEPARATOR, $realUrl . DIRECTORY_SEPARATOR) === 0) {

 
 
 
 throw new \RuntimeException(sprintf(
'Package %s cannot install to "%s" inside its source at "%s"',
$package->getName(), realpath($path), $realUrl
));
}


 $transportOptions = $package->getTransportOptions() + array('symlink' => null);


 $currentStrategy = self::STRATEGY_SYMLINK;
$allowedStrategies = array(self::STRATEGY_SYMLINK, self::STRATEGY_MIRROR);

$mirrorPathRepos = getenv('COMPOSER_MIRROR_PATH_REPOS');
if ($mirrorPathRepos) {
$currentStrategy = self::STRATEGY_MIRROR;
}

if (true === $transportOptions['symlink']) {
$currentStrategy = self::STRATEGY_SYMLINK;
$allowedStrategies = array(self::STRATEGY_SYMLINK);
} elseif (false === $transportOptions['symlink']) {
$currentStrategy = self::STRATEGY_MIRROR;
$allowedStrategies = array(self::STRATEGY_MIRROR);
}

$fileSystem = new Filesystem();
$this->filesystem->removeDirectory($path);

if ($output) {
$this->io->writeError(sprintf(
'  - Installing <info>%s</info> (<comment>%s</comment>): ',
$package->getName(),
$package->getFullPrettyVersion()
), false);
}

$isFallback = false;
if (self::STRATEGY_SYMLINK == $currentStrategy) {
try {
if (Platform::isWindows()) {

 $this->io->writeError(sprintf('Junctioning from %s', $url), false);
$this->filesystem->junction($realUrl, $path);
} else {
$absolutePath = $path;
if (!$this->filesystem->isAbsolutePath($absolutePath)) {
$absolutePath = getcwd() . DIRECTORY_SEPARATOR . $path;
}
$shortestPath = $this->filesystem->findShortestPath($absolutePath, $realUrl);
$path = rtrim($path, "/");
$this->io->writeError(sprintf('Symlinking from %s', $url), false);
$fileSystem->symlink($shortestPath, $path);
}
} catch (IOException $e) {
if (in_array(self::STRATEGY_MIRROR, $allowedStrategies)) {
$this->io->writeError('');
$this->io->writeError('    <error>Symlink failed, fallback to use mirroring!</error>');
$currentStrategy = self::STRATEGY_MIRROR;
$isFallback = true;
} else {
throw new \RuntimeException(sprintf('Symlink from "%s" to "%s" failed!', $realUrl, $path));
}
}
}


 if (self::STRATEGY_MIRROR == $currentStrategy) {
$fs = new ComposerFilesystem();
$realUrl = $fs->normalizePath($realUrl);

$this->io->writeError(sprintf('%sMirroring from %s', $isFallback ? '    ' : '', $url), false);
$iterator = new ArchivableFilesFinder($realUrl, array());
$fileSystem->mirror($realUrl, $path, $iterator);
}

$this->io->writeError('');
}




public function remove(PackageInterface $package, $path, $output = true)
{





if (Platform::isWindows() && $this->filesystem->isJunction($path)) {
if ($output) {
$this->io->writeError("  - Removing junction for <info>" . $package->getName() . "</info> (<comment>" . $package->getFullPrettyVersion() . "</comment>)");
}
if (!$this->filesystem->removeJunction($path)) {
$this->io->writeError("    <warn>Could not remove junction at " . $path . " - is another process locking it?</warn>");
throw new \RuntimeException('Could not reliably remove junction for package ' . $package->getName());
}
} else {
parent::remove($package, $path, $output);
}
}




public function getVcsReference(PackageInterface $package, $path)
{
$parser = new VersionParser;
$guesser = new VersionGuesser($this->config, new ProcessExecutor($this->io), $parser);
$dumper = new ArrayDumper;

$packageConfig = $dumper->dump($package);
if ($packageVersion = $guesser->guessVersion($packageConfig, $path)) {
return $packageVersion['commit'];
}
}
}
