<?php

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;
use MelisMarketPlace\Exception\ArrayKeyNotFoundException;
use MelisMarketPlace\Exception\EmptySiteException;
use MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException;
use MelisMarketPlace\Exception\TemplateIdMaxRangeReachedException;
use MelisMarketPlace\Support\MelisMarketPlaceCmsTables as Melis;
use MelisMarketPlace\Support\MelisMarketPlaceSiteInstall as Site;
use PDO;
use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Http\PhpEnvironment\Request;

class MelisMarketPlaceSiteService extends MelisCoreGeneralService
{
    /**
     * @var \Laminas\Db\Adapter\Adapter $adapter
     */
    protected $adapter;

    /**
     * @var string $setupFile - setup config file to lookup
     */
    protected $setupFile;

    /**
     * @var string $module - name of the module to process
     */
    protected $module;

    /**
     * @var string $action - set what type of action is being done
     */
    protected $action;

    const VAL_TABLE = 'table';
    const VAL_FIELD = 'field';
    const VAL_VALUE = 'value';
    const VAL_RETURN_FIELD = 'return';
    const ACTION_REQUIRE = 'require';

    /**
     * @param \Laminas\Http\PhpEnvironment\Request $request
     *
     * @return $this
     * @throws \MelisMarketPlace\Exception\EmptySiteException
     * @throws \MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException
     */
    public function installSite(Request $request)
    {
        $name = $request->getPost('name');
        $scheme = $request->getPost('scheme');
        $domain = $request->getPost('domain');
        $module = $request->getPost('module');
        $action = $request->getPost('action', 'download') === self::ACTION_REQUIRE ? Site::DOWNLOAD : Site::DOWNLOAD;

        $this->setDbAdapter();

        if ($scheme && $name && $domain && $module && $action) {

            $this->setModule($module)->setAction($action);

            $siteId = $this->getCurrentPageId();
            $platformName = $this->getPlatform()->plf_name;

            $siteTable = $this->siteTable()->save([
                'site_name' => $module,
                'site_label' => $name,
                'site_main_page_id' => $siteId,
            ]);

            /**
             * save the site home page language id
             */
            $this->siteHomeTable()->save([
                'shome_site_id' => $siteTable,
                'shome_lang_id' => 1,
                'shome_page_id' => $siteId
            ]);
            /**
             * Save the site lang id
             */
            $this->siteLangsTable()->save([
                'slang_site_id' => $siteId,
                'slang_lang_id' => 1,
            ]);
        } else {
            throw new EmptySiteException('Site data is empty', 500);
        }

        return $this;
    }

