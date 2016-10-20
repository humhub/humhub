<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;


/**
 * Space Administration Menu
 *
 * @author Luke
 */
class DefaultMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    public function init()
    {
        $this->addItem(array(
            'label' => Yii::t('AdminModule.manage', 'Basic'),
            'url' => $this->space->createUrl('/space/manage/default/index'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->id == 'default' && Yii::$app->controller->action->id == 'index'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.manage', 'Advanced'),
            'url' => $this->space->createUrl('/space/manage/default/advanced'),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->id == 'default' && Yii::$app->controller->action->id == 'advanced'),
        ));
        parent::init();
    }

}
