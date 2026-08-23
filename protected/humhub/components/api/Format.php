<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\api;

use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\helpers\Inflector;

/**
 * Value formatting shared by every API serializer, so the conventions of the current API
 * version live in one place (see `docs/develop/concept-api.md`).
 *
 * @since 1.19
 */
class Format
{
    /**
     * A stored datetime as ISO-8601 with offset, e.g. `2026-08-22T08:00:00+00:00`.
     *
     * Datetimes are stored without timezone information, in the application's timezone (see
     * `ApplicationTrait::setTimeZone()`, which pins the DB session timezone to the same
     * value). The API emits UTC rather than the caller's profile timezone: the value is
     * unambiguous either way, but a response that does not depend on who asked is easier to
     * reason about and to cache.
     *
     * @param string|null $value a `Y-m-d H:i:s` datetime as stored by the platform
     */
    public static function dateTime(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $date = new DateTimeImmutable($value, new DateTimeZone(Yii::$app->timeZone));

        return $date->setTimezone(new DateTimeZone('UTC'))->format('c');
    }

    /**
     * A model attribute name as the API spells it, e.g. `parent_comment_id` →
     * `parentCommentId`.
     *
     * Used wherever attribute names reach the wire without passing a serializer - validation
     * errors ({@see BaseController::validationErrors()}) above all, whose keys a client
     * matches against the field names it sent.
     */
    public static function attribute(string $name): string
    {
        return Inflector::variablize($name);
    }
}
