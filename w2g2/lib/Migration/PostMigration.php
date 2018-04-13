<?php

namespace OCA\w2g2\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ILogger;

class PostMigration implements IRepairStep {
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
        $this->insertDataFromTempTable();
        $this->dropTempTable();
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
                  WHERE table_name = '" . $this->tempTableName . "'";

        $result = $this->db->executeQuery($query)
            ->fetchAll();

        return is_array($result) && count($result) > 0;
    }

    /**
     * Insert the data from the temp table into the new table.
     *
     */
    protected function insertDataFromTempTable()
    {
        $files = $this->getLockedFiles();

        if (count($files) <= 0) {
            return;
        }

        // Add the data back in the table
        $insertQuery = "INSERT INTO " . $this->tableName . " (file_id, locked_by) VALUES ";

        $len = count($files);
        for ($i = 0; $i < $len; $i++) {
            $insertQuery .= "('" . $files[$i]['id'] . "', '" . $files[$i]['locked_by'] . "')";

            // Add a trailing comma if not the last one.
            if ($i != $len - 1) {
                $insertQuery .= ', ';
            }
        }

        $this->db->executeQuery($insertQuery);
    }

    /**
     * Get all files locked from the temporary table.
     *
     * @return array
     */
    protected function getLockedFiles()
    {
        $locksQuery = "SELECT * FROM " . $this->tempTableName;

        $locks = $this->db->executeQuery($locksQuery)
            ->fetchAll();

        $files = [];

        // Get all data in the table and store it temporarily to add it back later.
        if (count($locks) != 0) {
            $fileCacheQuery = "SELECT fileid FROM oc_filecache WHERE path=?";

            foreach ($locks as $lock) {
                $groupFolderIndex = strpos($lock['name'], '__groupfolders');
                $fileIndex = strpos($lock['name'], 'files/');
                $index = $groupFolderIndex ?: $fileIndex;

                if ($index) {
                    $fileName = substr($lock['name'], $index);

                    $result = $this->db->executeQuery($fileCacheQuery, [$fileName])
                        ->fetchAll();

                    // Check if the file with the given path exits.
                    if (
                        $result &&
                        is_array($result) &&
                        count($result) > 0 &&
                        array_key_exists('fileid', $result[0]) &&
                        $result[0]['fileid']
                    ) {
                        $files[] = [
                            'id' => $result[0]['fileid'],
                            'locked_by' => $lock['locked_by']
                        ];
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Drop the temp table.
     *
     */
    protected function dropTempTable()
    {
        $dropStatement = "DROP TABLE " . $this->tempTableName;

        $this->db->executeQuery($dropStatement);
    }
}
