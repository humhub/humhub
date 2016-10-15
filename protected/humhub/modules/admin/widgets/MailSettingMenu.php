<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Authentication Settings Menu
 */
class MailSettingMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/subTabMenu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_setting_mailing', 'General'),
            'url' => Url::toRoute(['/admin/setting/mailing']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'mailing'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_setting_mailing', 'Server Settings'),
            'url' => Url::toRoute(['/admin/setting/mailing-server']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'mailing-server'),
        ));

        parent::init();
    }

}
