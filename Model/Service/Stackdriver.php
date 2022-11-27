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
 * @package       AuroraExtensions\Stackdriver\Model\Service
 * @copyright     Copyright (C) 2020 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\Service;

use AuroraExtensions\ModuleComponents\Api\LocalizedScopeDeploymentConfigInterface;
use AuroraExtensions\ModuleComponents\Api\LocalizedScopeDeploymentConfigInterfaceFactory;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\Stackdriver\Api\StackdriverIntegrationInterface;
use AuroraExtensions\Stackdriver\Exception\InvalidStackdriverSetupException;
use Google\Cloud\Core\Report\SimpleMetadataProvider;
use Google\Cloud\ErrorReporting\Bootstrap;
use Google\Cloud\Logging\LoggingClient;
use Psr\Log\LoggerInterface;

use function __;

class Stackdriver implements StackdriverIntegrationInterface
{
    public const DEFAULT_SERVICE = 'main';

    /** @var LocalizedScopeDeploymentConfigInterface $deploymentConfig */
    private $deploymentConfig;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * @param LocalizedScopeDeploymentConfigInterfaceFactory $deploymentConfigFactory
     * @param ExceptionFactory $exceptionFactory
     * @return void
     */
    public function __construct(
        LocalizedScopeDeploymentConfigInterfaceFactory $deploymentConfigFactory,
        ExceptionFactory $exceptionFactory
    ) {
        $this->deploymentConfig = $deploymentConfigFactory->create(['scope' => 'stackdriver']);
        $this->exceptionFactory = $exceptionFactory;
        $this->initialize();
    }

    /**
     * @return void
     * @throws InvalidStackdriverSetupException
     */
    private function initialize(): void
    {
        /** @var string|null $projectName */
        $projectName = $this->deploymentConfig->get('logging/project_name');

        /** @var string|null $keyFilePath */
        $keyFilePath = $this->deploymentConfig->get('logging/key_file_path');

        if (!empty($projectName) && !empty($keyFilePath)) {
            /** @var string $logChannel */
            $logChannel = $this->deploymentConfig->get('logging/log_channel')
                ?? static::DEFAULT_SERVICE;

            /** @var LoggingClient $client */
            $client = new LoggingClient([
                'projectId' => $projectName,
                'keyFilePath' => $keyFilePath,
            ]);
            $this->logger = $client->psrLogger(
                $logChannel,
                [
                    'metadataProvider' => new SimpleMetadataProvider(
                        [],
                        $projectName,
                        $logChannel
                    ),
                ]
            );
            Bootstrap::init($this->logger);
        } else {
            /** @var InvalidStackdriverSetupException $exception */
            $exception = $this->exceptionFactory->create(
                InvalidStackdriverSetupException::class,
                __('Project name and/or key file path is invalid')
            );
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
