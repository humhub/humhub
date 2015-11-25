<?php

namespace humhub\widgets;

class BaseMenu extends \yii\base\Widget
{

    const EVENT_INIT = 'init';
    const EVENT_RUN = 'run';

    /**
     *
     * @var Array of items
     */
    public $items = array();

    /**
     *
     * @var Array of item groups
     */
    public $itemGroups = array();

    /**
     *
     * @var String type of the navigation, optional for identifing.
     */
    public $type = "";

    /**
     * Template of the navigation
     *
     * Available default template views:
     * - leftNavigation
     * - tabMenu
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
        $this->addItemGroup(array(
            'id' => '',
            'label' => ''
        ));
        $this->trigger(self::EVENT_INIT);
        return parent::init();
    }

    /**
     * Adds new Item to the menu
     *
     * @param Array $item
     *            with item definitions
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
         *
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
            $item['htmlOptions']['class'] .= " " . $item['id'];
        }

        $this->items[] = $item;
    }

    /**
     * Adds new Item Group to the menu
     *
     * @param Array $itemGroup
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
     * @param String $group
     *            limits the items to a specified group
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
        $this->trigger(self::EVENT_RUN);
        return $this->render($this->template, array());
    }

    /**
     * Add the active class from a menue item.
     * 
     * @param String $url
     *            the URL of the item to mark. You can use Url::toRoute(...) to generate it.
     */
    public function markAsActive($url)
    {
        foreach ($this->items as $key => $item) {
            if ($item['url'] == $url) {
                $this->items[$key]['htmlOptions']['class'] = 'active';
                $this->items[$key]['htmlOptions']['isActive'] = true;
            }
        }
    }

    /**
     * Remove the active class from a menue item.
     * 
     * @param String $url
     *            the URL of the item to mark. You can use Url::toRoute(...) to generate it.
     */
    public function markAsInactive($url)
    {
        foreach ($this->items as $key => $item) {
            if ($item['url'] == $url) {
                $this->items[$key]['htmlOptions']['class'] = '';
                $this->items[$key]['htmlOptions']['isActive'] = false;
            }
        }
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
