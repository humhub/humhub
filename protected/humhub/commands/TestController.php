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
        $message = "Console test message<br /><br />";

        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], ['message' => $message]);
        $mail->setTo($address);
        $mail->setSubject('Test message');
        $mail->send();

        Console::output("Message successfully sent!");
    }

}
