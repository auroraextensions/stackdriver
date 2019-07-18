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
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriverlogger/LICENSE.txt
 *
 * @package       AuroraExtensions_StackdriverLogger
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\StackdriverLogger\Model\Logger;

use AuroraExtensions\StackdriverLogger\Model\System\Module\Settings;
use Google\Cloud\Logging\LoggingClient;
use Magento\Framework\Logger\Monolog as FrameworkLogger;
use Psr\Log\LoggerInterface;

class Logger extends FrameworkLogger implements LoggerInterface
{
    /** @property LoggingClient $client */
    protected $client;

    /** @property Settings $settings */
    protected $settings;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param LoggingClient $client
     * @param Settings $settings
     * @return void
     */
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = [],
        LoggingClient $client,
        Settings $settings
    ) {
        parent::__construct(
            $name,
            $handlers,
            $processors
        );

        $this->client = $client;
        $this->settings = $settings;
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = [])
    {
        /** @var Google\Cloud\Logging\Logger $logger */
        $logger = $this->client->logger($this->settings->getLogChannel());
        $logger->write($message);

        return parent::addRecord($level, $message, $context);
    }
}
