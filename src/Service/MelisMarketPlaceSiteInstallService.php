<?php
namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;

class MelisMarketPlaceSiteInstallService extends MelisCoreGeneralService
{
    public function download($package)
    {

    }

    /**
     * @return \MelisComposerDeploy\Service\MelisComposerService
     */
    protected function composer()
    {
        /** @var \MelisComposerDeploy\Service\MelisComposerService $composer */
        $composer = $this->getServiceLocator()->get('MelisComposerService');
        return $composer;
    }
}
