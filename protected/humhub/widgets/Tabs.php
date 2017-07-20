<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 18.07.2017
 * Time: 09:14
 */


namespace humhub\widgets;

use Yii;

/**
 * Extends `\yii\bootstrap\Tabs` by providing providing view based tab items.
 *
 * View based tabs usage:
 *
 * <?=
 * Tabs::widget([
 *  'viewPath' => '@myModule/views/common',
 *  'params' => $_params_,
 *  'items' => [
 *    [
 *      'label' => 'One',
 *      'view' => 'example',
 *      'active' => true
 *    ],
 *    [
 *      'label' => 'Two',
 *      'view' => '@myModule/views/example',
 *      'params' => ['model' => new SomeModel()]
 *    ],
 *  ]
 * ]);
 * ?>
 *
 * @since 1.2.2
 * @see \yii\bootstrap\Tabs
 * @package humhub\widgets
 */
class Tabs extends \yii\bootstrap\Tabs
{
    /**
     * @var string contains the viewPath
     */
    public $viewPath;

    /**
     * @var array global view parameter will be used for all view based items without own params setting
     */
    public $params;

    /**
     * @inheritdoc
     */
    public $navType = 'nav-tabs tab-menu';

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if(!parent::beforeRun()) {
            return false;
        }

        $index = 0;
        foreach ($this->items as $key => $item) {
            if(isset($item['view'])) {
                $view = $item['view'];
                if($this->viewPath && strpos($view, '@') === false) {
                    $view = $this->viewPath . '/'.$item['view'];
                }

                $this->items[$key]['content'] = $this->render($view, $this->getParams($item));
                unset($item['view']);
                unset($item['params']);
            }

            if(!isset($item['sortOrder'])) {
                // keep stable sorting by adding counter (otherwise equal sorOrders will destroy index ordering)
                $this->items[$key]['sortOrder'] = 1000 + ($index * 10);
            }

            $index++;
        }

        $this->sortItems();

        return true;
    }

    /**
     * Returns the params for the given $item, if the $item does not provide own parameter settigns we use the global
     * params or an empty array if no params are provided at all.
     *
     * @param $item
     * @return array
     */
    private function getParams($item)
    {
        if(isset($item['params'])) {
            return $item['params'];
        }

        return !empty($this->params) ? $this->params : [];
    }

    /**
     * Checks if the current route contains the given route parts $modelId, $controllerId, §actionId
     * @param null $moduleId
     * @param null $controller
     * @param null $action
     * @return bool
     */
    public function isCurrentRoute($moduleId = null, $controllerId = null, $actionId = null)
    {
        if($moduleId && !(Yii::$app->controller->module && Yii::$app->controller->module->id == $moduleId)) {
            return false;
        }

        if($controllerId && !(Yii::$app->controller->id == $controllerId)) {
            return false;
        }

        if($actionId && !(Yii::$app->controller->action->id == $actionId)) {
            return false;
        }

        return true;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * Sorts the item attribute by sortOrder
     */
    private function sortItems()
    {
        usort($this->items, function ($a, $b) {
            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            } else
                if ($a['sortOrder'] < $b['sortOrder']) {
                    return - 1;
                } else {
                    return 1;
                }
        });
    }
}