<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\components;

use yii\base\BaseObject;

/**
 * LiveEvent implements a message which can be send via live communication
 *
 * @since 1.2
 * @author Luke
 */
abstract class LiveEvent extends BaseObject
{
    /**
     * @see \humhub\modules\content\components\ContentContainerActiveRecord
     * @var int
     */
    public $contentContainerId;

    /**
     * @see \humhub\modules\content\models\Content::VISIBILITY_*
     * @var int
     */
    public $visibility;

    /**
     * Safely restores a live event from its serialized database representation.
     *
     * Unlike a plain `unserialize()`, this never instantiates arbitrary classes:
     * the intended top-level class name is read from the serialize() prefix
     * (`O:<len>:"<class>":...`) without deserializing anything, validated to be a
     * real {@see LiveEvent} subclass, and only then passed as the sole entry of
     * `allowed_classes`. Any other class — top-level or nested — is blocked and
     * left as `__PHP_Incomplete_Class`, which neutralizes PHP object injection
     * (gadget chain) attacks via crafted `live` table payloads.
     *
     * Third-party `LiveEvent` subclasses are accepted automatically; no class
     * registration is required.
     *
     * @param string|null $serialized the serialized live event
     * @return LiveEvent|null the live event, or null if the data is invalid or not a LiveEvent
     * @since 1.19
     */
    public static function fromSerialized(?string $serialized): ?LiveEvent
    {
        if ($serialized === null || $serialized === '') {
            return null;
        }

        // Read the intended top-level class name straight from the serialize()
        // prefix. The class name length is part of the format, so this is exact
        // and requires no deserialization.
        if (!preg_match('/^O:(\d+):"/', $serialized, $match)) {
            return null;
        }

        $class = substr($serialized, strlen($match[0]), (int)$match[1]);

        // Only genuine LiveEvent subclasses are allowed (autoloads via $allow_string).
        if (!is_subclass_of($class, self::class, true)) {
            return null;
        }

        try {
            $event = unserialize($serialized, ['allowed_classes' => [$class]]);
        } catch (\Throwable $ex) {
            return null;
        }

        return $event instanceof LiveEvent ? $event : null;
    }

    /**
     * Returns the data of this event as array
     *
     * @return array the live event data
     */
    public function getData()
    {
        $data = get_object_vars($this);
        unset($data['visibility']);
        unset($data['contentContainerId']);

        return [
            'type' => str_replace('\\', '.', static::class),
            'contentContainerId' => $this->contentContainerId,
            'visibility' => $this->visibility,
            'data' => $data,
        ];
    }

}
