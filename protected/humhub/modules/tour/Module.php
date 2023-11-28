<?php

namespace humhub\modules\tour;

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
     * @return bool
     */
    public function showWelcomeWindow(): bool
    {
        return Yii::$app->user->id === 1 &&
            Yii::$app->getModule('installer')->settings->get('sampleData') != 1 &&
            $this->settings->user()->get('welcome') != 1;
    }
}
