<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\modules\user\components\PermissionManager;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;
use humhub\widgets\GridView;
use humhub\libs\Html;
use yii\data\DataProviderInterface;
use humhub\libs\DropDownGridColumn;

/**
 * PermissionGridView
 *
 * @author luke
 */
class PermissionGridEditor extends GridView
{
    /**
     * @var bool hide not changeable permissions
     */
    public $hideFixedPermissions = true;

    /**
     * @inheritdoc
     */
    public $showHeader = false;

    /**
     * @var PermissionManager
     */
    public $permissionManager;

    /**
     * @var string Group Id
     */
    public $groupId = "";

    /**
     * @var string used to group row headers
     */
    private $lastModuleId = '';

    /**
     * @inheritdoc
     */
    public $options = ['class' => 'grid-view permission-grid-editor'];

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
                    'label' => Yii::t('UserModule.base', 'Permission'),
                    'attribute' => 'title',
                    'content' => function ($data) {
                        $module = Yii::$app->getModule($data['moduleId']);
                        return Html::tag('strong', $data['title'])
                            . '&nbsp;&nbsp;'
                            . Html::tag('span', $module->getName(), ['class' => 'badge', 'data-module-id' => $data['moduleId']])
                            . Html::tag('br')
                            . $data['description'];
                    },
                ],
                [
                    'label' => '',
                    'class' => DropDownGridColumn::class,
                    'attribute' => 'state',
                    'readonly' => function ($data) {
                        return !($data['changeable']);
                    },
                    'submitAttributes' => ['permissionId', 'moduleId'],
                    'dropDownOptions' => 'states',
                ],
            ],
        ]);
        parent::init();
    }

    /**
     * Returns data provider
     *
     * @return DataProviderInterface
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function getDataProvider()
    {
        return new ArrayDataProvider([
            'allModels' => $this->permissionManager->createPermissionArray($this->groupId, $this->hideFixedPermissions),
            'pagination' => false,
            'sort' => [
                'attributes' => ['title', 'description', 'moduleId'],
            ],
        ]);
    }

}
