<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

/**
 * Base class for user gates providing sensible defaults: no extra allowed routes,
 * applies to full-page and AJAX requests but not to API requests, cacheable.
 *
 * @see UserGateInterface
 * @since 1.19
 */
abstract class UserGate implements UserGateInterface
{
    /**
     * Suggested sort order ranges for common gate types.
     */
    public const SORT_MAINTENANCE = 50;
    public const SORT_PASSWORD = 100;
    public const SORT_SECOND_FACTOR = 200;
    public const SORT_LEGAL = 300;
    public const SORT_ONBOARDING = 400;

    /**
     * @inheritdoc
     */
    public function getAllowedRoutes(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function appliesTo(RequestClass $requestClass): bool
    {
        return $requestClass !== RequestClass::Api;
    }

    /**
     * @inheritdoc
     */
    public function isCacheable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function onIntercept(): void
    {
    }
}
