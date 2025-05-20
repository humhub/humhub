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
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * Custom parameters for the introduction tour
     * Replace the default ones
     * Must be an array of valid params: see `\humhub\modules\tour\models\TourParams::isValidParams()`
     */
    public ?array $customTourParams = null;

    /**
     * @var array Driver.js extra options
     * Will be merged with the view options
     * See documentation: https://driverjs.com/docs
     */
    public array $driverOptions = [
        'showProgress' => 'true',
    ];

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

        return
            $user instanceof User
            && $user->id === 1
            && !Yii::$app->getModule('installer')->settings->get('sampleData')
            && $this->settings->user($user)->get('welcome');
    }
}
