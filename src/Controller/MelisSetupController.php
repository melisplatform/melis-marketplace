<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisMarketPlace\Controller;

use MelisCore\Controller\MelisAbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Upload;
use Laminas\File\Transfer\Adapter\Http;
use Laminas\Session\Container;

class MelisSetupController extends MelisAbstractActionController
{
}