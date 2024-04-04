<?php

/**
 * This class only exists here for IDE (PHPStorm/Netbeans/...) autocompletion.
 * This file is never included anywhere.
 * Adjust this file to match classes configured in your application config, to enable IDE autocompletion for custom components.
 * Example: A property phpdoc can be added in `__Application` class as `@property \vendor\package\Rollbar|__Rollbar $rollbar` and adding a class in this file
 * ```php
 * // @property of \vendor\package\Rollbar goes here
 * class __Rollbar {
 * }
 * ```
 */
class Yii
{
    /**
     * @var \yii\web\Application|\yii\console\Application|\humhub\components\Application|\humhub\components\console\Application|\humhub\interfaces\ApplicationInterface|__Application|__WebApplication
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 * @property \humhub\components\ModuleManager $moduleManager
 * @property \humhub\components\Controller $controller
 * @property \humhub\components\i18n\I18N $i18n
 * @property \humhub\components\mail\Mailer $mailer
 * @property \humhub\modules\ui\view\components\View $view
 * @property \humhub\components\SettingsManager $settings
 * @property \humhub\modules\notification\components\NotificationManager $notification
 * @property \humhub\components\i18n\Formatter
 * @property \humhub\components\AssetManager $assetManager
 * @property \humhub\modules\user\authclient\Collection $authClientCollection
 * @property \yii\queue\Queue $queue
 * @property \humhub\components\Request $request
 * @property \humhub\components\UrlManager $urlManager
 * @property \humhub\modules\live\components\Sender $live
 * @property \yii\mutex\Mutex $mutex
 * @property \yii\web\User|__WebUser $user
 * @property \yii\caching\ArrayCache $runtimeCache
 */
class __Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 * @property \humhub\modules\user\components\User $user
 * @property \humhub\components\mail\Mailer $mailer
 */
class __WebApplication extends \humhub\components\Application
{
}

if (!class_exists(WeakReference::class)) {
    class WeakReference
    {
        /* Methods */
        public static function create(object $object): self
        {
            return new static();
        }

        public function get(): ?object
        {
            return null;
        }
    }
}
