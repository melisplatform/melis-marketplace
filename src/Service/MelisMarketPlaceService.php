<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;

class MelisMarketPlaceService extends MelisCoreGeneralService
{
    /** @var int */
    const NEED_UPDATE = -1;

    /** @var int  */
    const UP_TO_DATE = 1;

    /** @var int  */
    const IN_ADVANCE = 2;

    /** @var string  */
    CONST DEV = 'dev-';

    /** @var string  */
    const MODULE_SETUP_CONTROLLER = 'MelisSetupController';

    /** @var string */
    const MODULE_SETUP_FORM = 'setupFormAction';

    /** @var string  */
    const MODULE_SETUP_VALIDATE_FORM = 'setupValidateDataAction';

    /** @var string  */
    const MODULE_SETUP_RESULT_FORM = 'setupResultAction';

    /** @var string  */
    const MODULE_SETUP_FORM_SHOW_ON_DOWNLOAD = 'displayFormOnMarketPlaceDownload';

    /** @var string  */
    const MODULE_SETUP_FORM_SHOW_ON_UPDATE = 'displayFormOnMarketPlaceUpdate';

    /** @var string  */
    const ACTION_DOWNLOAD = 'download';

    /** @var string  */
    const ACTION_UPDATE = 'update';

    /**
     * Get the value of the class' property
     * @param $class
     * @param $prop
     *
     * @return mixed|null
     * @throws \ReflectionException
     */
    protected function getClassProperty($class, $prop)
    {
        if (class_exists($class)) {
            $reflection = new \ReflectionClass($class);
            $property = $reflection->getProperty($prop)->getValue(new $class);
            return $property;
        }

        return null;
    }

    /**
     * Flag for Marketplace whether to display the setup form or not when downloading
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnDownload($module)
    {
        $moduleClass  = implode('\\', [$module, 'Controller', self::MODULE_SETUP_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_DOWNLOAD);
        }

        return false;
    }

    /**
     * Flag for Marketplace whether to display the setup form or not when updating
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnUpdate($module)
    {
        $moduleClass  = implode('\\', [$module, 'Controller', self::MODULE_SETUP_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_UPDATE);
        }

        return false;
    }

    /**
     * @param $module
     *
     * @return null
     */
    public function getFormDom($module)
    {
        $class   = implode('\\', [$module, 'Controller', str_replace('Controller', '',self::MODULE_SETUP_CONTROLLER)]);
        $forward = $this->getServiceLocator()->get('Application')->getMvcEvent()->getTarget()->forward();
        $form  = $forward->dispatch($class, ['action' => str_replace('Action', '', self::MODULE_SETUP_FORM)]);

        /** @var \Zend\View\Renderer\RendererInterface $renderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $formDom = (new \Zend\Mime\Part($renderer->render($form)))->getContent() ?: null;

        return $formDom;
    }

    /**
     * @param $module
     * @param $action
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function showForm($module, $action)
    {
        if ($action === self::ACTION_DOWNLOAD && $this->showSetupFormOnDownload($module)) {
            return true;
        }

        if ($action === self::ACTION_UPDATE && $this->showSetupFormOnUpdate($module)) {
            return true;
        }

        return false;
    }

    /**
     * @description Function to get the local version of a module
     * and compare it from the repository to determine
     * whether the module is up to date or not
     *
     * @param $moduleName
     * @param $moduleVersion
     *
     * @return array
     */
    public function compareLocalVersionFromRepo($moduleName = null, $moduleVersion = null)
    {
        // Event parameters prepare
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_start', $arrayParameters);

        $status = null;
        $moduleSvc = $this->getServiceLocator()->get('ModulesService');
        $tmpModName = ($arrayParameters['moduleName'] == "MelisMarketplace") ? "MelisMarketPlace" : $arrayParameters['moduleName'];
        $modulesInfo = $moduleSvc->getModulesAndVersions($tmpModName);
        $localVersion = $modulesInfo['version'];

        //check if local version is advance or not
        if (substr(strtolower($localVersion), 0, 4) === self::DEV) {
            $status = self::IN_ADVANCE;
        } else {
            //remove the v from the version and convert to float
            //to compare the version number
            $localV = (float) str_replace('v', "", strtolower($localVersion));
            $latestV = (float) str_replace('v', "", strtolower($arrayParameters['moduleVersion']));

            //check if  local version is updated than the version in repo
            if ($latestV <= $localV) {
                $status = self::UP_TO_DATE;
            } else {
                $status = self::NEED_UPDATE;
            }
        }

        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = $status;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_end', $arrayParameters);

        return $arrayParameters['results'];
    }
}
