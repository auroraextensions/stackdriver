<?php
/**
 * Logger.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriver/LICENSE.txt
 *
 * @package       AuroraExtensions\Stackdriver\Model\Logging
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\Logging;

use Exception;
use AuroraExtensions\Stackdriver\{
    Api\StackdriverAwareLoggerInterface,
    Api\StackdriverIntegrationInterface,
    Model\System\Module\Settings
};
use Google\Cloud\Logging\Logger as GoogleCloudLogger;
use Magento\Framework\Logger\Monolog;
use Psr\Log\{
    InvalidArgumentException,
    LoggerInterface
};

use function array_merge;
use function in_array;
use function is_numeric;
use function strtolower;

class Logger extends Monolog implements LoggerInterface, StackdriverAwareLoggerInterface
{
    /** @var StackdriverAwareLoggerInterface $stackdriver */
    private $stackdriver;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param Settings $settings
     * @param StackdriverIntegrationInterface $stackdriver
     * @return void
     */
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = [],
        Settings $settings,
        StackdriverIntegrationInterface $stackdriver
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
     * {@inheritdoc}
     */
    public function getStackdriver(): StackdriverIntegrationInterface
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
            $logLevel = is_numeric($level) ? strtolower($levelMap[$level]) : $level;

            /** @var array $logLevels */
            $logLevels = $this->getSettings()->getLogLevelsArray();

            if (in_array($logLevel, $logLevels)) {
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

                try {
                    $logger->log($level, $message, $options);
                } catch (InvalidArgumentException | Exception $e) {
                    /* No action required. */
                }
            }
        }

        return parent::addRecord($level, $message, $context);
    }
}
