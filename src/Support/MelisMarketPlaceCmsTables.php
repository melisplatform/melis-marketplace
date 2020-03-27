<?php

namespace MelisMarketPlace\Support;

use MelisCore\Support\MelisTables;

/**
 * This is a list of known Melis CMS tables
 *
 * Class MelisMarketPlaceTables
 * @package MelisMarketPlace\Support
 */
class MelisMarketPlaceCmsTables extends MelisTables
{
    const CMS_DOMAIN_ROBOTS = 'melis_cms_domain_robots';
    const CMS_LANG = 'melis_cms_lang';
    const CMS_NEWS = 'melis_cms_news';
    const CMS_NEWS_TEXTS = 'melis_cms_news_texts';
    const CMS_PAGE_ANALYTICS_DATA = 'melis_cms_page_analytics_data';
    const CMS_PAGE_ANALYTICS_DATA_SETTINGS = 'melis_cms_page_analytics_data_settings';
    const CMS_PAGE_DEFAULT_URLS = 'melis_cms_page_default_urls';
    const CMS_PAGE_LANG = 'melis_cms_page_lang';
    const CMS_PAGE_PUBLISHED = 'melis_cms_page_published';
    const CMS_PAGE_SAVED = 'melis_cms_page_saved';
    const CMS_PAGE_SEO = 'melis_cms_page_seo';
    const CMS_PAGE_STYLE = 'melis_cms_page_style';
    const CMS_PAGE_TREE = 'melis_cms_page_tree';
    const CMS_PLATFORM_ID = 'melis_cms_platform_ids';
    const CMS_PROSPECTS = 'melis_cms_prospects';
    const CMS_PROSPECTS_THEMES = 'melis_cms_prospects_themes';
    const CMS_PROSPECTS_THEMES_ITEMS = 'melis_cms_prospects_theme_items';
    const CMS_PROSPECTS_THEMES_ITEMS_TRANSLATIONS = 'melis_cms_prospects_theme_items_trans';
    const CMS_SITE = 'melis_cms_site';
    const CMS_SITE_301 = 'melis_cms_site_301';
    const CMS_SITE_404 = 'melis_cms_site_404';
    const CMS_SITE_DOMAIN = 'melis_cms_site_domain';
    const CMS_SLIDER = 'melis_cms_slider';
    const CMS_SLIDER_DETAILS = 'melis_cms_slider_details';
    const CMS_STYLE = 'melis_cms_style';
    const CMS_TEMPLATE = 'melis_cms_template';
    const CMS_GDPR_TEXTS = 'melis_cms_gdpr_texts';

    /**
     * @const CMS_TOTAL_PAGE holds the string value of "melis_cms_total_page" in configuration
     */
    const CMS_TOTAL_PAGE = 'melis_cms_total_page';

    /**
     * @const CMS_SITE_DEFAULT_PARENT_ID default parent value of Site ID
     */
    const CMS_SITE_DEFAULT_PARENT_ID = '-1';

    /**
     * @const CMS_SITE_ID string value to swap
     */
    const CMS_SITE_ID = '%site_id%';

    /**
     * @const CMS_SITE_HOME_PAGE_ID string value to swap
     */
    const CMS_SITE_HOME_PAGE_ID = '%site_home_page_id%';

    /**
     * @const CURRENT_PAGE_ID holds the string value of "%current_page_id%", which will be used to swap
     * with the current available page ID, if there's no available page ID, it will throw PlatformIdMaxRangeReachedException
     *
     * @throws \MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException
     */
    const CURRENT_PAGE_ID = '%current_page_id%';

    /**
     * @const CURRENT_TEMPLATE_ID holds the string value of "%current_template_id%", which will be used to swap
     * with the current available template ID, if there's no available template ID, it will throw PlatformIdMaxRangeReachedException
     *
     * @throws \MelisMarketPlace\Exception\TemplateIdMaxRangeReachedException
     */
    const CURRENT_TEMPLATE_ID = '%current_template_id%';

}
