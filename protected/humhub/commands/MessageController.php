<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\i18n\GettextPoFile;

/**
 * Extracts messages to be translated from source files.
 * 
 * @inheritdoc
 */
class MessageController extends \yii\console\controllers\MessageController
{

    /**
     * Extracts messages for a given module from source code.
     * 
     * @param string $moduleId
     */
    public function actionExtractModule($moduleId)
    {
        $module = Yii::$app->getModule($moduleId);

        $configFile = Yii::getAlias('@humhub/config/i18n.php');

        $config = array_merge([
            'translator' => 'Yii::t',
            'overwrite' => false,
            'removeUnused' => false,
            'sort' => false,
            'format' => 'php',
            'ignoreCategories' => [],
                ], require($configFile));
        $config['sourcePath'] = $module->getBasePath();

        if (!is_dir($config['sourcePath'] . '/messages')) {
            @mkdir($config['sourcePath'] . '/messages');
        }

        $files = FileHelper::findFiles(realpath($config['sourcePath']), $config);

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $config['translator'], $config['ignoreCategories']));
        }

        foreach ($config['languages'] as $language) {
            $dir = $config['messagePath'] . DIRECTORY_SEPARATOR . $language;
            if (!is_dir($dir)) {
                @mkdir($dir);
            }
            $this->saveMessagesToPHP($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort, $markUnused)
    {
        $dirNameBase = $dirName;

        foreach ($messages as $category => $msgs) {

            /**
             * Fix Directory
             */
            $module = $this->getModuleByCategory($category);
            if ($module !== null) {
                // Use Module Directory
                $dirName = str_replace(Yii::getAlias("@humhub/messages"), $module->getBasePath() . '/messages', $dirNameBase);
                preg_match('/.*?Module\.(.*)/', $category, $result);
                $category = $result[1];
            } else {
                // Use Standard HumHub Directory
                $dirName = $dirNameBase;
            }

            $file = str_replace("\\", '/', "$dirName/$category.php");
            $path = dirname($file);
            FileHelper::createDirectory($path);
            $msgs = array_values(array_unique($msgs));


            $coloredFileName = Console::ansiFormat($file, [Console::FG_CYAN]);
            $this->stdout("Saving messages to $coloredFileName...\n");
        
            $this->saveMessagesCategoryToPHP($msgs, $file, $overwrite, $removeUnused, $sort, $category, $markUnused);
        }
    }

    /**
     * Returns module instance by given message category.
     * 
     * @param string $category
     * @return \yii\base\Module
     */
    protected function getModuleByCategory($category)
    {
        if (preg_match('/(.*?)Module\./', $category, $result)) {
            $moduleId = strtolower($result[1]);
            $module = Yii::$app->getModule($moduleId, true);
            return $module;
        }

        return null;
    }

}
