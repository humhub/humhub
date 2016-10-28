<?php

namespace tests\codeception\_support;

use Codeception\Module;
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
        include __DIR__.'/../acceptance/_bootstrap.php';
        $this->initModules();
    }
    
    /**
     * Initializes modules defined in @tests/codeception/config/test.config.php
     * Note the config key in test.config.php is modules and not humhubModules!
     */
    protected function initModules() {
        $cfg = \Codeception\Configuration::config();
        if(!empty($cfg['humhub_modules'])) {
            Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
        }
    }
}
