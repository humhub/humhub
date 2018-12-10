<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu\widgets;

use humhub\components\Event;
use humhub\components\Widget;
use humhub\modules\ui\menu\MenuEntry;
use yii\helpers\Url;
use yii\web\View;

/**
 * Base class for menus and navigations.
 *
 * @since 1.4
 * @package humhub\modules\ui\widgets
 */
abstract class Menu extends Widget
{
    /**
     * @event MenuEvent an event raised before running the navigation widget.
     */
    const EVENT_RUN = 'run';

    /**
     * @var string dom element id
     */
    public $id;

    /**
     * @var string template view file of the navigation
     */
    public $template;

    /**
     * @var MenuEntry[] the menu entries
     */
    protected $entries = [];

    /**
     * Add new menu entry to the navigation
     *
     * @param MenuEntry $entry
     */
    public function addEntry(MenuEntry $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * Removes the entry from the navigation
     *
     * @param MenuEntry $entry
     * @return boolean
     */
    public function removeEntry($entry)
    {
        foreach ($this->entries as $i => $e) {
            if ($e === $entry) {
                unset($this->entries[$i]);
                return true;
            }
        }

        return false;
    }

    /**
     * Executes the navigation widget.
     *
     * @return string the result of navigation widget execution to be outputted.
     */
    public function run()
    {
        $this->trigger(static::EVENT_RUN);

        if (empty($this->template)) {
            return '';
        }

        return $this->render($this->template, $this->getViewParams());
    }

    /**
     * Returns the parameters which are passed to the view template
     *
     * @return array the view parameters
     */
    protected function getViewParams()
    {
        return [
            'menu' => $this,
            'entries' => $this->entries,

            // Deprecated
            'items' => $this->getItems(),
            'numItems' => count($this->getItems())
        ];
    }

    /**
     * Returns the first entry with the given URL
     *
     * @param $url string|array the url or route
     * @return MenuEntry
     */
    public function getEntryByUrl($url)
    {
        if (is_array($url)) {
            $url = Url::to($url);
        }

        foreach ($this->entries as $entry) {
            if ($entry->getUrl() === $url) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Returns the first active menu entry
     *
     * @return MenuEntry
     */
    public function getActiveEntry()
    {
        foreach ($this->entries as $entry) {
            if ($entry->getIsActive()) {
                return $entry;
            }
        }
    }

    /**
     * Sets an menu entry active and inactive all other entries
     *
     * @param MenuEntry $entry
     */
    public function setEntryActive($entry)
    {
        foreach ($this->entries as $e) {
            $e->setIsActive(($entry->getUrl() === $e->getUrl()));
        }
    }

    /**
     * -------------------------------------------------------------------
     *                       Compatibility Layer
     * -------------------------------------------------------------------
     */

    /**
     * @deprecated
     * @param array $entryArray
     */
    public function addItem($entryArray)
    {
        $entry = MenuEntry::createByArray($entryArray);
        $this->addEntry($entry);
    }

    /**
     * @deprecated since 1.4 not longer supported!
     * @return array item group
     */
    public function addItemGroup($itemGroup)
    {
        //throw new InvalidCallException('Item groups are not longer supported');
    }

    /**
     * @deprecated
     * @return array the item group
     */
    public function getItemGroups()
    {
        return [
            ['id' => 'default', 'label' => '', 'icon' => '', 'sortOrder' => 1000]
        ];
    }

    /**
     * @deprecated
     * @return array the menu items as array list
     */
    public function getItems($group = '')
    {
        $items = [];
        foreach ($this->entries as $entry) {
            $items[] = $entry->toArray();
        }
        return $items;
    }

    /**
     * @deprecated
     */
    public function setActive($url)
    {
        $entry = $this->getEntryByUrl($url);
        if ($entry) {
            $this->setEntryActive($entry);
        }
    }

    /**
     * @deprecated
     */
    public function setInactive($url)
    {
        $entry = $this->getEntryByUrl($url);
        if ($entry) {
            $entry->setIsActive(false);
        }
    }

    /**
     * @deprecated
     */
    public static function markAsActive($url)
    {
        Event::on(static::class, static::EVENT_RUN, function ($event) use ($url) {
            $event->sender->setActive($url);
        });
    }

    /**
     * @deprecated
     */
    public static function markAsInactive($url)
    {
        Event::on(static::class, static::EVENT_RUN, function ($event) use ($url) {
            $event->sender->setInactive($url);
        });
    }

    /**
     * @deprecated
     * @return array the menu entry as array
     */
    public function getActive()
    {
        $entry = $this->getActiveEntry();
        if ($entry) {
            return $entry->toArray();
        }
    }

    /**
     * @deprecated
     * @param $url string the URL or route
     */
    public function deleteItemByUrl($url)
    {
        $entry = $this->getEntryByUrl($url);
        if ($entry) {
            $this->removeEntry($entry);
        }
    }

    /**
     * @deprecated
     */
    public static function setViewState()
    {
        $instance = new static();
        if (!empty($instance->id)) {
            $active = $instance->getActive();
            $instance->view->registerJs('humhub.modules.ui.navigation.setActive("' . $instance->id . '", ' . json_encode($active) . ');', View::POS_END, 'active-' . $instance->id);
        }
    }

}
