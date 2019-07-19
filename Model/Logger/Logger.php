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

namespace AuroraExtensions\Stackdriver\Model\Logger;

use AuroraExtensions\Stackdriver\Model\System\Module\Settings;
use Google\Cloud\Logging\LoggingClient;
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
            'projectId'   => $this->settings->getProjectName(),
            'keyFilePath' => $this->settings->getKeyFilePath(),
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
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($this->settings->isModuleEnabled()) {
            try {
                /** @var Google\Cloud\Logging\LoggingClient $client */
                $client = $this->getLoggingClient();

                /** @var Google\Cloud\Logging\Logger $logger */
                $logger = $client->logger($this->settings->getLogChannel());
                $logger->write($message);
            } catch (\Exception $e) {
                /* No action required. */
            }
        }

        return parent::addRecord($level, $message, $context);
    }
}
