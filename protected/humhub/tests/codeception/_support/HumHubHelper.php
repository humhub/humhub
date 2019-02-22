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

    public function _before(\Codeception\TestInterface $test)
    {
        Yii::$app->getUrlManager()->setScriptUrl('/index-test.php');
    }

    public function fetchInviteToken($mail) {
        if($mail instanceof \yii\mail\MessageInterface) {
            $mail = $mail->toString();
        }

        $mail = preg_replace('/([\r\n=])*/', '', $mail);

        $re = [];
        preg_match('/registration&token3D([A-Za-z0-9_-]{12})/', $mail, $re);

        if(!isset($re[1])) {
            $this->assertTrue(false, 'Invite token not found');
        }

        return trim($re[1]);
    }

    public function inviteUserByEmail($email) {
        $this->getModule('Yii2')->_loadPage('POST', '/user/invite', ['Invite[emails]' => $email]);
    }

    public function assertMailSent($count = 0, $msg = null)
    {
        return $this->getModule('Yii2')->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getModule('Yii2')->grabLastSentEmail();
        $this->assertEquals($subject, $message->getSubject());
    }

    public function grapLastEmailText()
    {
        $message = $this->getModule('Yii2')->grabLastSentEmail();
        foreach($message->getSwiftMessage()->getChildren() as $part) {
            if($part->getContentType() === 'text/plain') {
                return $part->getBody();
            }
        }
    }

    /*public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getModule('Yii2')->grabLastSentEmail();
        $this->assertEquals($subject, $message->getSubject());
    }*/

    public function initModules() {
        $modules = array_map(function(Module $module) {
            return $module->id;
        },  Yii::$app->moduleManager->getModules());

        Yii::$app->moduleManager->disableModules($modules);

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