    /**
     * @param \Laminas\Http\PhpEnvironment\Request $request
     *
     * @return $this
     * @throws \MelisMarketPlace\Exception\EmptySiteException
     * @throws \MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException
     */
    public function marketplaceInstallSite(Request $request)
    {
        $name = $request->getPost('name');
        $scheme = $request->getPost('scheme');
        $domain = $request->getPost('domain');
        $module = $request->getPost('module');
        $action = $request->getPost('action', 'download') === self::ACTION_REQUIRE ? Site::DOWNLOAD : Site::DOWNLOAD;

        $this->setDbAdapter();

        if ($scheme && $name && $domain && $module && $action) {

            $this->setModule($module)->setAction($action);

            $siteId = $this->getCurrentPageId();
            $platformName = $this->getPlatform()->plf_name;
            $page404Id = (int)$siteId + 36;

            $siteTable = $this->siteTable()->save([
                'site_name' => $module,
                'site_label' => $name,
                'site_main_page_id' => $siteId,
            ]);

            $this->siteDomainTable()->save([
                'sdom_site_id' => $siteTable,
                'sdom_domain' => $domain,
                'sdom_scheme' => $scheme,
                'sdom_env' => getenv('MELIS_PLATFORM'),
            ]);

            /**
             * save the site home page language id
             */
            $this->siteHomeTable()->save([
                'shome_site_id' => $siteTable,
                'shome_lang_id' => 1,
                'shome_page_id' => $siteId
            ]);
            /**
             * Save the site lang id
             */
            $this->siteLangsTable()->save([
                'slang_site_id' => $siteTable,
                'slang_lang_id' => 1,
            ]);
            /**
             * Save the site 404 page od
             */
            $this->site404Table()->save([
                's404_site_id' => $siteTable,
                's404_page_id' => $page404Id,
            ]);
        } else {
            throw new EmptySiteException('Site data is empty', 500);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * Sets the Database adapter that will be used when querying
     * the database, this will use the configuration set
     * on the database config file
     *
     * @return $this
     */
    private function setDbAdapter()
    {
        /** @var \Laminas\Config\Config $config */
        $config = $this->getServiceManager()->get('config');
        $db = $config['db'];

        if ($db) {

            $driver = $db['driver'];
            $dsn = $db['dsn'];
            $username = $db['username'];
            $password = $db['password'];

            $this->adapter = new DbAdapter([
                'driver' => $driver,
                'dsn' => $dsn,
                'username' => $username,
                'password' => $password,
                'driver_options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                ],
            ]);
        }

        return $this;
    }

    /**
     * Returns the current available page ID
     *
     * @return int
     * @throws \MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException
     */
    private function getCurrentPageId()
    {
        $pageId = 1;
        $platformIds = $this->getCurrentPlatformId();

        if ($platformIds) {
            $pageId = $platformIds->pids_page_id_current;
            if ($pageId > $platformIds->pids_page_id_end) {
                throw new PlatformIdMaxRangeReachedException(
                    "Maximum of {$pageId}/{$platformIds->pids_page_id_end} site ID for {$platformName} platform has been reached.",
                    500);
            }
        } else {
            // create a set of Platform IDs
            $platformId = $this->getPlatformId();

            $startId = $platformId;
            $endId = $platformId * 1000;

            $platformIdPayload = [
                'pids_id' => $platformId,
                'pids_page_id_start' => $startId,
                'pids_page_id_current' => $startId,
                'pids_page_id_end' => $endId,
                'pids_tpl_id_start' => $startId,
                'pids_tpl_id_current' => 1,
                'pids_tpl_id_end' => $endId,
            ];

            $this->platformIdTable()->save($platformIdPayload);
        }

        return $pageId;
    }

    /**
     * @return array|\ArrayObject|null
     */
    private function getCurrentPlatformId()
    {
        $platformIds = $this->platformIdTable()->getEntryById($this->getPlatformId())->current();

        return $platformIds;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisPlatformIdsTable
     */
    private function platformIdTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisPlatformIdsTable $table */
        $table = $this->getServiceManager()->get('MelisEngineTablePlatformIds');

        return $table;
    }

    /**
     * @return int
     */
    private function getPlatformId()
    {
        $platformId = (int) $this->getPlatform()->plf_id ?: 1;

        return $platformId;
    }

    /**
     * @param string $env
     *
     * @return array|\ArrayObject|\Laminas\Db\ResultSet\ResultSet|null
     */
    protected function getPlatform($env = null)
    {
        $env = $env ?: getenv('MELIS_PLATFORM');

        /** @var \MelisCore\Model\Tables\MelisPlatformTable $platformTable */
        $platformTable = $this->getServiceManager()->get('MelisPlatformTable');
        $platform = $platformTable->getEntryByField('plf_name', $env)->current();

        return $platform;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSiteTable
     */
    private function siteTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteTable $siteTable */
        $siteTable = $this->getServiceManager()->get('MelisEngineTableSite');

        return $siteTable;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSiteDomainTable
     */
    public function siteDomainTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteDomainTable $siteDomain */
        $siteDomain = $this->getServiceManager()->get('MelisEngineTableSiteDomain');

        return $siteDomain;
    }

    private function siteHomeTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisCmsSiteHomeTable $siteHomeTable */
        $siteHomeTable = $this->getServiceManager()->get('MelisEngineTableCmsSiteHome');
        return $siteHomeTable;
    }

    private function siteLangsTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisCmsSiteLangsTable $siteHomeTable */
        $sitelangsTable = $this->getServiceManager()->get('MelisEngineTableCmsSiteLangs');
        return $sitelangsTable;
    }

