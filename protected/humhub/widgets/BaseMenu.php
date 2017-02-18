<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use yii\helpers\Url;

/**
 * BaseMenu is the base class for navigations.
 */
class BaseMenu extends \yii\base\Widget
{

    const EVENT_INIT = 'init';
    const EVENT_RUN = 'run';

    /**
     *
     * @var array of items
     */
    public $items = array();

    /**
     *
     * @var array of item groups
     */
    public $itemGroups = array();

    /**
     *
     * @var string type of the navigation, optional for identifing.
     */
    public $type = "";

    /**
     * @var string dom element id
     * @since 1.2
     */
    public $id;

    /**
     * Template of the navigation
     *
     * Available default template views:
     * - leftNavigation
     * - tabMenu
     *
     * @var string template file
     */
    public $template;

    /**
     * Initializes the navigation widget.
     * This method mainly normalizes the {@link items} property.
     * If this method is overridden, make sure the parent implementation is invoked.
     */
    public function init()
    {
        $this->addItemGroup(array(
            'id' => '',
            'label' => ''
        ));

        // Yii 2.0.11 introduced own init event
        if (version_compare(Yii::getVersion(), '2.0.11', '<')) {
            $this->trigger(self::EVENT_INIT);
        }
        return parent::init();
    }

    /**
     * Adds new Item to the menu
     *
     * @param array $item
     *            with item definitions
     */
    public function addItem($item)
    {
        if (!isset($item['label'])) {
            $item['label'] = 'Unnamed';
        }

        if (!isset($item['url'])) {
            $item['url'] = '#';
        }

        if (!isset($item['icon'])) {
            $item['icon'] = '';
        }


        if (!isset($item['group'])) {
            $item['group'] = '';
        }

        if (!isset($item['htmlOptions'])) {
            $item['htmlOptions'] = array();
        }

        if (!isset($item['pjax'])) {
            $item['pjax'] = true;
        }

        /**
         *
         * @deprecated since version 0.11 use directly htmlOptions instead
         */
        if (isset($item['target'])) {
            $item['htmlOptions']['target'] = $item['target'];
        }

        if (!isset($item['sortOrder'])) {
            $item['sortOrder'] = 1000;
        }

        if (!isset($item['newItemCount'])) {
            $item['newItemCount'] = 0;
        }

        if (!isset($item['isActive'])) {
            $item['isActive'] = false;
        }
        if (isset($item['isVisible']) && !$item['isVisible']) {
            return;
        }

        // Build Item CSS Class
        if (!isset($item['htmlOptions']['class'])) {
            $item['htmlOptions']['class'] = "";
        }

        if ($item['isActive']) {
            $item['htmlOptions']['class'] .= " active";
        }

        if (isset($item['id'])) {
            $item['htmlOptions']['class'] .= " " . $item['id'];
        }

        $this->items[] = $item;
    }

    /**
     * Adds new Item Group to the menu
     *
     * @param array $itemGroup
     *            with group definition
     */
    public function addItemGroup($itemGroup)
    {
        if (!isset($itemGroup['id']))
            $itemGroup['id'] = 'default';

        if (!isset($itemGroup['label']))
            $itemGroup['label'] = 'Unnamed';

        if (!isset($itemGroup['icon']))
            $itemGroup['icon'] = '';

        if (!isset($itemGroup['sortOrder']))
            $itemGroup['sortOrder'] = 1000;

        if (isset($itemGroup['isVisible']) && !$itemGroup['isVisible'])
            return;

        $this->itemGroups[] = $itemGroup;
    }

    /**
     * Returns Items of this Navigation
     *
     * @param string $group
     *            limits the items to a specified group
     * @return array a list of items with definition
     */
    public function getItems($group = "")
    {
        $this->sortItems();

        $ret = array();

        foreach ($this->items as $item) {

            if ($group == $item['group'])
                $ret[] = $item;
        }

        return $ret;
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

    /**
     * Sorts Item Groups by sortOrder Field
     */
    private function sortItemGroups()
    {
        usort($this->itemGroups, function ($a, $b) {
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

    /**
     * Returns all Item Groups
     *
     * @return array of item group definitions
     */
    public function getItemGroups()
    {
        $this->sortItemGroups();
        return $this->itemGroups;
    }

    /**
     * Executes the Menu Widget
     */
    public function run()
    {
        $this->trigger(self::EVENT_RUN);

        if (empty($this->template)) {
            return;
        }

        return $this->render($this->template, array());
    }

    /**
     * Activates the menu item with the given url
     * @param type $url
     */
    public function setActive($url)
    {
        foreach ($this->items as $key => $item) {
            if ($item['url'] == $url) {
                $this->items[$key]['htmlOptions']['class'] = 'active';
                $this->items[$key]['isActive'] = true;
                $this->view->registerJs('humhub.modules.ui.navigation.setActive("' . $this->id . '", ' . json_encode($this->items[$key]) . ');', \yii\web\View::POS_END, 'active-' . $this->id);
            }
        }
    }

    public function getActive()
    {
        foreach ($this->items as $item) {
            if ($item['isActive']) {
                return $item;
            }
        }
    }

    /*
     * Deactivates the menu item with the given url
     */

    public function setInactive($url)
    {
        foreach ($this->items as $key => $item) {
            if ($item['url'] == $url) {
                $this->items[$key]['htmlOptions']['class'] = '';
                $this->items[$key]['isActive'] = false;
            }
        }
    }

    /**
     * Add the active class from a menue item.
     * 
     * @param string $url
     *            the URL of the item to mark. You can use Url::toRoute(...) to generate it.
     */
    public static function markAsActive($url)
    {
        if (is_array($url)) {
            $url = Url::to($url);
        }

        \yii\base\Event::on(static::className(), static::EVENT_RUN, function($event) use($url) {
            $event->sender->setActive($url);
        });
    }

    /**
     * This function is used in combination with pjax to get sure the required menu is active
     */
    public static function setViewState()
    {
        $instance = new static();
        if (!empty($instance->id)) {
            $active = $instance->getActive();
            $instance->view->registerJs('humhub.modules.ui.navigation.setActive("' . $instance->id . '", ' . json_encode($active) . ');', \yii\web\View::POS_END, 'active-' . $instance->id);
        }
    }

    /**
     * Remove the active class from a menue item.
     * 
     * @param string $url
     *            the URL of the item to mark. You can use Url::toRoute(...) to generate it.
     */
    public static function markAsInactive($url)
    {
        if (is_array($url)) {
            $url = Url::to($url);
        }

        \yii\base\Event::on(static::className(), static::EVENT_RUN, function($event) use($url) {
            $event->sender->setInactive($url);
        });
    }

    /**
     * Removes Item by URL
     * 
     * @param string $url
     */
    public function deleteItemByUrl($url)
    {
        foreach ($this->items as $key => $item) {
            if ($item['url'] == $url) {
                unset($this->items[$key]);
            }
        }
    }

}

?>
