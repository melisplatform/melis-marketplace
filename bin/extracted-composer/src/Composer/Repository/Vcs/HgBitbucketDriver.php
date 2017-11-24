<?php











namespace Composer\Repository\Vcs;

use Composer\Config;
use Composer\IO\IOInterface;




class HgBitbucketDriver extends BitbucketDriver
{



public function getRootIdentifier()
{
if ($this->fallbackDriver) {
return $this->fallbackDriver->getRootIdentifier();
}

if (null === $this->rootIdentifier) {
if (! $this->getRepoData()) {
return $this->fallbackDriver->getRootIdentifier();
}

if ($this->vcsType !== 'hg') {
throw new \RuntimeException(
$this->url.' does not appear to be a mercurial repository, use '.
$this->cloneHttpsUrl.' if this is a git bitbucket repository'
);
}

$mainBranchData = $this->getMainBranchData();
$this->rootIdentifier = !empty($mainBranchData['name']) ? $mainBranchData['name'] : 'default';
}

return $this->rootIdentifier;
}




public static function supports(IOInterface $io, Config $config, $url, $deep = false)
{
if (!preg_match('#^https?://bitbucket\.org/([^/]+)/([^/]+)/?$#', $url)) {
return false;
}

if (!extension_loaded('openssl')) {
$io->writeError('Skipping Bitbucket hg driver for '.$url.' because the OpenSSL PHP extension is missing.', true, IOInterface::VERBOSE);

return false;
}

return true;
}




protected function setupFallbackDriver($url)
{
$this->fallbackDriver = new HgDriver(
array('url' => $url),
$this->io,
$this->config,
$this->process,
$this->remoteFilesystem
);
$this->fallbackDriver->initialize();
}




protected function generateSshUrl()
{
return 'ssh://hg@' . $this->originUrl . '/' . $this->owner.'/'.$this->repository;
}
}
