<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use Codeception\Module;
use humhub\models\UrlOembed;
use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use Yii;
use yii\helpers\FileHelper;
use yii\symfonymailer\Message;

/**
 * This helper is used to populate the database with needed fixtures before any tests are run.
 * In this example, the database is populated with the demo login user, which is used in acceptance
 * and functional tests.  All fixtures will be loaded before the suite is started and unloaded after it
 * completes.
 */
class HumHubHelper extends Module
{
    use HumHubHelperTrait;

    protected $config = [];

    /* @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore */
    public function _before(\Codeception\TestInterface $test)
    {
        static::reloadSettings(__METHOD__);
        static::flushCache(__METHOD__);
    }

    public function fetchInviteToken($mail)
    {
        if ($mail instanceof Message) {
            $mail = $mail->getHtmlBody();
        }

        $re = [];
        preg_match('/token=([A-Za-z0-9_-]{12})\"/', $mail, $re);

        if (!isset($re[1])) {
            $this->assertTrue(false, 'Invite token not found');
        }

        return trim($re[1]);
    }

    public function inviteUserByEmail($email)
    {
        $this->getModule('Yii2')->_loadPage('POST', '/user/invite', ['Invite[emails]' => $email]);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function assertMailSent($count = 0, $msg = null)
    {
        $this->getYiiModule()->seeEmailIsSent($count);
    }

    public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getYiiModule()->grabLastSentEmail();
        $this->assertEquals($subject, $message->getSubject());
    }

    public function grapLastEmailText()
    {
        /** @var Message $message */
        $message = $this->getYiiModule()->grabLastSentEmail();
        return $message->getTextBody();
    }

    /*public function assertEqualsLastEmailSubject($subject)
    {
        $message = $this->getYiiModule()->grabLastSentEmail();
        $this->assertEquals($subject, $message->getSubject());
    }*/

    public function initModules()
    {
        $modules = array_map(function (Module $module) {
            return $module->id;
        }, Yii::$app->moduleManager->getModules());

        Yii::$app->moduleManager->disableModules($modules);

        if (!empty($this->config['modules'])) {
            foreach ($this->config['modules'] as $moduleId) {
                $module = Yii::$app->moduleManager->getModule($moduleId);
                if ($module != null) {
                    $module->enable();
                } else {
                    //TODO: throw error ? skip ?...
                }
            }
        }
    }
}
