<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\modules\user\models\User;
use Yii;

/**
 * The permissions the user grants to visitors of the own profile
 *
 * @since 1.20
 */
class ProfilePermissionsSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('UserModule.account', 'Profile permissions');
    }

    public function getDescription(): string
    {
        return Yii::t('UserModule.account', 'These settings allow you to determine which permissions you want to grant visitors of your own individual profile. Each user can freely adjust the settings for his or her own profile.');
    }

    public function isVisible(): bool
    {
        return !empty(Yii::$app->getModule('user')->settings->get('enableProfilePermissions'));
    }

    /**
     * @return string the group selected by the `groupId` query parameter
     */
    public function getCurrentGroup(): string
    {
        $group = (string)Yii::$app->request->get('groupId');

        return isset(User::getUserGroups()[$group]) ? $group : User::USERGROUP_USER;
    }

    protected function renderContent(): string
    {
        return $this->render('profile-permissions', [
            'user' => Yii::$app->user->getIdentity(),
            'groups' => User::getUserGroups(),
            'group' => $this->getCurrentGroup(),
        ]);
    }
}
