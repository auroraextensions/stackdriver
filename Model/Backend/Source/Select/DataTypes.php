<?php
/**
 * DataTypes.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriverlogger/LICENSE.txt
 *
 * @package       AuroraExtensions_Stackdriver
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\Backend\Source\Select;

use AuroraExtensions\Stackdriver\Model\System\Module\Settings;
use Magento\Framework\Option\ArrayInterface;

class DataTypes implements ArrayInterface
{
    /** @property array $options */
    protected $options = [];

    /**
     * @param Settings $settings
     * @return void
     */
    public function __construct(Settings $settings)
    {
        /** @var array $dataTypes */
        $dataTypes = array_flip($settings->getDataTypes());

        array_walk(
            $dataTypes,
            [
                $this,
                'setOption'
            ]
        );
    }

    /**
     * @param int|string|null $value
     * @param int|string $key
     * @return void
     */
    protected function setOption($value, $key): void
    {
        $this->options[] = [
            'label' => __($key),
            'value' => $value,
        ];
    }

    /**
     * Get formatted option key/value pairs.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->options;
    }
}
