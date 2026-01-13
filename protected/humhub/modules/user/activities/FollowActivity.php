<?php

namespace humhub\modules\user\activities;

use humhub\helpers\Html;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\activity\models\Activity;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidValueException;

class FollowActivity extends BaseActivity implements ConfigurableActivityInterface
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

    public static function getTitle() : string
    {
        return Yii::t('UserModule.base', 'Following (User)');
    }

    public static function getDescription() : string
    {
        return Yii::t('UserModule.base', 'Whenever a user follows another user.');
    }


    public function getAsText(array $params = []): string
    {
        $defaultParams = [
            'user1' => $this->user->displayName,
            'user2' => $this->space->name,
        ];

        return Yii::t(
            'ActivityModule.base',
            '{user1} now follows {user2}.',
            array_merge($defaultParams, $params)
        );
    }

    public function getAsHtml()
    {
        return $this->getAsText([
            'user1' => Html::strong(Html::encode($this->user->displayName)),
            'user2' => Html::strong(Html::encode($this->followedUser->displayName)),
        ]);
    }
}