    /**
     * @return $this
     * @throws \MelisMarketPlace\Exception\ArrayKeyNotFoundException
     */
    public function invokeSetup()
    {
        $path = $this->moduleManager()->getComposerModulePath($this->getModule()) ?:
            $this->moduleManager()->getModulePath($this->getModule());

        $this->setDbAdapter();

        if (is_array($this->getConfig()) && $this->getConfig()) {
            $config = $this->getConfig();

            if (!isset($config[Site::DATA])) {
                throw new ArrayKeyNotFoundException(Site::DATA . " key not found in {$this->getAction()} configuration");
            }

            $dataConfig = $config[Site::DATA];

            // By default, we are forcing to process CMS Template, to easily query CMS Template later on
            $templates = $dataConfig[Melis::CMS_TEMPLATE];
            $templateQuery = $this->createInsertSql([Melis::CMS_TEMPLATE => $templates]);
            $this->processTransactions($templateQuery);

            // Next is to process the Page Tree where we build and save the structure and the contents of the pages
            $pageTree = $dataConfig[Melis::CMS_PAGE_TREE];
            $pageTreeQuery = $this->createInsertSql([Melis::CMS_PAGE_TREE => $pageTree]);
            $this->processTransactions($pageTreeQuery);

            // to avoid duplicates
            unset($dataConfig[Melis::CMS_TEMPLATE]);
            unset($dataConfig[Melis::CMS_PAGE_TREE]);

            $queries = $this->createInsertSql($dataConfig);
            $this->processTransactions($queries);

            /** @var \MelisEngine\Service\MelisTreeService $pageTreeSvc */
            $pageTreeSvc = $this->getServiceManager()->get('MelisEngineTree');
            $pageTreeMap = $pageTreeSvc->getAllPages($this->getSiteId());
                                                                                                //site id                                                           //site main page id
            $this->sendEvent('melis_marketplace_site_install_results', ['site_id' => $this->getIdSite(), 'pages' => $pageTreeMap, 'site_home_page_id' => $this->getSiteId()]);

        } else {
            throw new ArrayKeyNotFoundException("{$this->getAction()} key not found in {$this->getAction()} configuration");
        }

        return $this;
    }

