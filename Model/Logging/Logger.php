<?php
/**
 * Logger.php
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

namespace AuroraExtensions\Stackdriver\Model\Logging;

use AuroraExtensions\Stackdriver\Model\System\Module\Settings;
use Google\Cloud\Logging\Logger as GoogleCloudLogger;
use Magento\Framework\Logger\Monolog;
use Psr\Log\LoggerInterface;

class Logger extends Monolog implements LoggerInterface
{
    /** @property Stackdriver $stackdriver */
    protected $stackdriver;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param Settings $settings
     * @param Stackdriver $stackdriver
     * @return void
     */
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = [],
        Settings $settings,
        Stackdriver $stackdriver
    ) {
        parent::__construct(
            $name,
            $handlers,
            $processors
        );

        $this->settings = $settings;
        $this->stackdriver = $stackdriver;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return Stackdriver
     */
    public function getStackdriver(): Stackdriver
    {
        return $this->stackdriver;
    }

    /**
     * @param int|string $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($this->getSettings()->isModuleEnabled()) {
            /** @var array $levelMap */
            $levelMap = GoogleCloudLogger::getLogLevelMap();

            /** @var string $logLevel */
            $logLevel = is_numeric($level)
                ? strtolower($levelMap[$level])
                : $level;

            /** @var array $logLevels */
            $logLevels = $this->getStackdriver()->getLogLevels();

            if (in_array($logLevel, $logLevels)) {
                try {
                    /** @var Google\Cloud\Logging\PsrLogger $logger */
                    $logger = $this->getStackdriver()->getLogger();

                    /** @var array $options */
                    $options = [];

                    if ($this->getSettings()->isErrorReportingEnabled()) {
                        $options['@type'] = $this->getSettings()->getTypeUrn();
                    }

                    if ($this->getSettings()->includeContext()) {
                        $options = array_merge(
                            $options,
                            $context
                        );
                    }

                    $logger->log($level, $message, $options);
                } catch (\Exception $e) {
                    /* No action required. */
                }
            }
        }

        return parent::addRecord($level, $message, $context);
    }
}
