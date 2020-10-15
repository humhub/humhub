<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu\widgets;

use humhub\components\Event;
use humhub\libs\Sort;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\BaseStack;
use humhub\widgets\JsWidget;
use Yii;
use yii\helpers\Url;
use yii\web\View;

/**
 * Base class for menus and navigations.
 *
 * @since 1.4
 * @package humhub\modules\ui\widgets
 */
abstract class Menu extends JsWidget
{
    /**
     * @event MenuEvent an event raised before running the navigation widget.
     */
    const EVENT_RUN = 'run';

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

        if (empty($this->template) || empty($this->entries)) {
            return '';
        }

        if ($this->template === '@humhub/widgets/views/leftNavigation') {
            Yii::debug('Deprecated usage of leftNavigation view!');
            $this->template = '@ui/menu/widgets/views/left-navigation.php';
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
            'entries' => $this->getSortedEntries(),
            'options' => $this->getOptions(),
            // Deprecated
            'items' => $this->getItems(),
            'numItems' => count($this->getItems())
        ];
    }

    /**
     * Sorts the entry list by sortOrder and returns the sorted entry list.
     *
     * @return MenuEntry[]
     */
    public function getSortedEntries()
    {
        return Sort::sort($this->entries, 'sortOrder', BaseStack::DEFAULT_SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'menu-id' => $this->id
        ];
    }

    /**
     * Returns the first entry with the given URL
     *
     * @param $url string|array the url or route
     * @return MenuLink
     */
    public function getEntryByUrl($url)
    {
        if (is_array($url)) {
            $url = Url::to($url);
        }

        foreach ($this->entries as $entry) {
            if (!$entry instanceof MenuLink) {
                continue;
            }

            if ($entry->getUrl() === $url) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Returns the first entry with the given id
     *
     * @param $id string the menueId
     * @return MenuEntry
     */
    public function getEntryById($id)
    {
        foreach ($this->entries as $entry) {
            if (!$entry instanceof MenuEntry) {
                continue;
            }

            if ($entry->getId() === $id) {
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
    public function setEntryActive(MenuEntry $entry)
    {
        foreach ($this->entries as $currentEntry) {
            $currentEntry->setIsActive(($currentEntry->compare($entry)));
        }
    }

    /**
     * -------------------------------------------------------------------
     *                       Compatibility Layer
     * -------------------------------------------------------------------
     */

    /**
     * @param array $entryArray
     * @deprecated since 1.4
     */
    public function addItem($entryArray)
    {
        $entry = MenuLink::createByArray($entryArray);
        $this->addEntry($entry);
    }

    /**
     * @return array item group
     * @deprecated since 1.4 not longer supported!
     */
    public function addItemGroup($itemGroup)
    {
        //throw new InvalidCallException('Item groups are not longer supported');
    }

    /**
     * @return array the item group
     * @deprecated since 1.4
     */
    public function getItemGroups()
    {
        return [
            ['id' => 'default', 'label' => '', 'icon' => '', 'sortOrder' => 1000]
        ];
    }

    /**
     * @return array the menu items as array list
     * @deprecated since 1.4
     */
    public function getItems($group = '')
    {
        $items = [];
        foreach ($this->entries as $entry) {
            if ($entry instanceof MenuLink) {
                $items[] = $entry->toArray();
            }
        }
        return $items;
    }

    /**
     * Returns all entries filtered by $type. If no $type filter is given all entries
     * are returned.
     *
     * If $filterVisible is set, only visible entries will be returned
     *
     * @param null|string $type
     * @param bool $filterVisible
     * @return MenuEntry[]
     */
    public function getEntries($type = null, $filterVisible = false)
    {
        $result = [];
        foreach ($this->getSortedEntries() as $entry) {
            if ((!$filterVisible || $entry->isVisible()) && (!$type || get_class($entry) === $type || is_subclass_of($entry, $type))) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    /**
     * @param null $type
     * @param bool $filterVisible
     * @return MenuEntry|null
     */
    public function getFirstEntry($type = null, $filterVisible = false)
    {
        $entries = $this->getEntries($type, $filterVisible);
        if (count($entries)) {
            return $entries[0];
        }

        return null;
    }

    /**
     * Checks if this menu contains multiple entries of the given $type, or at all if no $type filter is given.
     *
     * @param null $type
     * @return bool
     */
    public function hasMultipleEntries($type = null)
    {
        return count($this->getEntries($type)) > 1;
    }

    /**
     * Activates an entry by given id or url search string.
     * @param $searchStr menu entry id or url
     */
    public function setActive($searchStr)
    {
        $entry = $this->getEntryById($searchStr);

        if (!$entry) {
            $entry = $this->getEntryByUrl($searchStr);
        }

        if ($entry) {
            $this->setEntryActive($entry);
        }
    }

    /**
     * Deactivates an entry by given id or url search string.
     * @param $searchStr menu entry id or url
     */
    public function setInactive($searchStr)
    {
        $entry = $this->getEntryById($searchStr);

        if (!$entry) {
            $entry = $this->getEntryByUrl($searchStr);
        }

        if ($entry) {
            $entry->setIsActive(false);
        }
    }

    /**
     * This function provides static menu entry activation, by entry id or url.
     */
    public static function markAsActive($searchStr)
    {
        Event::on(static::class, static::EVENT_RUN, function ($event) use ($searchStr) {
            $event->sender->setActive($searchStr);
        });
    }

    /**
     * This function provides static menu entry inactivation, by entry id or url.
     */
    public static function markAsInactive($url)
    {
        Event::on(static::class, static::EVENT_RUN, function ($event) use ($url) {
            $event->sender->setInactive($url);
        });
    }

    /**
     * @return array the menu entry as array
     * @deprecated since 1.4
     */
    public function getActive()
    {
        $activeEntry = $this->getActiveEntry();
        if ($activeEntry && $activeEntry instanceof MenuLink) {
            return $activeEntry->toArray();
        }

        return null;
    }

    /**
     * @param $url string the URL or route
     * @deprecated since 1.4
     */
    public function deleteItemByUrl($url)
    {
        $entry = $this->getEntryByUrl($url);
        if ($entry) {
            $this->removeEntry($entry);
        }
    }

    /**
     * @deprecated since 1.4
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
