<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\interfaces\ViewableInterface;
use humhub\models\Setting;
use humhub\modules\user\helpers\UserHelper;
use humhub\modules\user\models\User;

class SettingFile extends Setting implements ViewableInterface
{
    public function canView($user = null): bool
    {
        return true;
    }

    public function canEdit(User|int|null $user = null): bool
    {
        $user = UserHelper::getUserByParam($user);

        return $user && $user->isSystemAdmin();
    }

    public function attach(File $file): bool
    {
        return $file->updateAttributes([
            'object_model' => static::class,
            'object_id' => $this->id,
        ]);
    }
}