<?php

namespace humhub\modules\tour;

use humhub\modules\user\models\User;
use Yii;

/**
 * This module shows an introduction tour for new users
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class Module extends \humhub\components\Module
{
    /**
     * @var string[]
     */
    public $acceptableNames = ['interface', 'administration', 'profile', 'spaces'];

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * Check if the welcome tour window should be displayed automatically
     *
     * @param User|null $user
     * @return bool
     */
    public function showWelcomeWindow(?User $user = null): bool
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
        }

        return $user instanceof User
            && $user->id === 1
            && Yii::$app->getModule('installer')->settings->get('sampleData') != 1
            && $this->settings->user($user)->get('welcome') != 1;
    }
}
