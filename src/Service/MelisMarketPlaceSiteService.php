<?php

namespace MelisMarketPlace\Service;

use MelisCore\Service\MelisCoreGeneralService;
use MelisMarketPlace\Exception\FileNotFoundException;
use MelisMarketPlace\Exception\ArrayKeyNotFoundException;
use MelisMarketPlace\Support\MelisMarketPlaceSiteInstall as Site;
use MelisMarketPlace\Support\MelisMarketPlaceCmsTables as Melis;
use PDO;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Ddl;

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
     * @var array $config - used to store the setup configuration
     */
    protected $config;

    /**
     * @param $module
     * @param $action
     *
     * @return $this
     * @throws \MelisMarketPlace\Exception\FileNotFoundException
     */
    public function invokeSetup($module, $action)
    {
        $this->setModule($module)->setAction($action);

        $path = $this->moduleManager()->getComposerModulePath($this->getModule()) ?:
            $this->moduleManager()->getModulePath($this->getModule());

        $this->setupFile("$path/config/setup/{$this->getAction()}.config.php")->setDbAdapter();

        if (!file_exists($this->getSetupFile())) {
            throw new FileNotFoundException("Setup config file does not exists. File {$this->getSetupFile()}");
        }

        $this->setConfig(require $this->getSetupFile());

        if (is_array($this->getConfig()) && isset($this->getConfig()['setup'])) {
            $config = $this->getConfig()['setup'];

            if (!isset($config[Site::DATA])) {
                throw new ArrayKeyNotFoundException(Site::DATA . ' key not found in ' . $this->getSetupFile());
            }

            if (!isset($config[Site::DATA][Site::DATA_INSERT])) {
                throw new ArrayKeyNotFoundException(Site::DATA_INSERT . ' key not found in ' . $this->getSetupFile());
            }

            $dataConfig = $config[Site::DATA][Site::DATA_INSERT];
            $queries = $this->createInsertSql($dataConfig);

            // remove this
//            $this->clearTable();
            // end

            $this->processTransactions($queries);
//            dd($queries);
        } else {
            throw new ArrayKeyNotFoundException('`setup` key not found in ' . $this->getSetupFile());
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
     * @param $setupFileDownload
     *
     * @return $this
     */
    protected function setupFile($setupFile)
    {
        $this->setupFile = $setupFile;

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
     * @return string
     */
    protected function getSetupFile()
    {
        return $this->setupFile;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

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
     * @param string $env
     *
     * @return array|\ArrayObject|\Zend\Db\ResultSet\ResultSet|null
     */
    protected function getPlatform($env = null)
    {
        $env = $env ?: getenv('MELIS_PLATFORM');

        /** @var \MelisCore\Model\Tables\MelisPlatformTable $platformTable */
        $platformTable = $this->getServiceLocator()->get('MelisPlatformTable');
        $platform = $platformTable->getEntryByField($platformTable::NAME, $env)->current();

        return $platform;
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
     * @inheritdoc
     * Sets the Database adapter that will be used when querying
     * the database, this will use the configuration set
     * on the database config file
     */
    private function setDbAdapter()
    {
        // access the database configuration
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
                            if ($field != Melis::RELATION) {
                                $fields .= "`$field`, ";
                                $fieldValues .= "'$value', ";
                            } else {
                                $queries[$table][$idx][Melis::RELATION] = $this->createInsertSql($value, $table);
                            }
                        }

                        $sql = "INSERT INTO `$table`(" . substr($fields, 0, strlen($fields) - 2) . ") VALUES(" .  substr($fieldValues, 0, strlen($fieldValues) - 2) . ');' . PHP_EOL;
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
            foreach ($transaction as $idx => $transact) {

                if (strpos($transact[Melis::SQL], Melis::ROOT_FOREIGN_KEY) !== false) {
                    $insertedId = end($preservedForeignKeys) ?? -1;
                    $foreignKey = Melis::ROOT_FOREIGN_KEY;
                }

                if ($insertedId) {
                    $preservedForeignKeys = array_merge($preservedForeignKeys, [$insertedId => $insertedId]);
                }

                $sql = str_replace($foreignKey, $insertedId, $transact[Melis::SQL]);

                if ($insertedId = $this->insert($sql)) {
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
     * @todo remove this
     */
    private function clearTable()
    {
        $this->getAdapter()->createStatement('
        truncate table melis_cms_prospects;truncate table melis_cms_prospects_themes;truncate table melis_cms_prospects_theme_items;truncate table melis_cms_prospects_theme_items_trans;
        ')->execute();
    }
}
