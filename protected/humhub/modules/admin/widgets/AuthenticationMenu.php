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
class AuthenticationMenu extends \humhub\widgets\BaseMenu
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
        $groupId = Yii::$app->request->get('id');

        $this->addItem(array(
            'label' => Yii::t('AdminModule.setting', 'General'),
            'url' => Url::toRoute(['/admin/authentication']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'authentication' && Yii::$app->controller->action->id == 'index'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.setting', "LDAP"),
            'url' => Url::toRoute(['/admin/authentication/authentication-ldap']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'authentication' && Yii::$app->controller->action->id == 'authentication-ldap'),
        ));

        parent::init();
    }

}
