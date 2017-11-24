<?php











namespace Composer\Repository\Vcs;

use Composer\Cache;
use Composer\Downloader\TransportException;
use Composer\Config;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Util\ProcessExecutor;
use Composer\Util\RemoteFilesystem;
use Composer\Util\Filesystem;






abstract class VcsDriver implements VcsDriverInterface
{

protected $url;

protected $originUrl;

protected $repoConfig;

protected $io;

protected $config;

protected $process;

protected $remoteFilesystem;

protected $infoCache = array();

protected $cache;










final public function __construct(array $repoConfig, IOInterface $io, Config $config, ProcessExecutor $process = null, RemoteFilesystem $remoteFilesystem = null)
{
if (Filesystem::isLocalPath($repoConfig['url'])) {
$repoConfig['url'] = Filesystem::getPlatformPath($repoConfig['url']);
}

$this->url = $repoConfig['url'];
$this->originUrl = $repoConfig['url'];
$this->repoConfig = $repoConfig;
$this->io = $io;
$this->config = $config;
$this->process = $process ?: new ProcessExecutor($io);
$this->remoteFilesystem = $remoteFilesystem ?: Factory::createRemoteFilesystem($this->io, $config);
}







protected function shouldCache($identifier)
{
return $this->cache && preg_match('{[a-f0-9]{40}}i', $identifier);
}




public function getComposerInformation($identifier)
{
if (!isset($this->infoCache[$identifier])) {
if ($this->shouldCache($identifier) && $res = $this->cache->read($identifier)) {
return $this->infoCache[$identifier] = JsonFile::parseJson($res);
}

$composer = $this->getBaseComposerInformation($identifier);

if ($this->shouldCache($identifier)) {
$this->cache->write($identifier, json_encode($composer));
}

$this->infoCache[$identifier] = $composer;
}

return $this->infoCache[$identifier];
}

protected function getBaseComposerInformation($identifier)
{
$composerFileContent = $this->getFileContent('composer.json', $identifier);

if (!$composerFileContent) {
return null;
}

$composer = JsonFile::parseJson($composerFileContent, $identifier . ':composer.json');

if (empty($composer['time']) && $changeDate = $this->getChangeDate($identifier)) {
$composer['time'] = $changeDate->format(DATE_RFC3339);
}

return $composer;
}




public function hasComposerFile($identifier)
{
try {
return (bool) $this->getComposerInformation($identifier);
} catch (TransportException $e) {
}

return false;
}








protected function getScheme()
{
if (extension_loaded('openssl')) {
return 'https';
}

return 'http';
}








protected function getContents($url)
{
$options = isset($this->repoConfig['options']) ? $this->repoConfig['options'] : array();

return $this->remoteFilesystem->getContents($this->originUrl, $url, false, $options);
}




public function cleanup()
{
return;
}
}
