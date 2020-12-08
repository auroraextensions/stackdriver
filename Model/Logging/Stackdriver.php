<?php
/**
 * Stackdriver.php
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

use AuroraExtensions\Stackdriver\{
    Api\StackdriverIntegrationInterface,
    Model\System\Module\Settings
};
use Google\Cloud\{
    Core\Report\SimpleMetadataProvider,
    Logging\LoggingClient
};
use Psr\Log\LoggerInterface;

class Stackdriver implements StackdriverIntegrationInterface
{
    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * @param Settings $settings
     * @return void
     */
    public function __construct(Settings $settings)
    {
        /** @var LoggingClient $client */
        $client = new LoggingClient([
            'projectId' => $settings->getProjectName(),
            'keyFilePath' => $settings->getKeyFilePath(),
        ]);
        $this->logger = $client->psrLogger(
            $settings->getLogChannel(),
            [
                'metadataProvider' => new SimpleMetadataProvider(
                    [],
                    $settings->getProjectName(),
                    $settings->getLogChannel()
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
