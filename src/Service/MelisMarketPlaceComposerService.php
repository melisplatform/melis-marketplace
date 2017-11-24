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
/**
 * This service handles the requests and commands that will be made into composer
 */
class MelisMarketPlaceComposerService extends MelisCoreGeneralService
{
    const COMPOSER        = __DIR__ . '/../../bin/extracted-composer/composer';
    const INSTALL         = 'install';
    const UPDATE          = 'update';
    const REMOVE          = 'remove';
    const REQUIRE_PACKAGE = 'require';
    const DUMP_AUTOLOAD   = 'dump-autoload';

    const DEFAULT_ARGS    = '--prefer-dist -vvv -d';
    const REMOVE_ARGS     = '--no-update --no-scripts -d';

    /**
     * The path of the platform
     * @var string
     */
    protected $documentRoot;

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

        return $this->documentRoot ;
    }

    /**
     * Executes a $ composer update command
     * @param string|null $package
     * @param string|null $version
     * @return string|StreamOutput
     */
    public function update($package = null, $version = null)
    {
        $package = !empty($version) ? $package.':'.$version : $package;

        return $this->runCommand(self::UPDATE, $package, self::DEFAULT_ARGS);
    }

    /**
     * Executes $ composer require command
     * @param string $package
     * @param string|null $version
     * @return string|StreamOutput
     */
    public function requirePackage($package, $version = null)
    {
        $package = !empty($version) ? $package.':'.$version : $package;

        return $this->runCommand(self::REQUIRE_PACKAGE, $package,self::DEFAULT_ARGS);
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
    public function removePackage($package)
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

        set_time_limit(-1);
        ini_set ('memory_limit', -1);
        putenv('COMPOSER_HOME='. self::COMPOSER);

        if(in_array($cmd, $this->availableCommands())) {

            $docPath       = $this->getDocumentRoot();
            $commandString = "$cmd $args $docPath $package";
            $input         = new StringInput($commandString);
            $output        = new StreamOutput(fopen('php://output','w', false));
            $composer      = new Application();

            $composer->run($input, $output);

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
            self::REQUIRE_PACKAGE,
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


}