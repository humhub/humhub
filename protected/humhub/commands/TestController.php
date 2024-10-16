<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
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
        $this->stdout('====================================================' . PHP_EOL);
        $this->stdout('               DATABASE CONNECTION TEST' . PHP_EOL);
        $this->stdout('====================================================' . PHP_EOL . PHP_EOL);

        if (!empty(Yii::$app->db->dsn) && !empty(Yii::$app->db->username)) {
            $this->stdout('✅ DB Connection is configured' . PHP_EOL);
        } else {
            $this->stdout('❌ DB Connection is not configured' . PHP_EOL);
        }

        if (Yii::$app->isDatabaseInstalled(true)) {
            $this->stdout('✅ DB Connection is ok' . PHP_EOL);
        } else {
            $this->stdout('❌ DB Connection is failed' . PHP_EOL);
        }

        $this->stdout(PHP_EOL . '====================================================' . PHP_EOL);
    }
}
