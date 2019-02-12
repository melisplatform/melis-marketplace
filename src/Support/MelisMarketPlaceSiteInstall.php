<?php

namespace MelisMarketPlace\Support;

class MelisMarketPlaceSiteInstall
{
    /**
     * @const CONFIG holds the string value of "config"
     */
    const CONFIG = 'config';

    /**
     * @const DATA holds the string value of "data"
     */
    const DATA = 'data';

    /**
     * @const DATA_INSERT holds the string value of "insert"
     */
    const DATA_INSERT = 'insert';

    /**
     * @const DATA_UDPATE holds the string value of "update"
     */
    const DATA_UPDATE = 'update';

    /**
     * @const PAGES holds the string value of "pages"
     */
    const PAGES = 'pages';

    /**
     * @const DOWNLOAD holds the string value of "download"
     */
    const DOWNLOAD = 'download';

    /**
     * @const UPDATE holds the string value of "update"
     */
    const UPDATE = 'update';

    /**
     * const THEN holds the string value of "then"
     */
    const THEN = 'then';

    /**
     * @const UPDATE_CURRENT_SITE_ID callback function to trigger on a successful query transaction
     */
    const UPDATE_CURRENT_SITE_ID = 'incrementCurrentPlatformId';

    /**
     * @const UPDATE_CURRENT_TEMPLATE_ID callback function to trigger on a successful query transaction
     */
    const UPDATE_CURRENT_TEMPLATE_ID = 'incrementCurrentTemplateId';
}
