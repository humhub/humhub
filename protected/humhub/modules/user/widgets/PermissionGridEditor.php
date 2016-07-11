<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use yii\data\ArrayDataProvider;
use humhub\widgets\GridView;

/**
 * PermissionGridView
 *
 * @author luke
 */
class PermissionGridEditor extends GridView
{

    /**
     * @var \humhub\modules\user\components\PermissionManager
     */
    public $permissionManager;

    /**
     * @var string Group Id
     */
    public $groupId = "";

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::configure($this, [
            'dataProvider' => $this->getDataProvider(),
            'layout' => "{items}\n{pager}",
            'columns' => [
                [
                    'label' => Yii::t('UserModule.widgets_PermissionGridEditor', 'Title'),
                    'attribute' => 'title'
                ],
                [
                    'label' => Yii::t('UserModule.widgets_PermissionGridEditor', 'Description'),
                    'attribute' => 'description'
                ],
                [
                    'label' => Yii::t('UserModule.widgets_PermissionGridEditor', 'Module'),
                    'attribute' => 'moduleId'
                ],
                [
                    'label' => '',
                    'class' => 'humhub\libs\DropDownGridColumn',
                    'attribute' => 'state',
                    'readonly' => function($data) {
                        return ($data['changeable']);
                    },
                    'submitAttributes' => [ 'permissionId', 'moduleId'],
                    'dropDownOptions' => 'states'
                ],
            ],
        ]);

        parent::init();
    }

    /**
     * Returns data provider
     * 
     * @return \yii\data\DataProviderInterface
     */
    protected function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->permissionManager->createPermissionArray($this->groupId),
            'sort' => [
                'attributes' => ['title', 'description', 'moduleId'],
            ],
        ]);
    }

}
