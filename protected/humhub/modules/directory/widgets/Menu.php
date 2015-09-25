<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\user\models\Group;
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
        $spaceTypes = \humhub\modules\space\models\Type::find()->orderBy(['sort_key' => SORT_ASC])->where(['show_in_directory' => true])->all();

        $this->addItemGroup(array(
            'id' => 'directory',
            'label' => Yii::t('DirectoryModule.views_directory_layout', '<strong>Directory</strong> menu'),
            'sortOrder' => 100,
        ));

        if (Group::find()->count() > 1) {
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


        $spaceMenuCategory = 'directory';

        // Add own category for spaces if there are more than one space types
        if (count($spaceTypes) > 1) {

            $this->addItemGroup(array(
                'id' => 'spaces',
                'label' => Yii::t('DirectoryModule.views_directory_layout', 'Spaces'),
                'sortOrder' => 200,
            ));
            $spaceMenuCategory = 'spaces';

            $this->addItem(array(
                'label' => Yii::t('DirectoryModule.views_directory_layout', 'Spaces'),
                'group' => 'directory',
                'url' => Url::to(['/directory/directory/spaces']),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->action->id == "spaces" && Yii::$app->request->get('type_id') == ''),
            ));
        }

        foreach ($spaceTypes as $i => $type) {
            $this->addItem(array(
                'label' => $type->title,
                'group' => $spaceMenuCategory,
                'url' => Url::to(['/directory/directory/spaces', 'type_id' => $type->id]),
                'sortOrder' => 300 + $i,
                'isActive' => (Yii::$app->controller->action->id == "spaces" && Yii::$app->request->get('type_id') == $type->id),
            ));
        }

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
