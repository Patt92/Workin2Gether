<?php

namespace OCA\w2g2\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ILogger;

class PreMigration implements IRepairStep {
    /** @var ILogger */
    protected $logger;

    protected $tableName;
    protected $tempTableName;
    protected $db;

    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;

        $this->tableName = "oc_locks_w2g2";
        $this->tempTableName = "oc_locks_w2g2_temp";

        $this->db = \OC::$server->getDatabaseConnection();
    }

    /**
     * Returns the step's name
     */
    public function getName()
    {
        return 'Database migration!';
    }

    /**
     * @param IOutput $output
     * @return void
     */
    public function run(IOutput $output)
    {
        if ( ! $this->isOldVersion()) {
            return;
        }

        // Old database version. Migrate.
        $this->createTempTable();
        $this->insertDataInTempTable();
        $this->dropOldTable();
    }

    /**
     * Check if the app is still using the old database version.
     *
     * @return bool
     */
    protected function isOldVersion()
    {
        $query = "SELECT column_name
                  FROM information_schema.columns
                  WHERE table_name = '" . $this->tableName . "' and column_name = 'name'";

        $result = $this->db->executeQuery($query)
            ->fetchAll();

        return is_array($result) && count($result) > 0;
    }

    /**
     * Create a temporary table to store the old table data until the migration to the new format is done.
     *
     */
    protected function createTempTable()
    {
        $createStatement = "CREATE TABLE " . $this->tempTableName . " (name varchar(255) PRIMARY KEY, created TIMESTAMP DEFAULT CURRENT_TIMESTAMP, locked_by varchar(255))";

        $this->db->executeQuery($createStatement);
    }

    /**
     * Insert the data from the old table into the temporary one until the migration to the new format is done.
     *
     */
    protected function insertDataInTempTable()
    {
        $insertStatement = "INSERT INTO " . $this->tempTableName .
            " SELECT * FROM " . $this->tableName;

        $this->db->executeQuery($insertStatement);
    }

    /**
     * Drop the old table and the new one will be created by the Nextcloud migration.
     *
     */
    protected function dropOldTable()
    {
        $dropStatement = "DROP TABLE " . $this->tableName;

        $this->db->executeQuery($dropStatement);
    }
}
