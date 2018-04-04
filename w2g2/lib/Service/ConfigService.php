<?php

namespace OCA\w2g2\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\w2g2\Db\ConfigMapper;

class ConfigService {
    public function getColor()
    {
        return ConfigMapper::getColor();
    }

    public function getFontColor()
    {
        return ConfigMapper::getFontColor();
    }

    public function getDirectoryLock()
    {
        return ConfigMapper::getDirectoryLock();
    }

    public function getLockingByNameRule()
    {
        return ConfigMapper::getLockingByNameRule();
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
