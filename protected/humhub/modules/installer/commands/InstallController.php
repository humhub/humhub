<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use humhub\models\Setting;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\space\models\Space;

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

        Setting::Set('name', "HumHub Test");
        Setting::Set('systemEmailName', "humhub@example.com", 'mailing');
        Setting::Set('systemEmailName', "humhub@example.com", 'mailing');
        Setting::Set('secret', \humhub\libs\UUID::v4());

        $user = new User();
        $user->group_id = 1;
        $user->username = "Admin";
        $user->auth_mode = 'local';
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



        return self::EXIT_CODE_NORMAL;
    }

}
