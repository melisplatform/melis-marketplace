<?php
namespace MelisMarketPlace;

use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Session\Container;
use MelisMarketPlace\Listener\MelisMarketPlaceTestListener;

/**
 * Class Module
 * @package MelisMarketPlace
 * @require melis-core
 */
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->createTranslations($e);

        (new \MelisMarketPlace\Listener\MelisMarketPlaceTestListener())->attach($eventManager);
    }

    public function getConfig()
    {
        $config = [];
        $configFiles = [
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/app.interface.php',
            include __DIR__ . '/../config/app.tools.php',
            include __DIR__ . '/../config/app.forms.php',
            include __DIR__ . '/../config/diagnostic.config.php',
        ];

        foreach ($configFiles as $file)
            $config = ArrayUtils::merge($config, $file);

        return $config;
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function createTranslations($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');

        $container = new Container('meliscore');
        $locale = $container['melis-lang-locale'];

        if (!empty($locale)) {

            $translationType = [
                'interface'
            ];

            $translationList = [];
            if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../module/MelisModuleConfig/config/translation.list.php')) {
                $translationList = include 'module/MelisModuleConfig/config/translation.list.php';
            }

            foreach($translationType as $type) {

                $transPath = '';
                $moduleTrans = __NAMESPACE__."/$locale.$type.php";

                if(in_array($moduleTrans, $translationList)) {
                    $transPath = "module/MelisModuleConfig/languages/".$moduleTrans;
                }

                if(empty($transPath)) {
                    // if translation is not found, use melis default translations
                    $defaultLocale = (file_exists(__DIR__ . "/../language/$locale.$type.php"))? $locale : "en_EN";
                    $transPath = __DIR__ . "/../language/$defaultLocale.$type.php";
                }

                $translator->addTranslationFile('phparray', $transPath);
            }
        }
    }
}

