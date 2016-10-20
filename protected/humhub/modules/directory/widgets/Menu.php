<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use Yii;
use yii\helpers\Url;

use humhub\modules\directory\models\User;

/**
 * Directory Menu
 *
 * @since 0.21
 * @author Luke
 */
class Menu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/leftNavigation";

    public function init()
    {
        $this->addItemGroup(array(
            'id' => 'directory',
            'label' => Yii::t('DirectoryModule.views_directory_layout', '<strong>Directory</strong> menu'),
            'sortOrder' => 100,
        ));

        if (Yii::$app->getModule('directory')->isGroupListingEnabled()) {
            $this->addItem(array(
                'label' => Yii::t('DirectoryModule.views_directory_layout', 'Groups'),
                'group' => 'directory',
                'url' => Url::to(['/directory/directory/groups']),
                'sortOrder' => 100,
                'isActive' => (Yii::$app->controller->action->id == "groups"),
            ));
        }

        $this->addItem(array(
            'label' => Yii::t('DirectoryModule.views_directory_layout', 'Members'),
            'group' => 'directory',
            'url' => Url::to(['/directory/directory/members']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->action->id == "members"),
        ));

        $this->addItem(array(
            'label' => Yii::t('DirectoryModule.views_directory_layout', 'Spaces'),
            'group' => 'directory',
            'url' => Url::to(['/directory/directory/spaces']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->action->id == "spaces"),
        ));

        $this->addItem(array(
            'label' => Yii::t('DirectoryModule.views_directory_layout', 'User profile posts'),
            'group' => 'directory',
            'url' => Url::to(['/directory/directory/user-posts']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->action->id == "user-posts"),
        ));

        parent::init();
    }

}

?>
