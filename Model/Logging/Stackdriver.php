<?php
/**
 * Stackdriver.php
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
    Core\Report\SimpleMetadataProvider,
    Logging\LoggingClient,
    Logging\PsrLogger
};

class Stackdriver
{
    /** @property LoggingClient $client */
    protected $client;

    /** @property PsrLogger $logger */
    protected $logger;

    /** @property SimpleMetadataProvider $metadataProvider */
    protected $metadataProvider;

    /** @property Settings $settings */
    protected $settings;

    /**
     * @param Settings $settings
     * @return void
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'projectId'   => $this->getSettings()->getProjectName(),
            'keyFilePath' => $this->getSettings()->getKeyFilePath(),
        ];
    }

    /**
     * @return LoggingClient
     */
    public function getLoggingClient(): LoggingClient
    {
        if (!$this->client) {
            $this->client = new LoggingClient($this->getConfig());
        }

        return $this->client;
    }

    /**
     * @return PsrLogger
     */
    public function getLogger(): PsrLogger
    {
        if (!$this->logger) {
            $this->logger = $this->getLoggingClient()->psrLogger(
                $this->getSettings()->getLogChannel(),
                [
                    'metadataProvider' => $this->getMetadataProvider(),
                ]
            );
        }

        return $this->logger;
    }

    /**
     * @return array
     */
    public function getLogLevels(): array
    {
        return $this->getSettings()->getLogLevelsArray();
    }

    /**
     * @return SimpleMetadataProvider
     */
    public function getMetadataProvider(): SimpleMetadataProvider
    {
        if (!$this->metadataProvider) {
            $this->metadataProvider = new SimpleMetadataProvider(
                [],
                $this->getConfig()['projectId'],
                $this->getSettings()->getLogChannel()
            );
        }

        return $this->metadataProvider;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }
}
