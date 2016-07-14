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
class HumHubHelper extends Module
{

    protected $config = [];
    
    public function inviteUserByEmail($email) {
        $this->getModule('Yii2')->_loadPage('POST', '/user/invite', ['Invite[emails]' => $email]);
    }
    
    public function initModules() {
        if(!empty($this->config['modules'])) {
            foreach($this->config['modules'] as $moduleId) {
                $module = Yii::$app->moduleManager->getModule($moduleId);
                if($module != null) {
                    $module->enable();
                } else {
                    //TODO: throw error ? skip ?...
                }
            }
        }
    }

}
