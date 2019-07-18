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
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriverlogger/LICENSE.txt
 *
 * @package       AuroraExtensions_StackdriverLogger
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\StackdriverLogger\Model\System\Module;

use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    DataObject,
    DataObjectFactory
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
    protected function getContainer(): ?DataObject
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getDataTypes(): array
    {
        return $this->getContainer()->getData('data_types') ?? [];
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
            'stackdriverlogger/logging/log_channel',
            $scope,
            $store
        );
    }
}
