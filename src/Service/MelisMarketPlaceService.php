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
    const NEED_UPDATE = -1;
    const UP_TO_DATE  =  1;
    const IN_ADVANCE  =  2;


    /**
     * Function to get the local version of a module
     * and compare it from the repository to determine
     * whether the module is up to date or not
     *
     * @param $moduleName
     * @param $moduleVersion
     * @return array
     */
    public function compareLocalVersionFromRepo($moduleName = null, $moduleVersion = null){
        // Event parameters prepare
        $arrayParameters = $this->makeArrayFromParameters(__METHOD__, func_get_args());

        // Sending service start event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_start', $arrayParameters);

        $status        = null;
        $moduleSvc     = $this->getServiceLocator()->get('ModulesService');
        $tmpModName    = ($arrayParameters['moduleName'] == "MelisMarketplace") ? "MelisMarketPlace" : $arrayParameters['moduleName'] ;
        $modulesInfo   = $moduleSvc->getModulesAndVersions($tmpModName);
        $localVersion  = $modulesInfo['version'];

        //check if local version is advance or not
        if(substr(strtolower($localVersion), 0, 4) === "dev-"){
            $status = self::IN_ADVANCE;
        }
        else {

            //remove the v from the version and convert to float
            //to compare the version number
            $localV  = (float) str_replace('v', "", strtolower($localVersion));
            $latestV = (float) str_replace('v', "", strtolower($arrayParameters['moduleVersion']));

            //check if  local version is updated than the version in repo
            if($latestV <= $localV){
                $status = self::UP_TO_DATE;
            }else{
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