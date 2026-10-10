<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit\notifications;

use Closure;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;

/**
 * A notification whose blocks a test sets ({@see $testBlocks}); records the contexts its blocks
 * are asked for.
 */
final class TestBlocksNotification extends BaseNotification
{
    /**
     * @var Closure(self, NotificationContext): NotificationBlock[]|null
     */
    public static ?Closure $testBlocks = null;

    /**
     * @var NotificationContext[]
     */
    public static array $contexts = [];

    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    public function getBlocks(NotificationContext $context): array
    {
        self::$contexts[] = $context;

        return self::$testBlocks !== null ? (self::$testBlocks)($this, $context) : [];
    }

    public static function reset(): void
    {
        self::$testBlocks = null;
        self::$contexts = [];
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} wrote you";
    }
}
