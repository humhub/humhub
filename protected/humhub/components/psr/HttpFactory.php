<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\psr;

use GuzzleHttp\Psr7\HttpFactory as GuzzleHttpFactory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * The PSR-17 factories of the platform.
 *
 * This namespace is the only place that names the PSR-7 implementation; everything else
 * types against `Psr\Http\Message\*` and gets its objects from here.
 *
 * @since 1.20
 */
final class HttpFactory
{
    private static ?GuzzleHttpFactory $instance = null;

    public static function instance(): RequestFactoryInterface&ResponseFactoryInterface&ServerRequestFactoryInterface&StreamFactoryInterface&UploadedFileFactoryInterface&UriFactoryInterface
    {
        return self::$instance ??= new GuzzleHttpFactory();
    }
}
