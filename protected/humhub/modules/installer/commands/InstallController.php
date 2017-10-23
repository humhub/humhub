<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\base\Exception;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\Group;
use humhub\modules\installer\libs\InitialData;
use humhub\libs\UUID;

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

        InitialData::bootstrap();

        Yii::$app->settings->set('name', 'HumHub Test');
        Yii::$app->settings->set('mailer.systemEmailName', 'humhub@example.com');
        Yii::$app->settings->set('mailer.systemEmailName', 'humhub@example.com');
        Yii::$app->settings->set('secret', UUID::v4());

        $user = new User();
        //$user->group_id = 1;
        $user->username = 'Admin';
        $user->email = 'humhub@example.com';
        $user->status = User::STATUS_ENABLED;
        $user->language = '';
        if (!$user->save()) {
            throw new Exception("Could not save user");
        }

        $user->profile->title = 'System Administration';
        $user->profile->firstname = 'John';
        $user->profile->lastname = 'Doe';
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
