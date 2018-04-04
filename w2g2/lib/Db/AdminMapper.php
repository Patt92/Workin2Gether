<?php

namespace OCA\w2g2\Db;

class AdminMapper {
    protected $tableName;
    protected $lockMapper;

    public function __construct(LockMapper $lockMapper)
    {
        $this->tableName = 'locks_w2g2';
        $this->lockMapper = $lockMapper;
    }

    public function getLocks()
    {
        $lockedFiles = $this->lockMapper->all();

        $groupFolderName = "__groupfolders/";
        $fileName = 'files/';

        for ($i = 0; $i < count($lockedFiles); $i++) {
            $groupFolderIndex = strpos($lockedFiles[$i]['path'], $groupFolderName);
            $fileIndex = strpos($lockedFiles[$i]['path'], $fileName);

            if ($groupFolderIndex === 0) {
                $path = substr($lockedFiles[$i]['path'], strlen($groupFolderName));

                $slashIndex = strpos($path, '/');

                $groupFolderId = substr($path, 0, $slashIndex);
                $file = substr($path, $slashIndex + 1);

                $result = GroupFolderMapper::getMountPoints($groupFolderId);

                if ($result && count($result) > 0) {
                    $path = $result[0]['mount_point'];

                    $lockedFiles[$i]['path'] = $path . '/' . $file;
                }
            } else if ($fileIndex === 0) {
                $filePath = substr($lockedFiles[$i]['path'], strlen('files/'));
                $lockedFiles[$i]['path'] = $lockedFiles[$i]['locked_by'] . '/' . $filePath;
            }
        }

        return $lockedFiles;
    }
}
