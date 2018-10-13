<?php

/**
 * This file used for enhanced IDE code autocompletion only.
 * NOTE: To avoid warning of multiple autocompletion you should mark the file protected\vendor\yiisoft\yii2\Yii.php
 * as a plain text file for your IDE
 * @see https://github.com/samdark/yii2-cookbook/blob/master/book/ide-autocompletion.md#using-custom-yii-class
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 * @property-read \humhub\components\ModuleManager $moduleManager
 * @property-read \humhub\components\i18n\I18N $i18n
 * @property-read \humhub\components\mail\Mailer $mailer
 * @property-read \humhub\modules\ui\view\components\View $view
 * @property-read \humhub\components\SettingsManager $settings
 * @property-read \humhub\modules\notification\components\NotificationManager $notification
 * @property-read \humhub\modules\search\engine\Search $search
 * @property-read \humhub\components\i18n\Formatter
 * @property-read \humhub\components\AssetManager $assetManager
 * @property-read \humhub\modules\user\authclient\Collection $authClientCollection
 * @property-read \yii\queue\Queue $queue
 * @property-read \humhub\components\UrlManager $urlManager
 * @property-read \humhub\modules\live\components\Sender $live
 * @property-read \yii\mutex\Mutex $mutex
 *
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 * @property-read \humhub\modules\user\components\User $user
 */
class WebApplication extends \humhub\components\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 */
class ConsoleApplication extends \humhub\components\console\Application
{
}
