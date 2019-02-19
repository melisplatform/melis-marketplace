<?php

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;
use MelisMarketPlace\Exception\ArrayKeyNotFoundException;
use MelisMarketPlace\Exception\EmptySiteException;
use MelisMarketPlace\Exception\PlatformIdMaxRangeReachedException;
use MelisMarketPlace\Support\MelisMarketPlaceCmsTables as Melis;
use MelisMarketPlace\Support\MelisMarketPlaceSiteInstall as Site;
use PDO;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Http\PhpEnvironment\Request;

class MelisMarketPlaceSiteService extends MelisCoreGeneralService
{
    const VAL_TABLE = 0;
    const VAL_FIELD = 1;
    const VAL_VALUE = 2;
    const VAL_RETURN_FIELD = 3;
    /**
     * @var \Zend\Db\Adapter\Adapter $adapter
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

    /**
     * @param \Zend\Http\PhpEnvironment\Request $request
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
        $action = $request->getPost('action');

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

            $siteDomain = $this->siteDomainTable()->save([
                'sdom_site_id' => $siteId,
                'sdom_env' => $platformName,
                'sdom_scheme' => $scheme,
                'sdom_domain' => $domain,
            ]);

        } else {
            throw new EmptySiteException('Site data is empty', 500);
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
        $table = $this->getServiceLocator()->get('MelisEngineTablePlatformIds');

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
     * @return array|\ArrayObject|\Zend\Db\ResultSet\ResultSet|null
     */
    protected function getPlatform($env = null)
    {
        $env = $env ?: getenv('MELIS_PLATFORM');

        /** @var \MelisCore\Model\Tables\MelisPlatformTable $platformTable */
        $platformTable = $this->getServiceLocator()->get('MelisPlatformTable');
        $platform = $platformTable->getEntryByField('plf_name', $env)->current();

        return $platform;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSiteTable
     */
    private function siteTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteTable $siteTable */
        $siteTable = $this->getServiceLocator()->get('MelisEngineTableSite');

        return $siteTable;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSiteDomainTable
     */
    public function siteDomainTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteDomainTable $siteDomain */
        $siteDomain = $this->getServiceLocator()->get('MelisEngineTableSiteDomain');

        return $siteDomain;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisPageTreeTable
     */
    private function pageTreeTable()
    {
        /** @var \MelisEngine\Model\Tables\MelisPageTreeTable $pageTreeTable */
        $pageTreeTable = $this->getServiceLocator()->get('MelisEngineTablePageTree');

        return $pageTreeTable;
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

            $this->incrementCurrentPageId();
        } else {
            throw new ArrayKeyNotFoundException("{$this->getAction()} key not found in {$this->getAction()} configuration");
        }

        return $this;
    }

    /**
     * @return \MelisCore\Service\MelisCoreModulesService
     */
    protected function moduleManager()
    {
        /** @var \MelisCore\Service\MelisCoreModulesService $service */
        $service = $this->getServiceLocator()->get('ModulesService');

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
     * @inheritdoc
     * Sets the Database adapter that will be used when querying
     * the database, this will use the configuration set
     * on the database config file
     *
     * @return $this
     */
    private function setDbAdapter()
    {
        /** @var \Zend\Config\Config $config */
        $config = $this->getServiceLocator()->get('config');
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
     * @param null|string $path
     *
     * @return array
     */
    public function getConfig($path = null)
    {
        return $this->config()->getItem($path ? $this->getModule() . "/{$this->getAction()}/$path" : "{$this->getModule()}/{$this->getAction()}");
    }

    /**
     * @return \MelisCore\Service\MelisCoreConfigService
     */
    private function config()
    {
        /** @var \MelisCore\Service\MelisCoreConfigService $config */
        $config = $this->getServiceLocator()->get('MelisCoreConfig');

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
     * @param null $parentTable
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
                                if (is_array($value)) {
                                    $fn = current(array_keys($value));
                                    $args = array_values($value[$fn]);
                                    $fields .= "`$field`, ";
                                    $fieldValues .= "'" . $this->$fn($args) . "', ";
                                } else {
                                    $fields .= "`$field`, ";
                                    $fieldValues .= "'$value', ";
                                }
                            }

                            switch ($field) {
                                case Site::THEN:
                                    $queries[$table][$idx][Site::THEN] = $value;
                                    break;
                                case Melis::PRIMARY_KEY:
                                    $queries[$table][$idx][Melis::PRIMARY_KEY] = $value;
                                    break;
                                case Melis::RELATION:
                                    $queries[$table][$idx][Melis::RELATION] = $this->createInsertSql($value, $table);
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
                    Melis::CURRENT_PAGE_ID,
                ], [
                    $lastInsertedId,
                    $this->getSiteId(),
                    $this->getCurrentPageId(),
                ], $transact[Melis::SQL]);

                if ($insertedId = $this->insert($sql, $primaryKey)) {

                    // Execute set callbacks in the configuration array table
                    if (isset($transact[Site::THEN])) {
                        foreach ($transact[Site::THEN] as $fnOrKey => $fn) {
                            $this->$fn();
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
     * Returns the main page ID of the selected site module
     *
     * @return null|int
     */
    public function getSiteId()
    {
        /** @var \MelisEngine\Model\Tables\MelisSiteTable $siteTable */
        $siteTable = $this->getServiceLocator()->get('MelisEngineTableSite');

        $select = $siteTable->getTableGateway()->getSql()->select();

        $select->where->equalTo('site_name', $this->getModule());

        $resultSet = $siteTable->getTableGateway()->selectWith($select)->toArray();

        if ($resultSet) {
            return end($resultSet)['site_main_page_id'];
        }

        return null;
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
                $selectQuery = $this->createSelectSql($sql, $primaryKey);
                $result = $this->getAdapter()->query($selectQuery, DbAdapter::QUERY_MODE_EXECUTE)->toArray();
                $id = (int) current($result)[$primaryKey] ?? null;
            }

        }

        return $id;
    }

    /**
     * Returns the instance of DbAdapter
     *
     * @return \Zend\Db\Adapter\Adapter
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

        $fields = '';
        if ($table) {

            if (is_array($selection) && count($selection) > 1) {
                $fields = implode(', ', $selection);
            } else {
                $fields = current($selection) ?: $selection;
            }

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
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @return $this
     */
    private function incrementCurrentTemplateId()
    {
        $this->platformIdTable()->save([
            'pids_tpl_id_current' => ((int) $this->getCurrentPlatformId()->pids_tpl_id_current) + 1,
        ], $this->getPlatformId());

        return $this;
    }

    /**
     * @return \MelisEngine\Model\Tables\MelisSite404Table
     */
    private function site404Table()
    {
        /** @var \MelisEngine\Model\Tables\MelisSite404Table $site404 */
        $site404 = $this->getServiceLocator()->get('MelisEngineTableSite404');

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
}