<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use humhub\components\rateLimit\RateLimitResult;
use Sabre\DAV\Exception;
use Sabre\DAV\Server;
use Yii;

/**
 * `429 Too Many Requests` with `Retry-After` and the `RateLimit` headers.
 *
 * @since 1.20
 */
class TooManyRequests extends Exception
{
    public function __construct(private readonly RateLimitResult $result)
    {
        parent::__construct(Yii::t('base', 'Too many requests, try again in {seconds} seconds.', ['seconds' => $result->retryAfter]));
    }

    /**
     * @inheritdoc
     */
    public function getHTTPCode()
    {
        return 429;
    }

    /**
     * @inheritdoc
     */
    public function getHTTPHeaders(Server $server)
    {
        return ['Retry-After' => (string)(int)$this->result->retryAfter] + $this->result->toHeaders();
    }
}
