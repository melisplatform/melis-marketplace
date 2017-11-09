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
    /**
     * Function to ge the local version of a module
     * and compare it form the repository to determine
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

        $data = array();
        $moduleSvc   = $this->getServiceLocator()->get('ModulesService');
        $temp_mod_name = ($arrayParameters['moduleName'] == "MelisMarketplace") ? "MelisMarketPlace" : $arrayParameters['moduleName'] ;
        $modulesInfo = $moduleSvc->getModulesAndVersions($temp_mod_name);
        $local_version = $modulesInfo['version'];
        //check if local version is advance or not
        if(substr(strtolower($local_version), 0, 4) === "dev-"){
            $data['version_status'] = "tr_market_place_version_in_advance";
        }else{
            //remove the v from the version and convert to float
            //to compare the version number
            $local_v = (float) str_replace('v', "", strtolower($local_version));
            $latest_v = (float) str_replace('v', "", strtolower($arrayParameters['moduleVersion']));
            //check if local version is updated than the version in repo
            if($latest_v <= $local_v){
                $data['version_status'] = "tr_market_place_version_up_to_date";
            }else{
                $data['version_status'] = "tr_market_place_version_update";
            }
        }

        // Adding results to parameters for events treatment if needed
        $arrayParameters['results'] = $data;
        // Sending service end event
        $arrayParameters = $this->sendEvent('melismarketplace_compare_local_version_from_repo_end', $arrayParameters);

        return $arrayParameters['results'];
    }
}