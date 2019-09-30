<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\space\models\Space;
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
        if(!$this->space) {
            $this->space = ContentContainerHelper::getCurrent(Space::class);
        }

        if (!$this->space) {
            throw new Exception('Could not instance space menu without space!');
        }

        $this->panelTitle = Yii::t('SpaceModule.base', '<strong>Space</strong> menu');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.base', 'Stream'),
            'url' => $this->space->createUrl('/space/space/home'),
            'icon' => 'fa-bars',
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('space', 'space', ['index', 'home']),
        ]));

        parent::init();
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
        $settings = Yii::$app->getModule('space')->settings;

        $indexUrl = $settings->contentContainer($space)->get('indexUrl');
        if ($indexUrl !== null) {
            $pages = static::getAvailablePages();
            if (isset($pages[$indexUrl])) {
                return $indexUrl;
            }

            //Either the module was deactivated or url changed
            $settings->contentContainer($space)->delete('indexUrl');
        }

        return null;
    }

    /**
     * Returns space default / homepage
     *
     * @param $space Space
     * @return string|null the url to redirect or null for default home
     */
    public static function getGuestsDefaultPageUrl($space)
    {
        $settings = Yii::$app->getModule('space')->settings;

        $indexUrl = $settings->contentContainer($space)->get('indexGuestUrl');
        if ($indexUrl !== null) {
            $pages = static::getAvailablePages();
            if (isset($pages[$indexUrl])) {
                return $indexUrl;
            }

            //Either the module was deactivated or url changed
            $settings->contentContainer($space)->delete('indexGuestUrl');
        }

        return null;
    }

}
