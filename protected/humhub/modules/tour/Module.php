<?php

namespace humhub\modules\tour;

use Yii;
use humhub\modules\tour\widgets\Dashboard;

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
     * @inheridoc
     */
    public $isCoreModule = true;

    /**
     * Event Callback
     */
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $settings = Yii::$app->getModule('tour')->settings;

        if ($settings->get('enable') == 1 && $settings->user()->get("hideTourPanel") != 1) {
            $showWelcome = (
                Yii::$app->user->id == 1 &&
                Yii::$app->getModule('installer')->settings->get('sampleData') != 1 &&
                $settings->user()->get('welcome') != 1
            );
            $autoStartOnNewAccounts = (
                !$showWelcome &&
                Yii::$app->user->identity->updated_by === null &&
                Yii::$app->user->identity->created_at === Yii::$app->user->identity->updated_at
            );

            if ($autoStartOnNewAccounts) {
                Yii::$app->user->selfSetUpdatedBy(Yii::$app->user->identity);
                Yii::$app->getResponse()->redirect(['/dashboard/dashboard', 'tour' => true]);
            } else {
                $event->sender->addWidget(Dashboard::class, [], ['sortOrder' => 100]);
            }
        }
    }
}
