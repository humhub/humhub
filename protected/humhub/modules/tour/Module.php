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
     * Configuration files for each page of the introduction tour
     * Must return an array of valid params, see `\humhub\modules\tour\models\TourConfig::isValidConfig()`
     *
     * Example of a custom config file:
     * [
     *     '@tour/config/tour-interface.php', // default config
     *     __DIR__ . '/custom-tour/tour-administration.php', // custom config in protected/config/custom-tour/tour-administration.php
     * ],
     *
     * @since 1.18
     */
    public array $tourConfigFiles = [
        '@tour/config/tour-interface.php',
        '@tour/config/tour-spaces.php',
        '@tour/config/tour-profile.php',
        '@tour/config/tour-administration.php',
    ];

    /**
     * @var array Driver.js extra options
     * Will be merged with the view options
     * See documentation: https://driverjs.com/docs
     *
     * @since 1.18
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
