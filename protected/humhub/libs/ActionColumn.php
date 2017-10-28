<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use yii\grid\Column;
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
        $html = Html::beginTag('div', ['class' => 'btn-group dropdown-navigation']);
        $html .= Html::button('<i class="fa fa-cog"></i> <span class="caret"></span>', ['class' => 'btn btn-default dropdown-toggle', 'data-toggle' => 'dropdown']);
        $html .= Html::beginTag('ul', ['class' => 'dropdown-menu pull-right']);
        foreach ($this->actions as $title => $url) {
            if ($url === '---') {
                $html .= '<li class="divider"></li>';
            } else {
                $html .= Html::beginTag('li');
                $html .= Html::a($title, $this->handleUrl($url, $model));
                $html .= Html::endTag('li');
            }
        }
        $html .= Html::endTag('ul');
        $html .= Html::endTag('div');


        return $html;
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
        $url[$this->modelIdAttribute] = $model->getAttribute($this->modelIdAttribute);

        return \yii\helpers\Url::to($url);
    }

}
