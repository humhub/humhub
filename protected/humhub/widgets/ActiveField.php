<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

/**
 * A HumHub enhanced version of [[\yii\bootstrap\ActiveField]].
 *
 * @since 1.2
 * @author Luke
 */
class ActiveField extends \yii\bootstrap\ActiveField
{

    /**
     * @inheritdoc
     */
    public function widget($class, $config = [])
    {
        /* @var $class \yii\base\Widget */
        $config['model'] = $this->model;
        $config['attribute'] = $this->attribute;
        $config['view'] = $this->form->getView();

        if (isset($config['options']) && isset(class_parents($class)['humhub\widgets\InputWidget'])) {
            $this->adjustLabelFor($config['options']);
        }

        return parent::widget($class, $config);
    }

}
