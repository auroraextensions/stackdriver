<?php
/**
 * LocalizedScopeDeploymentConfig.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/stackdriver/LICENSE.txt
 *
 * @package       AuroraExtensions\Stackdriver\Model\Config
 * @copyright     Copyright (C) 2020 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\Stackdriver\Model\Config;

use Magento\Framework\App\DeploymentConfig;

use const null;
use function sprintf;

class LocalizedScopeDeploymentConfig
{
    /** @constant string SCOPE */
    private const SCOPE = 'stackdriver';

    /** @var DeploymentConfig $deploymentConfig */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @return void
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param string|null $xpath
     * @return mixed
     */
    public function get(string $xpath = null)
    {
        /** @var string $path */
        $path = !empty($xpath) ? sprintf('%s/%s', self::SCOPE, $xpath) : self::SCOPE;
        return $this->deploymentConfig->get($path);
    }
}
