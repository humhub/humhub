<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\modules\ui\form\interfaces\TabbedFormModel;
use humhub\widgets\Tabs;
use yii\base\Model;

/**
 * Class TabsForm
 *
 * @since 1.11.0
 */
class FormTabs extends Tabs
{

    /**
     * @var TabbedFormModel
     */
    public $form;

    /**
     * @inheritdoc
     */
    protected function beforeSortItems()
    {
        $this->initTabbedFormItems();

        parent::beforeSortItems();
    }

    private function initTabbedFormItems()
    {
        if (!($this->form instanceof TabbedFormModel && $this->form instanceof Model)) {
            return;
        }

        $items = $this->form->tabs;
        if (empty($items)) {
            return;
        }

        $this->items = $items;

        if (!$this->form->hasErrors()) {
            return;
        }

        // Find first error with field and activate that tab
        $errorFields = array_keys($this->form->getErrors());
        foreach ($this->items as $t => $tab) {
            if (!empty(array_intersect($tab['fields'], $errorFields))) {
                $this->items[$t]['active'] = true;
                // Stop on first found error
                return;
            }
        }
    }
}