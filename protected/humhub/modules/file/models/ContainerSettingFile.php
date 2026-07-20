<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\interfaces\ViewableInterface;
use humhub\modules\content\models\ContentContainerSetting;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\UserHelper;
use humhub\modules\user\models\User;

class ContainerSettingFile extends ContentContainerSetting implements ViewableInterface
{
    public function canView($user = null): bool
    {
        $user = UserHelper::getUserByParam($user);

        if ($container = $this->contentcontainer) {
            if ($container->class === Space::class) {
                return Space::find()
                    ->where(['id' => $container->pk])
                    ->visible($user)
                    ->exists();
            }

            if ($container->class === User::class) {
                return User::find()
                    ->where(['id' => $container->pk])
                    ->visible($user)
                    ->exists();
            }
        }

        return false;
    }

    public function canEdit(User|int|null $user = null): bool
    {
        $user = UserHelper::getUserByParam($user);

        $container = $this->contentcontainer?->getPolymorphicRelation();

        if ($container instanceof Space) {
            return $container->isAdmin($user);
        }

        if ($container instanceof User) {
            return $container->is($user);
        }

        return false;
    }

    public function attach(File $file): bool
    {
        return $file->updateAttributes([
            'object_model' => static::class,
            'object_id' => $this->id,
        ]);
    }
}