    /**
     * @return \MelisAssetManager\Service\MelisCoreModulesService
     */
    protected function moduleManager()
    {
        /** @var \MelisAssetManager\Service\MelisCoreModulesService $service */
        $service = $this->getServiceManager()->get('MelisAssetManagerModulesService');

        return $service;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @param null|string $path
     *
     * @return array
     */
    public function getConfig($path = null)
    {
        return $this->config()->getItem($path ? $this->getModule() . "/{$this->getAction()}/$path" : "{$this->getModule()}/{$this->getAction()}");
    }

    /**
     * @return \MelisAssetManager\Service\MelisCoreConfigService
     */
    private function config()
    {
        /** @var \MelisAssetManager\Service\MelisCoreConfigService $config */
        $config = $this->getServiceManager()->get('MelisConfig');

        return $config;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Converts data map configuration into an array of SQL statements recursively
     *
     * @param $tables
     * @param null|string $parentTable
     *
     * @return array
     */
    protected function createInsertSql($tables, $parentTable = null)
    {
        $sql = '';
        $queries = [];

        // loop through all the tables in data
        foreach ($tables as $table => $tableData) {
            if ($tableData) {
                foreach ($tableData as $idx => $payload) {

                    if ($payload) {
                        $fields = '';
                        $fieldValues = '';

                        // loop through all the fields and values in the data
                        foreach ($payload as $field => $value) {
                            $recursionSql = '';
                            // escape
                            $value = str_replace(["'", '"'], ["\'", '\"'], $value);

                            if (!in_array($field, [Melis::RELATION, Site::THEN, Melis::PRIMARY_KEY])) {

                                if ($table === Melis::CMS_PAGE_PUBLISHED) {
                                    if ($field === 'page_name') {
                                        $this->pageMap[$value] = '';
                                    }
                                }

                                if (is_array($value)) {

                                    $fn = current(array_keys($value));
                                    $args = $value[$fn];
                                    $fields .= "`$field`, ";
                                    $fieldValues .= "'" . $this->$fn($args) . "', ";
                                } else {
                                    $fields .= "`$field`, ";
                                    $fieldValues .= "'$value', ";
                                }
                            }

                            switch ($field) {
                                case Melis::PRIMARY_KEY:
                                    $queries[$table][$idx][Melis::PRIMARY_KEY] = $value;
                                    break;
                                case Melis::RELATION:
                                    $queries[$table][$idx][Melis::RELATION] = $this->createInsertSql($value, $table);
                                    break;
                                case Site::THEN:
                                    $queries[$table][$idx][Site::THEN] = $value;
                                    break;
                            }
                        }

                        $sql = "INSERT INTO `$table`(" . substr($fields, 0, strlen($fields) - 2) . ") VALUES("
                            . substr($fieldValues, 0, strlen($fieldValues) - 2) . ');' . PHP_EOL;
                        $queries[$table][$idx][Melis::SQL] = $sql;
                    }
                }
            }
        }

        return $queries;
    }

    /**
     * Reads and executes the SQL array recursively
     *
     * @param $queries
     * @param null $insertedId
     *
     * @throws \MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException
     */
    protected function processTransactions($queries, $insertedId = null)
    {

        $foreignKey = Melis::FOREIGN_KEY;
        $lastInsertedId = $insertedId;

        foreach ($queries as $table => $transaction) {

            foreach ($transaction as $key => $transact) {

                $primaryKey = $transact[Melis::PRIMARY_KEY] ?? null;

                if (strpos($transact[Melis::SQL], Melis::ROOT_FOREIGN_KEY) !== false) {
                    // for root foreign key, use the last insertedId in the preserved foreign keys
                    $foreignKey = Melis::ROOT_FOREIGN_KEY;
                }

                // swap temporary values into real values
                $sql = str_replace([
                    $foreignKey,
                    Melis::CMS_SITE_ID,
                    Melis::CMS_SITE_HOME_PAGE_ID,
                    Melis::CURRENT_PAGE_ID,
                    Melis::CURRENT_TEMPLATE_ID,
                ], [
                    $lastInsertedId,
                    $this->getIdSite(),
                    $this->getSiteId(),
                    $this->getCurrentPageId(),
                    $this->getCurrentTemplateId(),
                ], $transact[Melis::SQL]);

                if ($insertedId = $this->insert($sql, $primaryKey)) {

                    $this->sendEvent('melis_marketplace_site_install_inserted_id', ['table_name' => $table, 'id' => $insertedId, 'sql' => $sql]);

                    // Execute set callbacks in the configuration array table
                    if (isset($transact[Site::THEN])) {
                        foreach ($transact[Site::THEN] as $fnOrKey => $fn) {
                            if (is_string($fn)) {
                                $this->$fn();
                            }

                            if (is_array($fn)) {
                                $this->$fnOrKey($fn);
                            }
                        }
                    }

                    if (isset($transact[Melis::RELATION]) && count($transact[Melis::RELATION])) {
                        $this->processTransactions($transact[Melis::RELATION], $insertedId);
                    }
                }
            }
        }
    }

    /**
     * Returns the site id
     *
     * @return null|int
     */
    public function getIdSite()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteTable $siteTable */
        $siteTable = $this->getServiceLocator()->get('MelisEngineTableSite');

        $select = $siteTable->getTableGateway()->getSql()->select();

        $select->where->equalTo('site_name', $this->getModule());

        $resultSet = $siteTable->getTableGateway()->selectWith($select)->toArray();

        if ($resultSet) {
            return end($resultSet)['site_id'];
        }

        return null;
    }

    /**
     * Returns the main page ID of the selected site module
     *
     * @return null|int
     */
    public function getSiteId()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteTable $siteTable */
        $siteTable = $this->getServiceManager()->get('MelisEngineTableSite');

        $select = $siteTable->getTableGateway()->getSql()->select();

        $select->where->equalTo('site_name', $this->getModule());

        $resultSet = $siteTable->getTableGateway()->selectWith($select)->toArray();

        if ($resultSet) {
            return end($resultSet)['site_main_page_id'];
        }

        return null;
    }

    /**
     * Returns the current available template ID
     *
     * @return int
     * @throws \MelisMarketPlace\Exception\TemplateIdMaxRangeReachedException
     */
    private function getCurrentTemplateId()
    {
        $templateId = 1;
        $platformIds = $this->getCurrentPlatformId();

        if ($platformIds) {
            $templateId = $platformIds->pids_tpl_id_current;
            if ($templateId > $platformIds->pids_tpl_id_end) {
                throw new TemplateIdMaxRangeReachedException(
                    "Maximum of {$templateId}/{$platformIds->pids_tpl_id_end} template ID for {$platformName} platform has been reached.",
                    500);
            }
        }

        return $templateId;
    }

