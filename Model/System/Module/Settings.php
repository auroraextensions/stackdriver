<?php
/**
 * Settings.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriver/LICENSE.txt
 *
 * @package       AuroraExtensions_Stackdriver
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\System\Module;

use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    DataObject,
    DataObject\Factory as DataObjectFactory
};
use Magento\Store\{
    Model\ScopeInterface as StoreScopeInterface,
    Model\Store
};

class Settings
{
    /** @property DataObject $container */
    protected $container;

    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->container = $dataObjectFactory->create($data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return DataObject|null
     */
    public function getContainer(): ?DataObject
    {
        return $this->container;
    }

    /**
     * @param int $store
     * @param string $scope
     * @return bool
     */
    public function isModuleEnabled(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'stackdriver/general/enable',
            $scope,
            $store
        );
    }

    /**
     * @param int $store
     * @param string $scope
     * @return string
     */
    public function getKeyFilePath(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'stackdriver/general/key_file_path',
            $scope,
            $store
        );
    }

    /**
     * @param int $store
     * @param string $scope
     * @return string
     */
    public function getProjectName(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'stackdriver/general/gcp_project',
            $scope,
            $store
        );
    }

    /**
     * @param int $store
     * @param string $scope
     * @return string
     */
    public function getLogChannel(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'stackdriver/logging/log_channel',
            $scope,
            $store
        );
    }

    /**
     * @param int $store
     * @param string $scope
     * @return bool
     */
    public function includeLogContext(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'stackdriver/logging/include_log_context',
            $scope,
            $store
        );
    }
}
