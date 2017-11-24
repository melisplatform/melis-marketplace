<?php











namespace Composer\Downloader;

use Composer\Package\PackageInterface;
use Composer\Util\ProcessExecutor;




class FossilDownloader extends VcsDownloader
{



public function doDownload(PackageInterface $package, $path, $url)
{

 $this->config->prohibitUrlByConfig($url, $this->io);

$url = ProcessExecutor::escape($url);
$ref = ProcessExecutor::escape($package->getSourceReference());
$repoFile = $path . '.fossil';
$this->io->writeError("Cloning ".$package->getSourceReference());
$command = sprintf('fossil clone %s %s', $url, ProcessExecutor::escape($repoFile));
if (0 !== $this->process->execute($command, $ignoredOutput)) {
throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
}
$command = sprintf('fossil open %s', ProcessExecutor::escape($repoFile));
if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
}
$command = sprintf('fossil update %s', $ref);
if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
}
}




public function doUpdate(PackageInterface $initial, PackageInterface $target, $path, $url)
{

 $this->config->prohibitUrlByConfig($url, $this->io);

$url = ProcessExecutor::escape($url);
$ref = ProcessExecutor::escape($target->getSourceReference());
$this->io->writeError(" Updating to ".$target->getSourceReference());

if (!$this->hasMetadataRepository($path)) {
throw new \RuntimeException('The .fslckout file is missing from '.$path.', see https://getcomposer.org/commit-deps for more information');
}

$command = sprintf('fossil pull && fossil up %s', $ref);
if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
}
}




public function getLocalChanges(PackageInterface $package, $path)
{
if (!$this->hasMetadataRepository($path)) {
return null;
}

$this->process->execute('fossil changes', $output, realpath($path));

return trim($output) ?: null;
}




protected function getCommitLogs($fromReference, $toReference, $path)
{
$command = sprintf('fossil timeline -t ci -W 0 -n 0 before %s', $toReference);

if (0 !== $this->process->execute($command, $output, realpath($path))) {
throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
}

$log = '';
$match = '/\d\d:\d\d:\d\d\s+\[' . $toReference . '\]/';

foreach ($this->process->splitLines($output) as $line) {
if (preg_match($match, $line)) {
break;
}
$log .= $line;
}

return $log;
}




protected function hasMetadataRepository($path)
{
return is_file($path . '/.fslckout') || is_file($path . '/_FOSSIL_');
}
}
