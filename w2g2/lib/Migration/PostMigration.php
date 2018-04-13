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
            foreach ($locks as $lock) {
                $files[] = $this->getLock($lock);
            }
        }

        return $files;
    }

    protected function getLock($lock)
    {
        $fileCacheQuery = "SELECT fileid FROM *PREFIX*filecache WHERE path=?";

        $groupFolderIndex = strpos($lock['name'], '__groupfolders');

        if ($groupFolderIndex) {
            $fileName = substr($lock['name'], $groupFolderIndex);

            $result = $this->db->executeQuery($fileCacheQuery, [$fileName])
                ->fetchAll();
        } else {
            // Ordinary file
            $firstSlashIndex = strpos($lock['name'], '/') ;
            $storageOwner = substr($lock['name'], 0, $firstSlashIndex);
            $storageId = "home::" . $storageOwner;

            $storageQuery = "SELECT * FROM *PREFIX*storages WHERE id=?";

            $result = $this->db->executeQuery($storageQuery, [$storageId])
                ->fetchAll();

            $storageNumericId = $result && count($result) > 0 ? $result[0]['numeric_id'] : null;

            $filePath = substr($lock['name'], $firstSlashIndex + 1);

            $fileQuery = "SELECT file.fileid
                        FROM *PREFIX*filecache file 
                        INNER JOIN *PREFIX*storages storage 
                        ON file.storage = storage.numeric_id 
                        WHERE file.path = ? 
                        AND storage.numeric_id = ?
                        LIMIT 1";

            $result = $this->db->executeQuery($fileQuery, [$filePath, $storageNumericId])
                ->fetchAll();
        }

        // Check if the file with the given path exits.
        if (
            $result &&
            is_array($result) &&
            count($result) > 0 &&
            array_key_exists('fileid', $result[0]) &&
            $result[0]['fileid']
        ) {
            return [
                'id' => $result[0]['fileid'],
                'locked_by' => $lock['locked_by']
            ];
        }
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
