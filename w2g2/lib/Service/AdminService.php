<?php

namespace OCA\w2g2\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\w2g2\Db\AdminMapper;

class AdminService {
    private $mapper;

    public function __construct(AdminMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getLocks()
    {
        return $this->mapper->getLocks();
    }

    private function handleException($e)
    {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}