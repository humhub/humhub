<?php

namespace humhub\modules\user\activities;

use humhub\helpers\Html;
use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidValueException;

final class FollowActivity extends BaseActivity implements ConfigurableActivityInterface
{
    private User $followedUser;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($record, $config);

        if (!$this->contentContainer->polymorphicRelation instanceof User) {
            throw new InvalidValueException('Invalid user record!');
        }

        $this->followedUser = $this->contentContainer->polymorphicRelation;
    }

    public static function getTitle(): string
    {
        return Yii::t('UserModule.base', 'Following (User)');
    }

    public static function getDescription(): string
    {
        return Yii::t('UserModule.base', 'Whenever a user follows another user.');
    }

    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return Yii::t('ActivityModule.base', '{displayNames} now follow {followedDisplayName}.', $params);
        } else {
            return Yii::t('ActivityModule.base', '{displayName} now follows {followedDisplayName}.', $params);
        }
    }

    protected function getMessageParamsText(): array
    {
        return array_merge(
            parent::getMessageParamsText(),
            [
                'followedDisplayName' => $this->followedUser->displayName,
            ],
        );
    }

    protected function getMessageParamsHtml(): array
    {
        return array_merge(
            parent::getMessageParamsHtml(),
            [
                'followedDisplayName' => Html::strong(Html::encode($this->followedUser->displayName)),
            ],
        );
    }

    public function getGroupingQuery(): ?ActiveQueryActivity
    {
        return Activity::find()
            ->andWhere(['activity.class' => static::class])
            ->andWhere(['activity.contentcontainer_id' => $this->record->contentcontainer_id]);
    }
}
