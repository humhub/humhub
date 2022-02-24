<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\comment\widgets\CommentLink;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\like\widgets\LikeLink;
use humhub\widgets\BaseStack;
use yii\helpers\ArrayHelper;

/**
 * The ContentObjectLinks widget is used to display standard links for module content that implements its own detail view.
 * As an example, this is used at the end of a wiki page view.
 *
 * By default, the links for Comments and Likes are enabled here.
 *
 * Usage (in View file):
 * ```php
 * echo ContentObjectLinks::widget([
 *         'object' => $contentObject,
 *         'widgetParams' => [CommentLink::class => ['mode' => CommentLink::MODE_POPUP]],
 * ]);
 * ```
 *
 * @since 1.8
 */
class ContentObjectLinks extends BaseStack
{

    /**
     * @var ContentActiveRecord
     */
    public $object = null;

    /**
     * @inheritdoc
     */
    public $seperator = ' | ';

    /**
     * Can be set to overwrite or extend the widget params of a given widget as:
     *
     * ```
     * $widgetParams = [
     *      MyAddonWidget::class => [
     *          'paramName' => 'paramValue'
     *      ]
     * ]
     * ```
     * @var array
     */
    public $widgetParams = [];

    /**
     * Can be set to overwrite or extend the widget options of a given widget as:
     *
     * ```
     * $widgetOptions = [
     *      MyAddonWidget::class => [
     *          'optionName' => 'optionValue'
     *      ]
     * ]
     * ```
     * @var array
     */
    public $widgetOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDefaultWidgets();
        parent::init();
    }

    /**
     * Initialize default widgets for Content links
     */
    function initDefaultWidgets()
    {
        if (!($this->object instanceof ContentActiveRecord)) {
            return;
        }
        $this->addWidget(CommentLink::class, ['object' => $this->object], ['sortOrder' => 100]);
        $this->addWidget(LikeLink::class, ['object' => $this->object], ['sortOrder' => 200]);
    }

    /**
     * @inheritdoc
     */
    public function addWidget($className, $params = [], $options = [])
    {
        if (isset($this->widgetParams[$className])) {
            $params = ArrayHelper::merge($params, $this->widgetParams[$className]);
        }

        if (isset($this->widgetOptions[$className])) {
            $options = ArrayHelper::merge($options, $this->widgetOptions[$className]);
        }

        parent::addWidget($className, $params, $options);
    }

}
