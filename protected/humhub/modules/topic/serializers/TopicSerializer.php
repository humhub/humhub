<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\serializers;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;

/**
 * Serializes a {@see Topic} for the HTTP API (see `docs/develop/concept-api.md`).
 *
 * The short representation is what a topic picker renders and what other shapes embed when they
 * name a topic: its id, name and color, and the container it belongs to — `null` for a global
 * topic, so a picker offering the topics of several spaces can tell equal names apart. Like every
 * shape of the API it is caller-neutral.
 *
 * @since 1.20
 */
class TopicSerializer
{
    /**
     * @param ContentContainerActiveRecord|null $container the topic's space or profile, `null`
     *        for a global topic — passed in by {@see self::batch()}, else loaded
     * @return array{
     *     id: int,
     *     name: string,
     *     color: string|null,
     *     container: array{id: int, guid: string, name: string}|null,
     * }
     */
    public static function short(Topic $topic, ?ContentContainerActiveRecord $container = null): array
    {
        if ($container === null && $topic->contentcontainer_id !== null) {
            $container = $topic->getContainer();
        }

        return [
            'id' => (int)$topic->id,
            'name' => $topic->name,
            'color' => $topic->color ?: null,
            'container' => $container === null ? null : [
                'id' => (int)$container->contentcontainer_id,
                'guid' => $container->guid,
                'name' => $container->getDisplayName(),
            ],
        ];
    }

    /**
     * {@see self::short()} for a whole page of topics, with their containers loaded in one query
     * per container kind (spaces, and profiles with their profile for the display name) instead
     * of per topic.
     *
     * @param Topic[] $topics
     */
    public static function batch(array $topics): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            static fn(Topic $topic) => $topic->contentcontainer_id !== null ? (int)$topic->contentcontainer_id : null,
            $topics,
        ))));

        $containers = [];
        if ($ids !== []) {
            $containers = Space::find()->where(['space.contentcontainer_id' => $ids])->indexBy('contentcontainer_id')->all()
                + User::find()->with('profile')->where(['user.contentcontainer_id' => $ids])->indexBy('contentcontainer_id')->all();
        }

        return array_map(
            static fn(Topic $topic) => self::short($topic, $containers[$topic->contentcontainer_id] ?? null),
            $topics,
        );
    }
}
