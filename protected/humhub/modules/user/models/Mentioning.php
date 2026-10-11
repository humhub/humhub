<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\user\notifications\MentionedNotification;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "user_mentioning".
 * The followings are the available columns in table 'user_mentioning':
 *
 * @property int $id
 * @property string $object_model
 * @property int $object_id
 * @property int $user_id
 */
class Mentioning extends ActiveRecord
{
    /**
     * @var int how deep the current call is nested in {@see withoutNotification()}
     */
    private static int $silenced = 0;

    /**
     * Runs the callback without sending a {@see MentionedNotification} for the mentions it creates
     * - for a caller that notifies the mentioned users itself, e.g. together with other
     * notifications of the same event in a {@see \humhub\modules\notification\components\NotificationSet}.
     * The mentioned users are what {@see mention()} returns, and what
     * `RichText::postProcess()` reports as `mentioning`:
     *
     * ```php
     * $result = Mentioning::withoutNotification(fn() => RichText::postProcess($this->message, $this, 'message'));
     * $mentionedUsers = $result['mentioning'] ?? [];
     * ```
     *
     * Everything else a mention does is unchanged: it is stored and the user follows the content.
     *
     * @template T
     * @param callable(): T $callback
     * @return T the result of the callback
     * @api
     * @since 1.20
     */
    public static function withoutNotification(callable $callback): mixed
    {
        self::$silenced++;
        try {
            return $callback();
        } finally {
            self::$silenced--;
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_mentioning';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [ContentActiveRecord::class, ContentAddonActiveRecord::class],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_model', 'object_id', 'user_id'], 'required'],
            [['object_id', 'user_id'], 'integer'],
            [['object_model'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $mentionedSource = $this->getPolymorphicRelation();

        $originator = $this->getOriginatorBySource($mentionedSource);

        if (!$originator) {
            throw new Exception("Invalid polymorphic relation for Mentioning!");
        }

        if (self::$silenced === 0) {
            MentionedNotification::send($this->user, $mentionedSource, $originator);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $source
     * @return User|null
     */
    private function getOriginatorBySource($source)
    {
        if ($source instanceof ContentActiveRecord) {
            /** @var ContentActiveRecord $source */
            return $source->content->createdBy;
        } elseif ($source instanceof ContentAddonActiveRecord) {
            /** @var ContentAddonActiveRecord $source */
            return $source->createdBy;
        }

        return null;
    }

    /**
     * Creates the mentioning for the given `$guids`.
     * This function will skip `$guids` which are already mentioned in the given `$record`.
     *
     * @param string|string[] $guids
     * @param ContentActiveRecord|ContentAddonActiveRecord $record
     * @return array new mentionings for the given $record
     * @throws InvalidArgumentException if an invalid $record is provided
     * @since 1.3
     */
    public static function mention($guids, $record)
    {
        if (!($record instanceof ContentActiveRecord || $record instanceof ContentAddonActiveRecord)) {
            throw new InvalidArgumentException("Mentioning can only used in ContentActiveRecord or ContentAddonActiveRecord objects!");
        }

        // Mention only for published content
        if (!$record->content->getStateService()->isPublished()) {
            return [];
        }

        if (is_string($guids)) {
            $guids = [$guids];
        }

        $result = [];

        foreach ($guids as $guid) {
            $user = User::findOne(['guid' => $guid]);
            if (!$user) {
                continue;
            }

            // Check the user was already mentioned (e.g. edit)
            $mention = static::findOne([
                'object_model' => $record::class,
                'object_id' => $record->getPrimaryKey(),
                'user_id' => $user->id,
            ]);

            if (!$mention) {
                $mention = new static(['user_id' => $user->id]);
                $mention->setPolymorphicRelation($record);
                $mention->save();

                $result[] = $user;

                // Mentioned users automatically follows the content
                $record->content->getPolymorphicRelation()->follow($user->id);
            }
        }

        return $result;
    }

    /**
     * Related user
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
