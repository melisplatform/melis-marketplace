<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;
use Composer\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use MelisMarketPlace\Service\ComposerOutputFormatterStyle;
/**
 * This service handles the requests and commands that will be made into composer
 */
class MelisMarketPlaceComposerService extends MelisCoreGeneralService
{
    const COMPOSER          = __DIR__ . '/../../bin/extracted-composer/composer';
    const INSTALL           = 'install';
    const UPDATE            = 'update';
    const REMOVE            = 'remove';
    const DOWNLOAD          = 'require';
    const DUMP_AUTOLOAD     = 'dump-autoload';

    const DEFAULT_ARGS      = '-vv ';
    const REMOVE_ARGS       = '-vv --no-scripts ';
    const DRY_RUN_ARGS      = '--dry-run';
    const ROOT_REQS         = '--root-reqs ';
    const WITH_DEPENDENCIES ='--with-dependencies';

    /**
     * The path of the platform
     * @var string
     */
    protected $documentRoot;

    /**
     * Sets whether composer commands should be applied or just for testing
     * @var boolean
     */
    protected $isDryRun;

    /**
     * Sets the path of the platform, if nothing is set, then it will use the default path of this platform
     * @param $documentRoot
     */
    public function setDocumentRoot($documentRoot)
    {
        if($documentRoot)
            $this->documentRoot = $documentRoot;
        else
            $this->documentRoot = $this->getDefaultDocRoot();
    }

    /**
     * Returns the path of the platform, if nothing is set, then it will use the default path of this platform
     * @return string
     */
    public function getDocumentRoot()
    {
        if(!$this->documentRoot)
            $this->documentRoot = $this->getDefaultDocRoot();

        return $this->documentRoot;
    }

    /**
     * Sets whether to enable the dry-run arg
     * @param $status
     */
    public function setDryRun($status)
    {
        $this->isDryRun = (bool) $status;
    }

    /**
     * Returns if dry-run arg is enabled or not
     * @return mixed
     */
    public function getDryRun()
    {
        return $this->isDryRun;
    }

    /**
     * Executes a $ composer update command
     * @param string|null $package
     * @param string|null $version
     * @param boolean $dryRun
     * @return string|StreamOutput
     */
    public function update($package = null, $version = null, $dryRun = false)
    {
        if($dryRun)
            $this->setDryRun(true);

        $package = !empty($version) ? $package.':'.$version : $package;

        return $this->runCommand(self::UPDATE, $package, self::ROOT_REQS . self::DEFAULT_ARGS);
    }

    /**
     * Executes $ composer require command
     * @param string $package
     * @param string|null $version
     * @param boolean $dryRun
     * @return string|StreamOutput
     */
    public function download($package, $version = null, $dryRun = false)
    {
        if($dryRun)
            $this->setDryRun(true);

        $package = !empty($version) ? $package.':'.$version : $package;

        return $this->runCommand(self::DOWNLOAD, $package,self::DEFAULT_ARGS);
    }

    /**
     * Executes $ composer dump-autoload comand
     * @return string|StreamOutput
     */
    public function dumpAutoloader()
    {
        return $this->runCommand(self::DUMP_AUTOLOAD,self::DEFAULT_ARGS);
    }

    /**
     * Executes a $ composer remove package/package command
     * @param $package
     * @return bool
     */
    public function remove($package)
    {
        $output = $this->runCommand(self::REMOVE, $package, self::REMOVE_ARGS);

        if(!$output)
            return true;

        return false;
    }

    /**
     * This calls the composer CLI to execute a command
     * @param $cmd the command type
     * @param null $package which package should be executed
     * @param $args composer command-line arguments
     * @return string|StreamOutput
     */
    private function runCommand($cmd, $package = null, $args)
    {
        $translator = $this->getServiceLocator()->get('translator');
        $docPath    = str_replace(array('\\', 'public/../'), '', $this->getDocumentRoot());
        $docPath    = substr($docPath, 0, strlen($docPath)-1); // remove last "/" trail


        set_time_limit(-1);
        ini_set ('memory_limit', -1);
        putenv('COMPOSER_HOME='. self::COMPOSER);


        if(in_array($cmd, $this->availableCommands())) {

            $dryRunArgs = null;

            if($this->getDryRun()) {
                $dryRunArgs = self::DRY_RUN_ARGS;
            }


            $commandString = "$cmd $package $dryRunArgs $args --working-dir=\"$docPath\"";
            $input         = new StringInput($commandString);
            $output        = new StreamOutput(fopen('php://output','w'));
            $composer      = new Application();
            $formatter     = $output->getFormatter();

            $formatter->setDecorated(true);
            $formatter->setStyle('error', new ComposerOutputFormatterStyle(ComposerOutputFormatterStyle::ERROR));
            $formatter->setStyle('info', new ComposerOutputFormatterStyle(ComposerOutputFormatterStyle::INFO));
            $formatter->setStyle('comment', new ComposerOutputFormatterStyle(ComposerOutputFormatterStyle::COMMENT));
            $formatter->setStyle('warning', new ComposerOutputFormatterStyle(ComposerOutputFormatterStyle::ERROR));
            $output->setFormatter($formatter);

            chdir($docPath);
            print 'Command: ' . $commandString . '<br/>'.PHP_EOL;
            $composer->run($input, $output);

            file_put_contents('CHARME.txt', base64_encode($output), FILE_APPEND);

            return $output;
        }

        return sprintf($translator->translate('tr_market_place_unknown_command'), $cmd);
    }

    /**
     * Sets the limitation to what commands that can be executed
     * @return array
     */
    private function availableCommands()
    {
        return array(
            self::INSTALL,
            self::UPDATE,
            self::DUMP_AUTOLOAD,
            self::DOWNLOAD,
            self::REMOVE
        );
    }

    /**
     * Returns the document root of this platform
     * @return string
     */
    private function getDefaultDocRoot()
    {
        return $_SERVER['DOCUMENT_ROOT'].'/../';
    }

    private function hexentities($str) {
        $return = '';
        for($i = 0; $i < strlen($str); $i++) {
            $return .= '&#x'.bin2hex(substr($str, $i, 1)).';';
        }
        return $return;
    }


}