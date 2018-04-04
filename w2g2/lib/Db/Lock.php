<?php

namespace OCA\w2g2\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Lock extends Entity implements JsonSerializable {
    protected $fileId;
    protected $lockedBy;
    protected $created;

    public function jsonSerialize() {
        return [
            'fileId' => $this->fileId,
            'lockedBy' => $this->lockedBy,
            'created' => $this->created
        ];
    }
}
