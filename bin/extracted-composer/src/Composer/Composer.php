<?php











namespace Composer;

use Composer\Package\RootPackageInterface;
use Composer\Package\Locker;
use Composer\Repository\RepositoryManager;
use Composer\Installer\InstallationManager;
use Composer\Plugin\PluginManager;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Autoload\AutoloadGenerator;
use Composer\Package\Archiver\ArchiveManager;






class Composer
{
const VERSION = '1.5.2';
const BRANCH_ALIAS_VERSION = '';
const RELEASE_DATE = '2017-09-11 16:59:25';




private $package;




private $locker;




private $repositoryManager;




private $downloadManager;




private $installationManager;




private $pluginManager;




private $config;




private $eventDispatcher;




private $autoloadGenerator;




private $archiveManager;





public function setPackage(RootPackageInterface $package)
{
$this->package = $package;
}




public function getPackage()
{
return $this->package;
}




public function setConfig(Config $config)
{
$this->config = $config;
}




public function getConfig()
{
return $this->config;
}




public function setLocker(Locker $locker)
{
$this->locker = $locker;
}




public function getLocker()
{
return $this->locker;
}




public function setRepositoryManager(RepositoryManager $manager)
{
$this->repositoryManager = $manager;
}




public function getRepositoryManager()
{
return $this->repositoryManager;
}




public function setDownloadManager(DownloadManager $manager)
{
$this->downloadManager = $manager;
}




public function getDownloadManager()
{
return $this->downloadManager;
}




public function setArchiveManager(ArchiveManager $manager)
{
$this->archiveManager = $manager;
}




public function getArchiveManager()
{
return $this->archiveManager;
}




public function setInstallationManager(InstallationManager $manager)
{
$this->installationManager = $manager;
}




public function getInstallationManager()
{
return $this->installationManager;
}




public function setPluginManager(PluginManager $manager)
{
$this->pluginManager = $manager;
}




public function getPluginManager()
{
return $this->pluginManager;
}




public function setEventDispatcher(EventDispatcher $eventDispatcher)
{
$this->eventDispatcher = $eventDispatcher;
}




public function getEventDispatcher()
{
return $this->eventDispatcher;
}




public function setAutoloadGenerator(AutoloadGenerator $autoloadGenerator)
{
$this->autoloadGenerator = $autoloadGenerator;
}




public function getAutoloadGenerator()
{
return $this->autoloadGenerator;
}
}
