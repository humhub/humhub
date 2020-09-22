<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\grid\Column;
use yii\helpers\Url;
use humhub\libs\Html;

/**
 * Description of ActionColumn
 *
 * @author Luke
 */
class ActionColumn extends Column
{

    /**
     * @var string the ID attribute of the model, to generate action URLs.
     */
    public $modelIdAttribute = 'id';

    /**
     * @var array list of actions (key = title, value = url)
     */
    public $actions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->options['style'] = 'width:56px';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $actions = $this->getActions($model, $key, $index);

        if (empty($actions)) {
            return '';
        }

        $html = Html::beginTag('div', ['class' => 'btn-group dropdown-navigation']);
        $html .= Html::button('<i class="fa fa-cog"></i> <span class="caret"></span>', ['class' => 'btn btn-default dropdown-toggle', 'data-toggle' => 'dropdown']);
        $html .= Html::beginTag('ul', ['class' => 'dropdown-menu pull-right']);
        foreach ($actions as $title => $url) {
            if ($url === '---') {
                $html .= '<li class="divider"></li>';
            } else {
                $linkOptions = null;
                if (isset($url['linkOptions'])) {
                    $linkOptions = $url['linkOptions'];
                    unset($url['linkOptions']);
                }

                $html .= Html::beginTag('li');
                $html .= Html::a($title, $this->handleUrl($url, $model), $linkOptions);
                $html .= Html::endTag('li');
            }
        }
        $html .= Html::endTag('ul');
        $html .= Html::endTag('div');


        return $html;
    }

    protected function getActions($model, $key, $index)
    {
        if ($this->actions === null) {
            return [];
        } elseif (is_callable($this->actions)) {
            return call_user_func($this->actions, $model, $key, $index, $this);
        }

        return $this->actions;
    }

    /**
     * Builds the URL for a given Action
     * 
     * @param array $url
     * @param \yii\base\Model $model
     * @return string the url
     */
    protected function handleUrl($url, $model)
    {
        if (!isset($url[$this->modelIdAttribute])) {
            $url[$this->modelIdAttribute] = $model->getAttribute($this->modelIdAttribute);
        }

        return Url::to($url);
    }
}
