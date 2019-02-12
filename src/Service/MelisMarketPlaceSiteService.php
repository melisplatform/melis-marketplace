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

        if ($scheme && $name && $domain && $module && $action) {

            $this->setModule($module)->setAction($action);

            $siteId = 1;
            $platformIds = $this->getCurrentPlatformId();
            $totalPage = $this->getConfig(Site::CONFIG)[Melis::CMS_TOTAL_PAGE];
            $platformName = $this->getPlatform()->plf_name;
            $platformId = $this->getPlatformId();

            if (!$platformIds) {
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
            } else {
                // update the platform IDs
                $siteId = $platformIds->pids_page_id_current + 1;
                if ($siteId > $platformIds->pids_page_id_end) {
                    throw new PlatformIdMaxRangeReachedException(
                        "Maximum of {$siteId}/{$platformIds->pids_page_id_end} site ID for {$platformName} platform has been reached.",
                        500);
                }
            }

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

            if ($siteTable && $siteDomain) {
                $this->incrementCurrentPlatformId();
            }

        } else {
            throw new EmptySiteException('Site data is empty', 500);
        }

        return $this;
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
     * @return $this
     */
    private function incrementCurrentPlatformId()
    {
        $this->platformIdTable()->save([
            'pids_page_id_current' => ((int) $this->getCurrentPlatformId()->pids_page_id_current) + 1,
        ], $this->getPlatformId());

        return $this;
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

            $queries = $this->createInsertSql($dataConfig);

            $this->processTransactions($queries);

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
                            if (! in_array($field, [Melis::RELATION, Site::THEN])) {
                                $fields .= "`$field`, ";
                                $fieldValues .= "'$value', ";
                            } else if ($field === Site::THEN) {
                                $queries[$table][$idx][Site::THEN] = $value;
                            } else {
                                $queries[$table][$idx][Melis::RELATION] = $this->createInsertSql($value, $table);
                            }
                        }

                        $sql = "INSERT INTO `$table`(" . substr($fields, 0, strlen($fields) - 2) . ") VALUES(" . substr($fieldValues, 0, strlen($fieldValues) - 2) . ');' . PHP_EOL;
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
     * @param null|int $insertedId
     * @param array $preservedForeignKeys
     */
    protected function processTransactions($queries, $insertedId = null, $preservedForeignKeys = [])
    {
        $foreignKey = Melis::FOREIGN_KEY;

        foreach ($queries as $table => $transaction) {
            foreach ($transaction as $key => $transact) {

                if (strpos($transact[Melis::SQL], Melis::ROOT_FOREIGN_KEY) !== false) {
                    // for root foreign key, use the last insertedId in the preserved foreign keys
                    $insertedId = end($preservedForeignKeys) ?? -1;
                    $foreignKey = Melis::ROOT_FOREIGN_KEY;
                }

                if ($insertedId) {
                    // avoid insertedId collision, solution: make insertedId as its own key with the same value
                    $preservedForeignKeys = array_merge($preservedForeignKeys, [$insertedId => $insertedId]);
                }

                $sql = str_replace([$foreignKey, Melis::CMS_SITE_ID], [$insertedId, $this->getSiteId()], $transact[Melis::SQL]);

                if ($insertedId = $this->insert($sql)) {

                    if (isset($transact[Site::THEN])) {
                        foreach ($transact[Site::THEN] as $fn) {
                            $this->$fn();
                        }
                    }

                    if (isset($transact[Melis::RELATION]) && count($transact[Melis::RELATION])) {
                        $this->processTransactions($transact[Melis::RELATION], $insertedId, $preservedForeignKeys);
                    }
                }
            }
        }

    }

    /**
     * @param $sql
     *
     * @return int|null
     */
    private function insert($sql)
    {
        if ($this->getAdapter()) {
            $statement = $this->getAdapter()->createStatement($sql);
            $result = $statement->execute();
            if ($lastInsertId = $result->getGeneratedValue()) {
                return (int) $lastInsertId;
            }
        }

        return null;
    }

    /**
     * Returns the instance of DbAdapter
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    private function getAdapter()
    {
        $this->setDbAdapter();

        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
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
     * @return \MelisEngine\Model\Tables\MelisSite404Table
     */
    private function site404Table()
    {
        /** @var \MelisEngine\Model\Tables\MelisSite404Table $site404 */
        $site404 = $this->getServiceLocator()->get('MelisEngineTableSite404');

        return $site404;
    }
}
