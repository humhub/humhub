<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\helpers\ArrayHelper;
use humhub\libs\StringHelper;
use humhub\modules\admin\widgets\PrerequisitesList;
use humhub\modules\installer\forms\DatabaseForm;
use humhub\modules\installer\libs\DynamicConfig;
use humhub\modules\installer\Module;
use humhub\services\MigrationService;
use Yii;

/**
 * SetupController checks prerequisites and is responsible for database connection and schema setup.
 *
 * @property Module $module
 * @since 0.5
 */
class SetupController extends Controller
{
    public const PASSWORD_PLACEHOLDER = 'n0thingToSeeHere!';

    /**
     * @inheritdoc
     */
    public $access = ControllerAccess::class;

    public function actionIndex()
    {
        return $this->redirect(['prerequisites']);
    }

    /**
     * Prequisites action checks application requirement using the SelfTest
     * Libary
     *
     * (Step 2)
     */
    public function actionPrerequisites()
    {
        Yii::$app->cache->flush();

        if ($this->module->enableAutoSetup) {
            return $this->redirect(['database']);
        }

        return $this->render('prerequisites', ['hasError' => PrerequisitesList::hasError()]);
    }

    /**
     * Database action is responsible for all database related stuff.
     * Checking given database settings, writing them into a config file.
     *
     * (Step 3)
     */
    public function actionDatabase()
    {
        $errorMessage = "";

        $dynamicConfig = new DynamicConfig();
        $model = new DatabaseForm();

        if (($model->autoLoad() || $model->load(Yii::$app->request->post())) && $model->validate()) {
            try {
                if ($model->create) {
                    /** @var yii\db\Connection $temporaryConnection */
                    $temporaryConnection = Yii::createObject($model->getDbConfigAsArray(false));
                    // Create Database Connection without specifying a database
                    $temporaryConnection->open();

                    if (!$temporaryConnection->createCommand('SHOW DATABASES LIKE "' . $model->database . '"')
                        ->execute()) {
                        $temporaryConnection->createCommand(
                            'CREATE DATABASE `' . $model->database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                        )->execute();
                    }
                }

                $dbConfig = $model->getDbConfigAsArray(true);

                /** @var yii\db\Connection $temporaryConnection */
                $temporaryConnection = Yii::createObject($dbConfig);
                // Try access to the given database
                $temporaryConnection->open();

                $dynamicConfig->content['components']['db'] = $dbConfig;
                $dynamicConfig->save();

                return $this->redirect(['migrate']);
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        $model->password = '';
        return $this->render('database', ['model' => $model, 'errorMessage' => $errorMessage]);
    }


    public function actionMigrate()
    {
        if (!$this->module->checkDBConnection()) {
            return $this->redirect(['/installer/setup/database', 'dbFailed' => 1]);
        }

        $this->initDatabase();
        return $this->redirect(['cron']);
    }

    /**
     * Crontab
     */
    public function actionCron()
    {
        if ($this->module->enableAutoSetup) {
            return $this->redirect(['finalize']);
        }

        return $this->render('cron', []);
    }

    /**
     * Pretty URLs
     */
    public function actionPrettyUrls()
    {
        if ($this->module->enableAutoSetup) {
            return $this->redirect(['finalize']);
        }

        $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');

        $info = [];
        $errors = [];
        if (StringHelper::startsWith($serverSoftware, 'apache')) {
            $info[] = Yii::t('InstallerModule.base','<strong>Apache</strong> web server detected.');
            if (function_exists('apache_get_modules')) {
                $mods = apache_get_modules();
                if (!!in_array('mod_rewrite', $mods)) {
                    $info[] =  Yii::t('InstallerModule.base', 'The <strong>mod_rewrite</strong> module is active.');
                } else {
                    $errors[] = Yii::t('InstallerModule.base', 'The <strong>mod_rewrite</strong> module is not enabled.');
                }
            }
            if (file_exists(Yii::getAlias('@webroot/.htaccess'))) {
                $info[] = Yii::t('InstallerModule.base', 'The <strong>.htaccess</strong> file is in place.');
            } else {
                $errors[] = Yii::t('InstallerModule.base', 'The <strong>.htaccess</strong> file is not in place. In the installation folder, locate the <strong>.htaccess.dist</strong> file and rename it to <strong>.htaccess</strong>.');
            }
        } elseif (StringHelper::startsWith($serverSoftware, 'nginx')) {
            $info[] = Yii::t('InstallerModule.base', '<strong>Nginx</strong> web server detected.');
            $info[] = Yii::t('InstallerModule.base', 'Ensure the following rule is present in your configuration: <strong>try_files \$uri \$uri/ /index.php?\$args;</strong>.');
        } else {
            $errors[] = Yii::t('InstallerModule.base', 'Unable to automatically detect your web server type. Please ensure URL rewriting is configured.');
        }

        if ($problem = !empty($errors)) {
            $info[] = Yii::t('InstallerModule.base', 'Pretty URLs may not work correctly.');
        } else {
            $info[] = Yii::t('InstallerModule.base', 'Pretty URLs should work correctly.');
        }

        return $this->render('pretty-urls', [
            'info' => implode(' ', ArrayHelper::merge($info, $errors)),
            'problem' => $problem,
        ]);
    }

    public function actionFinalize()
    {
        if (!$this->module->checkDBConnection()) {
            return $this->redirect(['/installer/setup/database', 'dbFailed' => 1]);
        }

        Yii::$app->cache->flush();

        // Start the migration a second time here to retry any migrations aborted by timeouts.
        // In addition, in SaaS hosting, no setup step is required and only this action is executed directly.
        $this->initDatabase();

        return $this->redirect(['/installer/config']);
    }

    private function initDatabase()
    {
        // Flush Caches
        Yii::$app->cache->flush();

        // Migrate Up Database
        MigrationService::create()->migrateUp();
    }
}
