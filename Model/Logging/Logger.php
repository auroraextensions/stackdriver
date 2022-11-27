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
 * @copyright     Copyright (C) 2020 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\Logging;

use Exception;
use AuroraExtensions\ModuleComponents\Api\LocalizedScopeDeploymentConfigInterface;
use AuroraExtensions\ModuleComponents\Api\LocalizedScopeDeploymentConfigInterfaceFactory;
use AuroraExtensions\Stackdriver\Api\ReportedErrorEventMetadataProviderInterface;
use AuroraExtensions\Stackdriver\Api\StackdriverAwareLoggerInterface;
use AuroraExtensions\Stackdriver\Api\StackdriverIntegrationInterface;
use Google\Cloud\Logging\Logger as GoogleCloudLogger;
use Monolog\Logger as Monolog;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use function array_merge;
use function in_array;
use function strtolower;

class Logger extends Monolog implements
    StackdriverAwareLoggerInterface,
    ReportedErrorEventMetadataProviderInterface
{
    /** @var LocalizedScopeDeploymentConfigInterface $deploymentConfig */
    private $deploymentConfig;

    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param LocalizedScopeDeploymentConfigInterfaceFactory $deploymentConfigFactory
     * @param StackdriverIntegrationInterface $stackdriver
     * @return void
     */
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = [],
        LocalizedScopeDeploymentConfigInterfaceFactory $deploymentConfigFactory,
        StackdriverIntegrationInterface $stackdriver
    ) {
        parent::__construct(
            $name,
            $handlers,
            $processors
        );
        $this->deploymentConfig = $deploymentConfigFactory->create(['scope' => 'stackdriver']);
        $this->logger = $stackdriver->getLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLogLevels(): array
    {
        return (array) $this->deploymentConfig->get('logging/log_levels');
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeUrn(): string
    {
        return $this->deploymentConfig->get('error_reporting/type_urn');
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param int|string $level
     * @param string|Exception $message
     * @param array $context
     * @return bool
     */
    public function addRecord(
        $level,
        $message,
        array $context = []
    ): bool {
        /** @var bool $isLoggingEnabled */
        $isLoggingEnabled = !$this->deploymentConfig->get('logging/disabled');

        if ($isLoggingEnabled) {
            /** @var array $levelMap */
            $levelMap = GoogleCloudLogger::getLogLevelMap();

            /** @var string $logLevel */
            $logLevel = strtolower(
                $levelMap[$level] ?? (string) $level
            );

            if (in_array($logLevel, $this->getLogLevels())) {
                /** @var array $options */
                $options = [];

                /** @var bool $isErrorReportingEnabled */
                $isErrorReportingEnabled = !$this->deploymentConfig->get('error_reporting/disabled');

                if ($isErrorReportingEnabled) {
                    $options['@type'] = $this->getTypeUrn();
                }

                /** @var bool $includeContext */
                $includeContext = (bool) $this->deploymentConfig->get('logging/include_context');

                if ($includeContext) {
                    $options = array_merge(
                        $options,
                        $context
                    );
                }

                try {
                    $this->logger->log(
                        $level,
                        $message,
                        $options
                    );
                } catch (InvalidArgumentException | Exception $e) {
                    parent::addRecord(
                        LogLevel::ERROR,
                        $e->getMessage(),
                        ['exception' => $e]
                    );
                }
            }
        }

        if ($message instanceof Exception && !isset($context['exception'])) {
            $context['exception'] = $message;
        }

        return parent::addRecord(
            $level,
            $message instanceof Exception ? $message->getMessage() : $message,
            $context
        );
    }
}
