<?php











namespace Composer\Downloader;

use Composer\Config;
use Composer\Cache;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Package\PackageInterface;
use Composer\Util\IniHelper;
use Composer\Util\Platform;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use Composer\IO\IOInterface;
use Symfony\Component\Process\ExecutableFinder;
use ZipArchive;




class ZipDownloader extends ArchiveDownloader
{
protected static $hasSystemUnzip;
private static $hasZipArchive;
private static $isWindows;

protected $process;
private $zipArchiveObject;

public function __construct(IOInterface $io, Config $config, EventDispatcher $eventDispatcher = null, Cache $cache = null, ProcessExecutor $process = null, RemoteFilesystem $rfs = null)
{
$this->process = $process ?: new ProcessExecutor($io);
parent::__construct($io, $config, $eventDispatcher, $cache, $rfs);
}




public function download(PackageInterface $package, $path, $output = true)
{
if (null === self::$hasSystemUnzip) {
$finder = new ExecutableFinder;
self::$hasSystemUnzip = (bool) $finder->find('unzip');
}

if (null === self::$hasZipArchive) {
self::$hasZipArchive = class_exists('ZipArchive');
}

if (null === self::$isWindows) {
self::$isWindows = Platform::isWindows();
}

if (!self::$hasZipArchive && !self::$hasSystemUnzip) {

 $iniMessage = IniHelper::getMessage();
$error = "The zip extension and unzip command are both missing, skipping.\n" . $iniMessage;

throw new \RuntimeException($error);
}

return parent::download($package, $path, $output);
}









protected function extractWithSystemUnzip($file, $path, $isLastChance)
{
if (!self::$hasZipArchive) {

 $isLastChance = true;
}

if (!self::$hasSystemUnzip && !$isLastChance) {

 
 return $this->extractWithZipArchive($file, $path, true);
}

$processError = null;

 $overwrite = $isLastChance ? '-o' : '';

$command = 'unzip -qq '.$overwrite.' '.ProcessExecutor::escape($file).' -d '.ProcessExecutor::escape($path);

try {
if (0 === $this->process->execute($command, $ignoredOutput)) {
return true;
}

$processError = new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
} catch (\Exception $e) {
$processError = $e;
}

if ($isLastChance) {
throw $processError;
}

$this->io->writeError('    '.$processError->getMessage());
$this->io->writeError('    The archive may contain identical file names with different capitalization (which fails on case insensitive filesystems)');
$this->io->writeError('    Unzip with unzip command failed, falling back to ZipArchive class');

return $this->extractWithZipArchive($file, $path, true);
}









protected function extractWithZipArchive($file, $path, $isLastChance)
{
if (!self::$hasSystemUnzip) {

 $isLastChance = true;
}

if (!self::$hasZipArchive && !$isLastChance) {

 
 return $this->extractWithSystemUnzip($file, $path, true);
}

$processError = null;
$zipArchive = $this->zipArchiveObject ?: new ZipArchive();

try {
if (true === ($retval = $zipArchive->open($file))) {
$extractResult = $zipArchive->extractTo($path);

if (true === $extractResult) {
$zipArchive->close();

return true;
}

$processError = new \RuntimeException(rtrim("There was an error extracting the ZIP file, it is either corrupted or using an invalid format.\n"));
} else {
$processError = new \UnexpectedValueException(rtrim($this->getErrorMessage($retval, $file)."\n"), $retval);
}
} catch (\ErrorException $e) {
$processError = new \RuntimeException('The archive may contain identical file names with different capitalization (which fails on case insensitive filesystems): '.$e->getMessage(), 0, $e);
} catch (\Exception $e) {
$processError = $e;
}

if ($isLastChance) {
throw $processError;
}

$this->io->writeError('    '.$processError->getMessage());
$this->io->writeError('    Unzip with ZipArchive class failed, falling back to unzip command');

return $this->extractWithSystemUnzip($file, $path, true);
}







public function extract($file, $path)
{

 if (self::$isWindows) {
$this->extractWithZipArchive($file, $path, false);
} else {
$this->extractWithSystemUnzip($file, $path, false);
}
}








protected function getErrorMessage($retval, $file)
{
switch ($retval) {
case ZipArchive::ER_EXISTS:
return sprintf("File '%s' already exists.", $file);
case ZipArchive::ER_INCONS:
return sprintf("Zip archive '%s' is inconsistent.", $file);
case ZipArchive::ER_INVAL:
return sprintf("Invalid argument (%s)", $file);
case ZipArchive::ER_MEMORY:
return sprintf("Malloc failure (%s)", $file);
case ZipArchive::ER_NOENT:
return sprintf("No such zip file: '%s'", $file);
case ZipArchive::ER_NOZIP:
return sprintf("'%s' is not a zip archive.", $file);
case ZipArchive::ER_OPEN:
return sprintf("Can't open zip file: %s", $file);
case ZipArchive::ER_READ:
return sprintf("Zip read error (%s)", $file);
case ZipArchive::ER_SEEK:
return sprintf("Zip seek error (%s)", $file);
default:
return sprintf("'%s' is not a valid zip archive, got error code: %s", $file, $retval);
}
}
}
