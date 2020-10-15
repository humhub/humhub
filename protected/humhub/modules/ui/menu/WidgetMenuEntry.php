<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\libs\Html;
use humhub\widgets\Link;
use Yii;
use yii\helpers\Url;

/**
 * Class WidgetMenuEntry
 *
 * Widget based menu entry
 *
 * @since 1.4
 * @see Menu
 */
class WidgetMenuEntry extends MenuEntry
{
    public $widgetClass;

    public $widgetOptions;

    /**
     * Renders the link tag for this menu entry
     *
     * @param array $extraHtmlOptions
     * @return string the Html link
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        try {
            return call_user_func($this->widgetClass.'::widget', $this->widgetOptions);
        } catch(\Exception $e) {
            Yii::error($e);
        }
    }

    /**
     * @inheritDoc
     * @since 1.7
     */
    public function getEntryClass()
    {
        return $this->widgetClass;
    }
}
