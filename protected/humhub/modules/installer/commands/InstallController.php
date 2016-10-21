<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;

use humhub\modules\user\models\Group;

/**
 * Console Install
 *
 * @author Luke
 */
class InstallController extends Controller
{

    public function actionAuto()
    {
        $this->stdout("Install:\n\n", Console::FG_YELLOW);

        \humhub\modules\installer\libs\InitialData::bootstrap();

        Yii::$app->settings->set('name', "HumHub Test");
        Yii::$app->settings->set('mailer.systemEmailName', "humhub@example.com");
        Yii::$app->settings->set('mailer.systemEmailName', "humhub@example.com");
        Yii::$app->settings->set('secret', \humhub\libs\UUID::v4());

        $user = new User();
        //$user->group_id = 1;
        $user->username = "Admin";
        $user->email = 'humhub@example.com';
        $user->status = User::STATUS_ENABLED;
        $user->language = '';
        $user->last_activity_email = new \yii\db\Expression('NOW()');
        if (!$user->save()) {
            throw new \yii\base\Exception("Could not save user");
        }

        $user->profile->title = "System Administration";
        $user->profile->firstname = "John";
        $user->profile->lastname = "Doe";
        $user->profile->save();

        $password = new Password();
        $password->user_id = $user->id;
        $password->setPassword('test');
        $password->save();

        // Assign to system admin group
        Group::getAdminGroup()->addUser($user);


        return self::EXIT_CODE_NORMAL;
    }

}
