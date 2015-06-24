<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * The TestMail command is used to test the e-mail subsystem.
 *
 * @package humhub.commands.shell.EMailing
 * @since 0.5
 */
class TestMail extends HConsoleCommand {

    public function run($args) {
        print " E-Mail - Test Interface\n";
        print "-------------------------------------------------------------------------\n\n";

        if (!isset($args[0]) || ($args[0] == "")) {
            print "\n Run with parameter [email]!\n";
            print "\n\n";
            exit;
        }
        $email = $args[0];

        $user = User::model()->findByPk(1);

        $message = new HMailMessage();
        $message->view = 'application.views.mail.EMailing';
        $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
        $message->addTo($email);
        $message->subject = "Test Mail";

        $message->setBody(array('user' => $user), 'text/html');
        Yii::app()->mail->send($message);

        print "Sent! \n";

        print "\nEMailing completed.\n";
    }

}
