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
    DataObject,
    DataObjectFactory
};

class Settings
{
    /** @property DataObject $container */
    protected $container;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->container = $dataObjectFactory->create($data);
    }

    /**
     * @return DataObject|null
     */
    public function getContainer(): ?DataObject
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
}
