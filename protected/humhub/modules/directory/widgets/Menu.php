<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\directory\Module;
use humhub\modules\ui\menu\widgets\LeftNavigation;

/**
 * Directory module navigation
 *
 * @since 0.21
 * @author Luke
 */
class Menu extends LeftNavigation
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('directory');

        $this->panelTitle = Yii::t('DirectoryModule.base', '<strong>Directory</strong> menu');

        if ($module->isGroupListingEnabled()) {
            $this->addItem([
                'label' => Yii::t('DirectoryModule.base', 'Groups'),
                'group' => 'directory',
                'url' => Url::to(['/directory/directory/groups']),
                'sortOrder' => 100,
                'isActive' => (Yii::$app->controller->action->id == "groups"),
            ]);
        }

        $this->addItem([
            'label' => Yii::t('DirectoryModule.base', 'Members'),
            'group' => 'directory',
            'url' => Url::to(['/directory/directory/members']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->action->id == "members"),
        ]);

        $this->addItem([
            'label' => Yii::t('DirectoryModule.base', 'Spaces'),
            'group' => 'directory',
            'url' => Url::to(['/directory/directory/spaces']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->action->id == "spaces"),
        ]);

        if ($module->showUserProfilePosts) {
            $this->addItem([
                'label' => Yii::t('DirectoryModule.base', 'User profile posts'),
                'group' => 'directory',
                'url' => Url::to(['/directory/directory/user-posts']),
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->action->id == "user-posts"),
            ]);
        }

        parent::init();
    }

}

?>
