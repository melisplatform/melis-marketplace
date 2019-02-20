<?php

namespace MelisMarketPlace\Support;

class MelisMarketPlaceSiteInstall
{
    /**
     * @const CONFIG holds the string value of "config"
     */
    const CONFIG = 'config';

    /**
     * @const DATA holds the string value of "_DATA"
     */
    const DATA = '_DATA';

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
     * const THEN holds the string value of "_THEN"
     */
    const THEN = '_THEN';

    /**
     * @const UPDATE_CURRENT_SITE_ID callback function to trigger on a successful query transaction
     */
    const UPDATE_CURRENT_PAGE_ID = 'incrementCurrentPageId';

    /**
     * @const UPDATE_CURRENT_TEMPLATE_ID callback function to trigger on a successful query transaction
     */
    const UPDATE_CURRENT_TEMPLATE_ID = 'incrementCurrentTemplateId';

    /**
     * @const TRIGGER_EVENT Triggers an event with arguments merging the Site configuration and the $args paramater
     *
     * @param $args
     * @param-suggest string $event_name
     * @param-suggest mixed $params
     *
     * @return array|null
     */
     const TRIGGER_EVENT = 'triggerEvent';

    /**
     * @const GET_FIELD_VALUE callback function to trigger on a successful query transaction, this requires a parameter
     * that returns the defined field
     *
     * @var string $table
     * @var string $field
     * @var string|int $value
     * @var string $returnField
     *
     * @return null|string|int
     */
    const GET_FIELD_VALUE = 'getFieldValue';

    /**
     * @const GET_PAGE_ID Retrieves the corresponding Page ID
     *
     * @param $args
     * @param-suggest int $site_id | key: "site_id"
     * @param-suggest string $page_name | key: "page_name"
     * @param-suggest mixed $return | key: "return"
     *
     * @return null|int|string
     */
    const GET_PAGE_ID = 'getPageId';

    /**
     * @const GET_TEMPLATE_ID Retrieves the corresponding Template ID of the Site
     *
     * @param $args
     * @param-suggest int $site_id | key: "site_id"
     * @param-suggest string $template_name | key: "template_name"
     * @param-suggest mixed $return | key: "return"
     *
     * @return null|int|string
     */
    const GET_TEMPLATE_ID = 'getPageId';
}
