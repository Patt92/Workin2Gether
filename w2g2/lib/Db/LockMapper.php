<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class LockMapper extends Mapper {
    public function __construct(IDbConnection $db)
    {
        parent::__construct($db, 'locks_w2g2', '\OCA\w2g2\Db\Lock');
    }

    public function all()
    {
        $query = "SELECT f.path, f.fileid, l.locked_by FROM " . $this->tableName . " AS l JOIN *PREFIX*" . 'filecache' . " as f ON l.file_id = f.fileid";

        return $this->db->executeQuery($query)
            ->fetchAll();
    }

    public function find($fileId)
    {
        $sql = 'SELECT * FROM ' . $this->tableName . ' WHERE file_id = ?';

        return $this->findEntity($sql, [$fileId]);
    }

    public function findAll()
    {
        $sql = 'SELECT * FROM ' . $this->tableName;

        return $this->findEntities($sql);
    }

    public function store(Lock $lock)
    {
        $sql = 'INSERT INTO ' . $this->tableName . ' (file_id, locked_by) VALUES (?, ?)';

        $this->db->executeQuery($sql, [$lock->getFileId(), $lock->getLockedBy()]);
    }

    public function deleteOne(Lock $lock)
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' WHERE file_id = ?';

        $this->db->executeQuery($sql, [$lock->getFileId()]);
    }

    public function deleteAll()
    {
        $sql = 'DELETE FROM ' . $this->tableName;

        $this->db->executeQuery($sql);
    }
}
