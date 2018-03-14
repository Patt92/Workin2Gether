<?php

namespace OCA\w2g2\Migration;

class UpdateDatabase
{
    protected $tableName;

    public function __construct()
    {
        $this->tableName = "oc_locks_w2g2";
    }

    public function run()
    {
        if ( ! $this->shouldUpdate()) {
            return;
        }

        $this->update();

        return 'done';
    }

    protected function shouldUpdate()
    {
        $updateCheckQuery = "SELECT column_name
                  FROM information_schema.columns
                  WHERE table_name = '" . $this->tableName . "' and column_name = 'name'";

        $result = \OCP\DB::prepare($updateCheckQuery)
            ->execute()
            ->fetchAll();

        return is_array($result) && count($result) > 0;
    }

    protected function update()
    {
        $locksQuery = "SELECT * FROM " . $this->tableName;

        $locks = \OCP\DB::prepare($locksQuery)
            ->execute()
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

                    $result = \OCP\DB::prepare($fileCacheQuery)
                        ->execute([$fileName])
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

            $deleteQuery = "DELETE FROM " . $this->tableName;
            \OCP\DB::prepare($deleteQuery)->execute();
        }

        $renameQuery = "ALTER TABLE " . $this->tableName . " RENAME COLUMN name TO file_id";
        $typeQuery = "ALTER TABLE " . $this->tableName . " ALTER COLUMN file_id TYPE INT USING file_id::integer";

        \OCP\DB::prepare($renameQuery)->execute();
        \OCP\DB::prepare($typeQuery)->execute();

        // Add the data back in the table
        if (count($files) > 0) {
            $insertQuery = "INSERT INTO " . $this->tableName . " (file_id, locked_by) VALUES ";

            $len = count($files);
            for ($i = 0; $i < $len; $i++) {
                $insertQuery .= "('" . $files[$i]['id'] . "', '" . $files[$i]['locked_by'] . "')";

                // Add a trailing comma if not the last one.
                if ($i != $len - 1) {
                    $insertQuery .= ', ';
                }
            }

            \OCP\DB::prepare($insertQuery)->execute();
        }
    }
}
