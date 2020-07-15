<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\filter\widgets;

use humhub\modules\ui\filter\widgets\FilterInput;
use humhub\libs\Html;

/**
 * Dropdown stream filter input type.
 * 
 * @since 1.6
 * @package humhub\modules\ui\filter\widgets
 */
class DropdownFilterInput extends FilterInput
{
    /**
     * @inheritdoc
     */
    public $view = 'dropdownInput';

    /**
     * @inheritdoc
     */
    public $type = 'dropdown';

    /**
     * @var array dropdown selection
     */
    public $selection = [];

    /**
     * @var array dropdown items
     */
    public $items = [];

    /**
     * @inheritdoc
     */
    public function prepareOptions()
    {
        parent::prepareOptions();
        $this->options['data-action-change'] = 'inputChange';
        Html::addCssClass($this->options, 'form-control');
    }

    /**
     * @inheritdoc
     */
    protected function getWidgetOptions()
    {
        return array_merge(parent::getWidgetOptions(), [
            'selection' => $this->selection,
            'items' => $this->items
        ]);
    }
}
