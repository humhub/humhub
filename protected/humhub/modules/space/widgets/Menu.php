<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use Yii;
use yii\base\Exception;

/**
 * The Main Navigation for a space. It includes the Modules the Stream
 *
 * @author Luke
 * @since 0.5
 */
class Menu extends LeftNavigation
{

    /** @var Space */
    public $space;

    /** @var Space */
    public $id = 'space-main-menu';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->space) {
            $this->space = ContentContainerHelper::getCurrent(Space::class);
        }

        if (!$this->space) {
            throw new Exception('Could not instance space menu without space!');
        }

        $this->panelTitle = Yii::t('SpaceModule.base', '<strong>Space</strong> menu');

        parent::init();

        // For private Spaces without membership, show only the About Page in the menu.
        // This is necessary for the invitation process otherwise there is no access in this case anyway.
        if (!$this->space->isMember() && $this->space->visibility == Space::VISIBILITY_NONE) {
            $this->entries = [];
            $this->addAboutPage();
            return;
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.base', 'Stream'),
            'url' => $this->space->createUrl('/space/space/home'),
            'icon' => 'stream',
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('space', 'space', ['index', 'home']),
        ]));

        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        if (!$module->hideAboutPage) {
            $this->addAboutPage();
        }
    }

    private function addAboutPage()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.base', 'About'),
            'url' => $this->space->createUrl('/space/space/about'),
            'icon' => 'about',
            'sortOrder' => 10000,
            'isActive' => MenuLink::isActiveState('space', 'space', ['about']),
        ]));

    }


    /**
     * Searches for urls of modules which are activated for the current space
     * and offer an own site over the space menu.
     * The urls are associated with a module label.
     *
     * Returns an array of urls with associated module labes for modules
     */
    public static function getAvailablePages()
    {
        //Initialize the space Menu to check which active modules have an own page
        $entries = (new static())->getEntries(MenuLink::class);
        $result = [];
        foreach ($entries as $entry) {
            /* @var $entry MenuLink */
            $result[$entry->getUrl()] = $entry->getLabel();
        }

        return $result;
    }

    /**
     * Returns space default / homepage
     *
     * @param Space $space
     * @return string|null the url to redirect or null for default home
     */
    public static function getDefaultPageUrl($space)
    {
        return static::getAvailablePageUrl($space, 'indexUrl');
    }

    /**
     * Returns space default / homepage for guests
     *
     * @param $space Space
     * @return string|null the url to redirect or null for default home
     */
    public static function getGuestsDefaultPageUrl($space)
    {
        return static::getAvailablePageUrl($space, 'indexGuestUrl');
    }


    /**
     * Get default Space page URL by setting name
     *
     * @param Space $space
     * @param string $pageSettingName
     * @return string|null
     */
    public static function getAvailablePageUrl(Space $space, string $pageSettingName): ?string
    {
        /* @var Module $spaceModule */
        $spaceModule = Yii::$app->getModule('space');

        $indexUrl = $spaceModule->settings->contentContainer($space)->get($pageSettingName);
        if ($indexUrl === null) {
            return null;
        }

        $pages = static::getAvailablePages();

        return isset($pages[$indexUrl]) ? $indexUrl : null;
    }

}
