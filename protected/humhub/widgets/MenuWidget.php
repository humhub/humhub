<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * MenuWidget is the base class for navigations/menus.
 *
 * It allows modules to inject new items by events.
 *
 * @package humhub.widgets
 * @since 0.5
 * @author Luke
 */
class MenuWidget extends HWidget
{

    /**
     * @var Array of items
     */
    public $items = array();

    /**
     * @var Array of item groups
     */
    public $itemGroups = array();

    /**
     * @var String type of the navigation, optional for identifing.
     */
    public $type = "";

    /**
     * Template of the navigation
     *
     * Available default template views:
     *      - leftNavigation
     *      - tabMenu
     *
     * @var String template file
     */
    public $template;

    /**
     * Initializes the navigation widget.
     * This method mainly normalizes the {@link items} property.
     * If this method is overridden, make sure the parent implementation is invoked.
     */
    public function init()
    {

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);

        $this->addItemGroup(array('id' => '', 'label' => ''));

        // Fire Event
        if ($this->hasEventHandler('onInit'))
            $this->onInit(new CEvent($this));

        return parent::init();
    }

    /**
     * This event is raised after init is performed.
     *
     * @param CEvent $event the event parameter
     */
    public function onInit($event)
    {
        $this->raiseEvent('onInit', $event);
    }

    /**
     * Adds new Item to the menu
     *
     * @param Array $item with item definitions
     */
    public function addItem($item)
    {

        if (!isset($item['label']))
            $item['label'] = 'Unnamed';

        if (!isset($item['url']))
            $item['url'] = '#';

        if (!isset($item['icon']))
            $item['icon'] = '';

        if (!isset($item['group']))
            $item['group'] = '';

        if (!isset($item['htmlOptions']))
            $item['htmlOptions'] = array();
        
        /**
         * @deprecated since version 0.11 use directly htmlOptions instead
         */
        if (isset($item['target'])) {
            $item['htmlOptions']['target'] = $item['target'];
        }
 
        if (!isset($item['sortOrder']))
            $item['sortOrder'] = 1000;

        if (!isset($item['newItemCount']))
            $item['newItemCount'] = 0;

        if (!isset($item['isActive']))
            $item['isActive'] = false;

        if (isset($item['isVisible']) && !$item['isVisible'])
            return;

        
        // Build Item CSS Class
        if (!isset($item['htmlOptions']['class']))
            $item['htmlOptions']['class'] = "";
        
        if ($item['isActive']) {
            $item['htmlOptions']['class'] .= " active";
        }
        
        if (isset($item['id'])) {
            $item['htmlOptions']['class'] .= " ".$item['id'];
        }
        
        
        $this->items[] = $item;
    }

    /**
     * Adds new Item Group to the menu
     *
     * @param Array $itemGroup with group definition
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
     * @param String $group limits the items to a specified group
     * @return Array a list of items with definition
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

        usort($this->items, function($a, $b) {
            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            } else if ($a['sortOrder'] < $b['sortOrder']) {
                return -1;
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

        usort($this->itemGroups, function($a, $b) {
            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            } else if ($a['sortOrder'] < $b['sortOrder']) {
                return -1;
            } else {
                return 1;
            }
        });
    }

    /**
     * Returns all Item Groups
     *
     * @return Array of item group definitions
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

        // Fire Event
        if ($this->hasEventHandler('onRun'))
            $this->onRun(new CEvent($this));

        $this->render($this->template, array());
    }

    /**
     * This event is raised before run is performed.
     * @param CEvent $event the event parameter
     */
    public function onRun($event)
    {
        $this->raiseEvent('onRun', $event);
    }

}

?>
