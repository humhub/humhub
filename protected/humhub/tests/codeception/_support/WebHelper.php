<?php

namespace tests\codeception\_support;

use Codeception\Module;
use Codeception\TestInterface;
use Yii;

/**
 * This helper is used to populate the database with needed fixtures before any tests are run.
 * In this example, the database is populated with the demo login user, which is used in acceptance
 * and functional tests.  All fixtures will be loaded before the suite is started and unloaded after it
 * completes.
 */
class WebHelper extends Module
{
    /**
     * Method called before any suite tests run. Loads User fixture login user
     * to use in acceptance and functional tests.
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        include __DIR__ . '/../acceptance/_bootstrap.php';
        $this->initModules();
    }

    public function _before(\Codeception\TestInterface $test)
    {
        ;
    }

    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules()
    {
        $cfg = \Codeception\Configuration::config();
        if (!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }

    /**
     * @inheritdoc
     */
    public function _failed(TestInterface $test, $fail)
    {
        parent::_failed($test, $fail);

        $filePath = codecept_output_dir() . str_replace(['\\', '/', ':', ' '], '.', $test->getSignature());

        $logFilePath = Yii::getAlias('@runtime/logs') . DIRECTORY_SEPARATOR . 'app.log';
        if (file_exists($logFilePath)) {
            copy($logFilePath, $filePath . '.app.log');
        }

        if (!Yii::$app->db->isActive) {
            return;
        }

        preg_match('/host=([^;]+)/', Yii::$app->db->dsn, $hostMatch);
        preg_match('/dbname=([^;]+)/', Yii::$app->db->dsn, $dbMatch);

        exec(sprintf(
            'mysqldump --skip-column-statistics -u%s -p%s -h%s %s > %s',
            escapeshellarg(Yii::$app->db->username ?? 'root'),
            escapeshellarg(Yii::$app->db->password ?? 'root'),
            escapeshellarg($hostMatch[1] ?? '127.0.0.1'),
            escapeshellarg($dbMatch[1] ?? 'humhub_test'),
            escapeshellarg($filePath . '.dump.sql'),
        ));
    }
}