    /**
     * @param string $sql
     * @param null|int $primaryKey - will be used to query the inserted data if no primary key is set in the table
     *
     * @return int|null
     */
    private function insert($sql, $primaryKey = null)
    {
        $id = null;

        if ($this->getAdapter()) {

            $statement = $this->getAdapter()->createStatement($sql);
            $result = $statement->execute();

            if ($lastInsertId = $result->getGeneratedValue()) {
                $id = (int) $lastInsertId;
            }

            if (is_null($id) || !$id && !is_null($primaryKey)) {
                // create a SELECT query
                $selectQuery = $this->createSelectSql($sql, [$primaryKey]);
                $result = $this->getAdapter()->query($selectQuery, DbAdapter::QUERY_MODE_EXECUTE)->toArray();
                $id = (int) current($result)[$primaryKey] ?? null;
            }
        }

        return $id;
    }

    /**
     * Returns the instance of DbAdapter
     *
     * @return \Laminas\Db\Adapter\Adapter
     */
    private function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $sql
     * @param string|array $selection
     *
     * @return string
     */
    private function createSelectSql($sql, $selection = '*')
    {

        // extract the columns that will be used as expressions
        $queryFields = preg_match('/\(\`+(.?)+\`\)/', $sql, $queryFieldMatches);
        $queryFieldMatches = array_map(function ($a) {
            $a = str_replace(['(', ')'], '', trim($a));

            return $a;
        }, $queryFieldMatches);

        $fieldExpression = [];
        foreach ($queryFieldMatches as $idx => $queryExpression) {
            if (!is_null($queryExpression) && !empty($queryExpression) && $queryExpression != '') {
                $fieldExpression = explode(', ', $queryExpression);
            }
        }

        // extract the values that will be used as expressions
        $queryValues = preg_match('/VALUES\(+(.?)+\)/', $sql, $queryValueMatches);
        $queryValueMatches = array_map(function ($a) {
            $a = str_replace(['(', ')', 'VALUES'], '', trim($a));

            return $a;
        }, $queryValueMatches);

        $valueExpression = [];
        foreach ($queryValueMatches as $idx => $queryExpression) {
            if (!is_null($queryExpression) && !empty($queryExpression) && $queryExpression != '') {
                $valueExpression = explode(', ', $queryExpression);
            }
        }

        $table = preg_match('/INSERT\sINTO\s\`(.*?)\`/', $sql, $matches);
        $transaction = 'SELECT';
        foreach ($matches as $matched) {
            if (strpos($matched, 'INSERT INTO') === false) {
                $table = $matched;
            }
        }

        $fields = ! is_array($selection) ? $selection : implode(', ', $selection);

        if ($table) {

            $transaction .= " $fields FROM `$table`";

            if ($fieldExpression &&
                $valueExpression &&
                count($fieldExpression) === count($valueExpression)) {

                $transaction .= " WHERE ";
                foreach ($fieldExpression as $idx => $field) {
                    $value = $valueExpression[$idx] ?? '';
                    if (count($fieldExpression) - 1 !== $idx) {
                        $transaction .= "$field = $value AND ";
                    } else {
                        $transaction .= "$field = $value LIMIT 1;";
                    }
                }
            }
        }

        return $transaction;
    }

    /**
     * @param int $incremental
     *
     * @return $this
     */
    private function incrementCurrentPageId($incremental = 0)
    {
        $this->platformIdTable()->save([
            'pids_page_id_current' => ((int) $this->getCurrentPlatformId()->pids_page_id_current) + 1 + $incremental,
        ], $this->getPlatformId());

        return $this;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisPageTreeTable
     */
    private function pageTreeTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisPageTreeTable $pageTreeTable */
        $pageTreeTable = $this->getServiceManager()->get('MelisEngineTablePageTree');

        return $pageTreeTable;
    }

