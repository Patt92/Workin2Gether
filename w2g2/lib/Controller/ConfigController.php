<?php

namespace OCA\w2g2\Controller;

use OCA\w2g2\Db\ConfigMapper;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\w2g2\Migration\UpdateDatabase;
use OCA\w2g2\Service\ConfigService;

class ConfigController extends Controller {
    protected $appName;
    protected $service;

    public function __construct($AppName, IRequest $request, ConfigService $configService)
    {
        parent::__construct($AppName, $request);

        $this->appName = $AppName;
        $this->service = $configService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param $type
     * @return DataResponse
     */
    public function getColor($type)
    {
        if ($type === 'color') {
            $result = $this->service->getColor();
        } else {
            $result = $this->service->getFontColor();

        }

        return new DataResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function directoryLock()
    {
        $result = $this->service->getDirectoryLock();

        return new DataResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return DataResponse
     */
    public function updateDatabase()
    {
        $updateDatabase = new UpdateDatabase();

        return new DataResponse($updateDatabase->run());
    }

    /**
     * @NoAdminRequired
     *
     * @param $type
     * @param $value
     * @return DataResponse
     */
    public function update($type, $value)
    {
        $l = \OCP\Util::getL10N($this->appName);
        $configValue = \OC::$server->getConfig()->getAppValue('w2g2', $type, '[unset]');

        if ($configValue == '[unset]') {
            ConfigMapper::store($type, $value);

            return new DataResponse($l->t($type) . " " . $l->t("has been set!"));
        }

        ConfigMapper::update($type, $value);

        return new DataResponse($l->t("Updated successfully!"));
    }
}