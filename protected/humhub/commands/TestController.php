<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

/**
 * TestController provides some console tests
 *
 * @inheritdoc
 */
class TestController extends \yii\console\Controller
{
    /**
     * Sends a test e-mail to the given e-mail address
     *
     * @param string $address the e-mail address
     */
    public function actionEmail($address)
    {
        $message = 'Console test message<br /><br />';

        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], ['message' => $message]);
        $mail->setTo($address);
        $mail->setSubject('Test message');
        $mail->send();

        Console::output('Message successfully sent!');
    }


    /**
     * Test database connection
     */
    public function actionDbConnection()
    {
        $this->stdout(PHP_EOL . 'DB Connection: ');
        if (empty(Yii::$app->db->dsn) || empty(Yii::$app->db->username)) {
            $this->stdout('Not Configured!', BaseConsole::FG_RED, BaseConsole::BOLD);
        } elseif (Yii::$app->isDatabaseInstalled(true)) {
            $this->stdout('OK!', BaseConsole::FG_GREEN, BaseConsole::BOLD);
        } else {
            $this->stdout('Failed!', BaseConsole::FG_RED, BaseConsole::BOLD);
        }
        $this->stdout(PHP_EOL . PHP_EOL);
    }
}
