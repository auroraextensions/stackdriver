<?php
/**
 * StackdriverAwareLocalizedException.php
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

namespace AuroraExtensions\Stackdriver\Exception;

use AuroraExtensions\Stackdriver\Model\Logging\Stackdriver;
use Google\Cloud\ErrorReporting\Bootstrap;
use Magento\Framework\{
    Exception\LocalizedException,
    Phrase
};

class StackdriverAwareLocalizedException extends LocalizedException
{
    /** @property PsrLogger $logger */
    public static $logger;

    /** @property Stackdriver $stackdriver */
    protected $stackdriver;

    /**
     * @param Phrase $phrase
     * @param Exception|null $cause
     * @param int|string $code
     * @param Stackdriver|null $stackdriver
     * @return void
     */
    public function __construct(
        Phrase $phrase,
        \Exception $cause = null,
        $code = 0,
        Stackdriver $stackdriver = null
    ) {
        parent::__construct(
            $phrase,
            $cause,
            $code
        );
        $this->stackdriver = $stackdriver;

        if (!self::$logger) {
            $this->initLogger();
        }
    }

    /**
     * @return void
     */
    public function initLogger(): void
    {
        self::$logger = $this->stackdriver->getLogger();
        Bootstrap::init(self::$logger);
    }
}
