<?php
/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;
use Zend\View\Model\JsonModel;

class MelisMarketPlaceService extends MelisCoreGeneralService
{
    /**
     * @var int NEED_UPDATE
     */
    const NEED_UPDATE = -1;

    /**
     * @var int UP_TO_DATE
     */
    const UP_TO_DATE = 1;

    /**
     * @var int IN_ADVANCE
     */
    const IN_ADVANCE = 2;

    /**
     * @var string DEV
     */
    CONST DEV = 'dev-';

    /**
     * @var string MODULE_SETUP_POST_DOWNLOAD_CONTROLLER
     */
    const MODULE_SETUP_POST_DOWNLOAD_CONTROLLER = 'MelisSetupPostDownloadController';

    /**
     * @var string MODULE_SETUP_POST_UPDATE_CONTROLLER
     */
    const MODULE_SETUP_POST_UPDATE_CONTROLLER = 'MelisSetupPostUpdateController';

    /**
     * @var string MODULE_SETUP_FORM
     */
    const MODULE_SETUP_FORM = 'getFormAction';

    /**
     * @var string MODULE_SETUP_VALIDATE_FORM
     */
    const MODULE_SETUP_VALIDATE_FORM = 'validateFormAction';

    /**
     * @var string MODULE_SETUP_SUBMIT_FORM
     */
    const MODULE_SETUP_SUBMIT_FORM = 'submitAction';

    /**
     * @var string MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE
     */
    const MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE = 'showOnMarketplacePostSetup';

    /**
     * @var string ACTION_DOWNLOAD
     */
    const ACTION_DOWNLOAD = 'download';

    /**
     * @var string ACTION_UPDATE
     */
    const ACTION_UPDATE = 'update';

    /**
     * @var string $action
     */
    protected $action = 'download';

    /**
     * @return string
     */
    protected function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     */
    protected function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $module
     *
     * @return string|null
     */
    public function getForm($module)
    {
        if (!$this->moduleManager()->isModuleLoaded($module)) {
            $this->moduleManager()->loadModule($module);
        }

        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $form = $this->forward()->dispatch($class, ['action' => str_replace('Action', '', self::MODULE_SETUP_FORM)]);

        /** @var \Zend\View\Renderer\RendererInterface $renderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $formDom = (new \Zend\Mime\Part($renderer->render($form)))->getContent() ?: null;

        return trim($formDom);
    }

    /**
     * @param $module
     * @param $post
     *
     * @return array|\ArrayAccess|null|\Traversable
     */
    public function validateForm($module, $post)
    {
        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $params = array_merge(
            ['action' => str_replace('Action', '', self::MODULE_SETUP_VALIDATE_FORM)],
            ['post' => $post]);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->forward()->dispatch($class, $params);

        if ($result instanceof JsonModel) {
            return $result->getVariables();
        }

        return null;
    }

    /**
     * @param $module
     * @param $post
     *
     * @return array|\ArrayAccess|null|\Traversable
     */
    public function submitForm($module, $post)
    {
        $class = implode('\\', [$module, 'Controller', str_replace('Controller', '', $this->getActionController())]);
        $params = array_merge(
            ['action' => str_replace('Action', '', self::MODULE_SETUP_SUBMIT_FORM)],
            ['post' => $post]);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->forward()->dispatch($class, $params);

        if ($result instanceof JsonModel) {
            return $result->getVariables();
        }


        return null;
    }

    /**
     * @return string
     */
    public function getActionController()
    {
        switch ($this->getAction()) {
            case self::ACTION_DOWNLOAD:
                return self::MODULE_SETUP_POST_DOWNLOAD_CONTROLLER;
                break;
            case self::ACTION_UPDATE:
                return self::MODULE_SETUP_POST_UPDATE_CONTROLLER;
                break;
            default:
                return self::ACTION_DOWNLOAD;
                break;
        }

        return self::ACTION_DOWNLOAD;
    }

    /**
     * @param $module
     * @param $action
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function hasPostSetup($module, $action)
    {
        $this->setAction($action);

        $namespace = implode('\\', [$module, 'Controller', $this->getActionController()]);

        if (!class_exists($namespace) && !method_exists($namespace, $this->getActionController())) {
            return false;
        }

        if ($action === self::ACTION_DOWNLOAD && $this->showSetupFormOnDownload($module)) {
            return true;
        }

        if ($action === self::ACTION_UPDATE && $this->showSetupFormOnUpdate($module)) {
            return true;
        }

        return false;
    }

    /**
     * Flag for Marketplace whether to display the setup form or not when downloading
     *
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnDownload($module)
    {
        $moduleClass = implode('\\', [$module, 'Controller', self::MODULE_SETUP_POST_DOWNLOAD_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE);
        }

        return false;
    }

    /**
     * Get the value of the class' property
     *
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
     * Flag for Marketplace whether to display the setup form or not when updating
     *
     * @param $module
     *
     * @return bool|mixed|null
     * @throws \ReflectionException
     */
    protected function showSetupFormOnUpdate($module)
    {
        $moduleClass = implode('\\', [$module, 'Controller', self::MODULE_SETUP_POST_UPDATE_CONTROLLER]);

        if (class_exists($moduleClass)) {
            return $this->getClassProperty($moduleClass, self::MODULE_SETUP_FORM_SHOW_ON_MARKETPLACE);
        }

        return false;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public function plugModule($module)
    {
        return $this->moduleManager()->loadModule($module);
    }

    /**
     * @return \MelisAssetManager\Service\MelisCoreModulesService
     */
    protected function moduleManager()
    {
        /** @var \MelisAssetManager\Service\MelisCoreModulesService $service */
        $service = $this->getServiceLocator()->get('MelisAssetManagerModulesService');

        return $service;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public function unplugModule($module)
    {
        return $this->moduleManager()->unloadModule($module);
    }

    /**
     * Function to get the local version of a module
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
        $moduleSvc = $this->moduleManager();
        $tmpModName = ($arrayParameters['moduleName'] == "MelisMarketplace") ? "MelisMarketPlace" : $arrayParameters['moduleName'];
        $modulesInfo = $moduleSvc->getModulesAndVersions($tmpModName);
        $localVersion = $modulesInfo['version'];

        //check if local version is advance or not
        if (substr(strtolower($localVersion), 0, 4) === self::DEV) {
            $status = self::IN_ADVANCE;
        } else {
            //remove the v from the version and convert to float
            //to compare the version number
            $localV = str_replace('v', "", strtolower($localVersion));
            $latestV = str_replace('v', "", strtolower($arrayParameters['moduleVersion']));

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

    /**
     * @return \Zend\Mvc\Controller\Plugin\Forward
     */
    protected function forward()
    {
        /** @var \Zend\Mvc\Controller\Plugin\Forward $forward */
        $forward = $this->getServiceLocator()->get('Application')->getMvcEvent()->getTarget()->forward();
        return $forward;
    }
}