    /**
     * @return $this
     */
    private function incrementCurrentTemplateId()
    {
        $this->platformIdTable()->save([
            'pids_tpl_id_current' => ((int) $this->getCurrentTemplateId()) + 1,
        ], $this->getPlatformId());

        return $this;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSite404Table
     */
    private function site404Table()
    {
        /** @var \MelisEngine\Model\Tables\MelisSite404Table $site404 */
        $site404 = $this->getServiceManager()->get('MelisEngineTableSite404');

        return $site404;
    }

    /**
     * @param $args
     *
     * @return null|string|int
     */
    private function getFieldValue($args)
    {
        $table = $args[static::VAL_TABLE] ?? null;
        $field = $args[static::VAL_FIELD] ?? null;
        $value = $args[static::VAL_VALUE] ?? null;
        $returnField = $args[static::VAL_RETURN_FIELD];

        if ($table && $field && $value && $returnField) {
            $sql = "SELECT $returnField FROM `$table` WHERE `$field` = '$value' LIMIT 1;";
            $result = $this->getAdapter()->query($sql, DbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $value = current($result)[$returnField] ?? null;

            return $value;
        }

        return null;
    }

    /**
     * Used to flatten a multi-dimensional array
     *
     * @param $array
     *
     * @return array
     */
    private function flattenArray($array)
    {
        $flat = [];

        array_walk_recursive($array, function ($a) use (&$flat) {
            $flat[] = $a;
        });

        return $flat;
    }

    /**
     * Retrieves the corresponding Page ID
     *
     * @param $args
     * @param-suggest string $page_name
     *
     * @return null|int|string
     */
    private function getPageId($args)
    {
        $siteId = $this->getSiteId();
        $pageName = $args['page_name'] ?? null;
        $return = 'tree_page_id';

        $melisCmsPageTree = Melis::CMS_PAGE_TREE;
        $melisCmsPagePublished = Melis::CMS_PAGE_PUBLISHED;
        $pageId = null;

        if ($siteId && $pageName && $return) {

            $transaction = "SELECT $return FROM `$melisCmsPageTree` " .
                "LEFT JOIN `$melisCmsPagePublished` ON `tree_page_id` = `page_id` WHERE " .
                "`tree_father_page_id` = $siteId AND `page_name` = '$pageName';";

            if ($result = $this->getAdapter()->query($transaction, DbAdapter::QUERY_MODE_EXECUTE)->toArray()) {
                $pageId = current($result)[$return] ?? null;
            }
        }

        return $pageId;
    }

    /**
     * Retrieves the corresponding Template ID of the Site
     *
     * @param $args
     * @param-suggest string $template_name
     *
     * @return null|int|string
     */
    private function getTemplateId($args)
    {
        $siteId = $this->getIdSite();
        $templateName = $args['template_name'] ?? null;
        $return = 'tpl_id';

        $melisCmsTemplates = Melis::CMS_TEMPLATE;
        $templateId = null;

        if ($siteId && $templateName && $return) {
            $transaction = "SELECT $return FROM `$melisCmsTemplates` WHERE `tpl_site_id` = $siteId AND `tpl_name` = '$templateName'";

            if ($result = $this->getAdapter()->query($transaction, DbAdapter::QUERY_MODE_EXECUTE)->toArray()) {
                $templateId = current($result)[$return] ?? null;
            }
        }

        return  $templateId;
    }

    /**
     * Triggers an event with arguments merging the Site configuration and the $args paramater
     *
     * @param $args
     * @param-suggest string $event_name
     * @param-suggest mixed $params
     *
     * @return array|null
     */
    protected function triggerEvent($args)
    {
        $eventName = $args['event_name'] ?? null;
        $params = $args['params'] ?? null;

        if ($eventName && $params) {
            $config = $this->getConfig();
            $dataConfig = $config[Site::DATA];

            /**
             * Replacing values before pass to events
             */
            foreach ($params as $key => $value) {
                $params[$key] = str_replace([
                            Melis::CMS_SITE_ID,
                            Melis::CMS_SITE_HOME_PAGE_ID,
                            Melis::CURRENT_PAGE_ID,
                            Melis::CURRENT_TEMPLATE_ID,
                        ], [
                            $this->getIdSite(),
                            $this->getSiteId(),
                            $this->getCurrentPageId(),
                            $this->getCurrentTemplateId(),
                        ], 
                        $value
                    );
            }

            $params = array_merge($dataConfig, ['params' => $params]);

            return $this->sendEvent($eventName, $params);
        }

        return null;
    }
}
