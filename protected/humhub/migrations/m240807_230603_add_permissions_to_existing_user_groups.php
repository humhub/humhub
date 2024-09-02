<?php

use humhub\libs\BasePermission;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupPermission;
use yii\db\Migration;
use yii\db\Query;

/**
 * Class m240807_230603_add_permissions_to_existing_user_groups
 */
class m240807_230603_add_permissions_to_existing_user_groups extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (Yii::$app->isInstalled()) {
            foreach ((new Query())->from(Group::tableName())->batch() as $groups) {
                foreach ($groups as $group) {
                    foreach ([Yii::createObject(CreatePublicSpace::class), Yii::createObject(CreatePrivateSpace::class)] as $permission) {
                        /** @var BasePermission $permission */

                        $exist = (new Query())->from(GroupPermission::tableName())
                            ->where([
                                'group_id' => $group['id'],
                                'module_id' => $permission->getModuleId(),
                                'class' => $permission::class,
                            ])
                            ->exists();

                        if (!$exist) {
                            Yii::$app->db->createCommand()->insert(
                                GroupPermission::tableName(),
                                [
                                    'permission_id' => $permission->getId(),
                                    'group_id' => $group['id'],
                                    'module_id' => $permission->getModuleId(),
                                    'class' => $permission::class,
                                    'state' => $permission::STATE_ALLOW,
                                ],
                            )->execute();
                        }
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240807_230603_add_permissions_to_existing_user_groups cannot be reverted.\n";

        return false;
    }
}
