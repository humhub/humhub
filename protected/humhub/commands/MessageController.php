<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\helpers\Console;
use yii\helpers\FileHelper;

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
        $module = Yii::$app->moduleManager->getModule($moduleId);

        $configFile = Yii::getAlias('@humhub/config/i18n.php');

        $config = array_merge([
            'translator' => 'Yii::t',
            'overwrite' => false,
            'removeUnused' => false,
            'sort' => true,
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

        // Remove unrelated translation categories
        foreach ($messages as $category => $msgs) {
            $categoryModule = $this->getModuleByCategory($category);
            if ($categoryModule == null || $categoryModule->id != $module->id) {
                unset($messages[$category]);
            }
        }

        foreach ($config['languages'] as $language) {
            $dir = $config['sourcePath'] . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $language;
            if (!is_dir($dir)) {
                @mkdir($dir);
            }

            $this->saveMessagesToPHP($messages, $dir, $config['overwrite'], $config['removeUnused'], $config['sort'], false);
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
            if (strpos($result[1], '-') !== false || strpos($result[1], '_') !== false) {
                // module id already in correct format (-,_)
                return Yii::$app->moduleManager->getModule($result[1], true);
            } else {
                $moduleId = strtolower(preg_replace("/([A-Z])/", '_\1', lcfirst($result[1])));
                try {
                    return Yii::$app->moduleManager->getModule($moduleId, true);
                } catch (\yii\base\Exception $ex) {
                    // Module not found, try again with dash syntax
                    $moduleId = strtolower(preg_replace("/([A-Z])/", '-\1', lcfirst($result[1])));
                    return Yii::$app->moduleManager->getModule($moduleId, true);
                }
            }
        }

        return null;
    }

    /**
     * Collects all translated strings and stores it in a archive.json file.
     */
    public function actionBuildArchive()
    {
        // Get Message Folders
        $messageFolders = [Yii::getAlias('@humhub/messages')];
        foreach (Yii::$app->moduleManager->getModules(['includeCoreModules' => true, 'returnClass' => false]) as $id => $module) {
            $messageFolders[] = $module->getBasePath() . '/messages';
        }

        foreach (Yii::$app->params['availableLanguages'] as $language => $name) {
            print "Processing " . $language . " ...";

            if (!is_dir(Yii::getAlias("@humhub/messages/" . $language))) {
                print "Skipped (No message folder)\n";
                continue;
            }

            // Load Archive
            $archive = [];
            $archiveFile = Yii::getAlias('@humhub/messages/' . $language . '/archive.json');
            if (file_exists($archiveFile)) {
                $archive = \yii\helpers\Json::decode(file_get_contents($archiveFile));
            }

            // Loop overall messages
            foreach ($messageFolders as $messageFolder) {
                if (is_dir($messageFolder . '/' . $language)) {
                    foreach (glob($messageFolder . '/' . $language . '/*.php') as $messageFile) {
                        $messages = require($messageFile);
                        foreach ($messages as $original => $translated) {

                            // Removed unused marking
                            if (substr($translated, 0, 2) == '@@' && substr($translated, -2, 2) == '@@') {
                                $translated = preg_replace('/^@@/', '', $translated);
                                $translated = preg_replace('/@@$/', '', $translated);
                            }

                            if ($translated != "") {
                                if (isset($archive[$original]) && !in_array($translated, $archive[$original])) {
                                    $archive[$original][] = $translated;
                                } else {
                                    $archive[$original] = [$translated];
                                }
                            }
                        }
                    }
                }
            }

            // Save
            file_put_contents($archiveFile, \yii\helpers\Json::encode($archive));

            print "Saved!\n";
        }
    }

}
