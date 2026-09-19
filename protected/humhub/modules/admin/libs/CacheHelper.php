<?php

namespace humhub\modules\admin\libs;

use humhub\modules\admin\events\FetchReloadableScriptsEvent;
use humhub\modules\admin\Module;
use humhub\services\ActiveThemeService;
use Yii;
use yii\base\Component;

class CacheHelper extends Component
{
    public const EVENT_FETCH_RELOADABLE_SCRIPTS = 'fetchReloadableScripts';

    public static function getReloadableScriptUrls()
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('admin');
        $instance = new static();
        $urls = $module->defaultReloadableScripts;
        $event = new FetchReloadableScriptsEvent(['urls' => $urls]);
        $instance->trigger(static::EVENT_FETCH_RELOADABLE_SCRIPTS, $event);
        return $event->urls;
    }

    public static function flushCache(): string
    {
        $output = "Flushing cache ...";
        Yii::$app->cache->flush();

        $output .= "\nFlushing asset manager ...";
        Yii::$app->assetManager->clear();

        $output .= "\nFlushing theme cache ...";
        // Not activate(): that would persist the current in-memory theme as the admin's
        // choice, which is wrong whenever it is only the fallback ActiveThemeService
        // resolved because the selected theme could not be found.
        ActiveThemeService::flush();
        Yii::$app->view->theme->publishResources(true);
        Yii::$app->systemRevision->touch();

        return $output;
    }
}
