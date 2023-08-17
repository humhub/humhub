<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use Yii;
use yii\helpers\FileHelper;

trait HumHubHelperTrait
{
    protected function flushCache(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Flushing cache', $caller ?? __METHOD__));
        $cachePath = Yii::getAlias('@runtime/cache');
        if ($cachePath && is_dir($cachePath)) {
            FileHelper::removeDirectory($cachePath);
        }
        Yii::$app->cache->flush();
        RichTextToShortTextConverter::flushCache();
        RichTextToHtmlConverter::flushCache();
        RichTextToPlainTextConverter::flushCache();
        RichTextToMarkdownConverter::flushCache();
        UrlOembed::flush();
    }

    protected function reloadSettings(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Reloading settings', $caller ?? __METHOD__));
        Yii::$app->settings->reload();

        foreach (Yii::$app->modules as $module) {
            if ($module instanceof \humhub\components\Module) {
                $module->settings->reload();
            }
        }
    }

    protected function deleteMails(?string $caller = null)
    {
        codecept_debug(sprintf('[%s] Deleting mails', $caller ?? __METHOD__));
        $path = Yii::getAlias('@runtime/mail');
        $files = glob($path . '/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }
}
