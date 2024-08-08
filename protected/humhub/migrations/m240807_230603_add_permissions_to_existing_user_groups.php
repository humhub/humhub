<?php

use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\user\models\Group;
use yii\db\Migration;

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
            $groups = Group::find();

            /** @var Group $group */
            foreach ($groups->each() as $group) {
                $alreadySet = Yii::$app->user->permissionManager->getGroupState(
                    $group->id,
                    Yii::createObject(CreatePrivateSpace::class),
                    false,
                );
                if ($alreadySet == '') {
                    Yii::$app->user->permissionManager->setGroupState(
                        $group->id,
                        CreatePrivateSpace::class,
                        CreatePrivateSpace::STATE_ALLOW,
                    );
                }

                $alreadySet = Yii::$app->user->permissionManager->getGroupState(
                    $group->id,
                    Yii::createObject(CreatePublicSpace::class),
                    false,
                );
                if ($alreadySet == '') {
                    Yii::$app->user->permissionManager->setGroupState(
                        $group->id,
                        CreatePublicSpace::class,
                        CreatePublicSpace::STATE_ALLOW,
                    );
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
