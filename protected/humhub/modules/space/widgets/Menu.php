<?php

namespace humhub\modules\space\widgets;

use Yii;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;

/**
 * The Main Navigation for a space. It includes the Modules the Stream
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class Menu extends \humhub\widgets\BaseMenu
{
    /** @var Space */
    public $space;
    public $template = "@humhub/widgets/views/leftNavigation";

    public function init()
    {
        if ($this->space === null && Yii::$app->controller instanceof ContentContainerController && Yii::$app->controller->contentContainer instanceof Space) {
            $this->space = Yii::$app->controller->contentContainer;
        }

        if ($this->space === null) {
            throw new \yii\base\Exception("Could not instance space menu without space!");
        }

        $this->addItemGroup(array(
            'id' => 'modules',
            'label' => Yii::t('SpaceModule.widgets_SpaceMenuWidget', '<strong>Space</strong> menu'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMenuWidget', 'Stream'),
            'group' => 'modules',
            'url' => $this->space->createUrl('/space/space/home'),
            'icon' => '<i class="fa fa-bars"></i>',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->id == "space" && (Yii::$app->controller->action->id == "index" || Yii::$app->controller->action->id == 'home') && Yii::$app->controller->module->id == "space"),
        ));

        parent::init();
    }

    /**
     * Searches for urls of modules which are activated for the current space
     * and offer an own site over the space menu.
     * The urls are associated with a module label.
     * 
     * Returns an array of urls with associated module labes for modules 
     * @param type $space
     */
    public static function getAvailablePages()
    {
        //Initialize the space Menu to check which active modules have an own page
        $moduleItems = (new static())->getItems('modules');
        $result = [];
        foreach ($moduleItems as $moduleItem) {
            $result[$moduleItem['url']] = $moduleItem['label'];
        }
        return $result;
    }

    /**
     * Returns space default / homepage
     * 
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
            } else {
                //Either the module was deactivated or url changed
                $settings->contentContainer($space)->delete('indexUrl');
            }
        }
        return null;
    }

    /**
     * Returns space default / homepage
     * 
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
            } else {
                //Either the module was deactivated or url changed
                $settings->contentContainer($space)->delete('indexGuestUrl');
            }
        }
        return null;
    }

}

?>
