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
use Google\Cloud\{
    Logging\Logger as StackdriverLogger,
    Logging\LoggingClient
};
use Magento\Framework\{
    Exception\LocalizedException,
    Logger\Monolog
};
use Psr\Log\LoggerInterface;

class Logger extends Monolog implements LoggerInterface
{
    /** @property LoggingClient $client */
    protected $client;

    /** @property array $config */
    protected $config;

    /** @property Settings $settings */
    protected $settings;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param Settings $settings
     * @return void
     */
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = [],
        Settings $settings
    ) {
        parent::__construct(
            $name,
            $handlers,
            $processors
        );

        $this->settings = $settings;
    }

    /**
     * @return array
     */
    protected function getConfig(): array
    {
        return [
            'projectId'   => $this->getSettings()->getProjectName(),
            'keyFilePath' => $this->getSettings()->getKeyFilePath(),
        ];
    }

    /**
     * @return LoggingClient
     */
    protected function getLoggingClient(): LoggingClient
    {
        if (!$this->client) {
            $this->client = new LoggingClient($this->getConfig());
        }

        return $this->client;
    }

    /**
     * @return Settings
     */
    protected function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * Get log levels selected from admin.
     *
     * @return array
     */
    protected function getLogLevels(): array
    {
        return $this->getSettings()->getLogLevelsArray();
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
            $levelMap = StackdriverLogger::getLogLevelMap();

            /** @var string $logLevel */
            $logLevel = is_numeric($level)
                ? strtolower($levelMap[$level])
                : $level;

            /** @var array $logLevels */
            $logLevels = $this->getLogLevels();

            if (in_array($logLevel, $logLevels)) {
                try {
                    /** @var Google\Cloud\Logging\LoggingClient $client */
                    $client = $this->getLoggingClient();

                    /** @var Google\Cloud\Logging\PsrLogger $logger */
                    $logger = $client->psrLogger($this->getSettings()->getLogChannel());

                    /** @var array $options */
                    $options = $this->getSettings()->includeLogContext() ? $context : [];

                    $logger->log($level, $message, $options);
                } catch (\Exception $e) {
                    /* No action required. */
                }
            }
        }

        return parent::addRecord($level, $message, $context);
    }
}
