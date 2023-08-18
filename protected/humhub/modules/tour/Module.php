<?php

namespace humhub\modules\tour;

use Yii;
use humhub\modules\tour\widgets\Dashboard;
use humhub\modules\user\models\User;

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
     * @var bool enable auto-start tour for new accounts
     * @since 1.15
     */
    public $autoStartOnNewAccounts = false;

    /**
     * Check is first login
     * @since 1.15
     *
     * @param User $user
     * @return bool
     */
    private function getIsFirstLogin(User $user): bool
    {
        $settings = Yii::$app->getModule('tour')->settings;
        $showWelcome = (
            $user->id == 1 &&
            Yii::$app->getModule('installer')->settings->get('sampleData') != 1 &&
            $settings->user()->get('welcome') != 1
        );

        return (
            !$showWelcome &&
            $user->updated_by === null &&
            $user->created_at === $user->updated_at
        );
    }

    /**
     * Set updated_by for current user
     * @since 1.15
     *
     * @param User $user
     * @return void
     */
    private function setFirstLoginDone(User $user)
    {
        $user->updateAttributes(['updated_by' => $user->id]);
    }

    /**
     * Event Callback
     */
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('tour');
        $settings = $module->settings;

        if ($settings->get('enable') == 1 && $settings->user()->get("hideTourPanel") != 1) {
             $module->autoStartOnNewAccounts = $module->getIsFirstLogin(Yii::$app->user->identity);
            if ($module->autoStartOnNewAccounts) {
                $module->setFirstLoginDone(Yii::$app->user->identity);

                Yii::$app->getResponse()->redirect(['/dashboard/dashboard', 'tour' => true]);
            } else {
                $event->sender->addWidget(Dashboard::class, [], ['sortOrder' => 100]);
            }
        }
    }
}
