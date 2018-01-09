<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Validator\File\Size;
use Zend\Validator\File\IsImage;
use Zend\Validator\File\Upload;
use Zend\File\Transfer\Adapter\Http;
use Zend\Session\Container;

class MelisSetupController extends AbstractActionController
{
    public function setupFormAction()
    {
        return null;

    }

    public function setupResultAction()
    {
        return null;
    }
}