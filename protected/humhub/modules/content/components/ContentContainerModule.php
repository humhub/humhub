<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;


use humhub\components\Module;

/**
 * Base Module with ContentContainer support.
 * 
 * Override this class if your module should have the possibility to be 
 * enabled/disabled on a content container (e.g. Space/User).
 *  
 * @since 0.20
 * @author luke
 */
class ContentContainerModule extends Module
{

    /**
     * Returns the list of valid content container classes this module supports.
     * 
     * ~~~
     * public function getContentContainerTypes()
     * {
     *      return [
     *          User::className(),
     *          Space::className()
     *      ];
     * }
     * ~~~
     * 
     * @return array valid content container classes
     */
    public function getContentContainerTypes()
    {
        return [];
    }

    /**
     * Checks whether the module is enabled the given content container class.
     * 
     * @param string $class the class of content container
     * @return boolean
     */
    public function hasContentContainerType($class)
    {
        return in_array($class, $this->getContentContainerTypes());
    }

    /**
     * Returns the module description shown in content container modules section.
     * By default the main module description is returned.
     * 
     * @param string $container
     * @return string the module description
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        return $this->getDescription();
    }

    /**
     * Returns the name of the module used in content container context.
     * By default the main module name is returned.
     * 
     * @param ContentContainerActiveRecord $container
     * @return string the module name
     */
    public function getContentContainerName(ContentContainerActiveRecord $container)
    {
        return $this->getName();
    }

    /**
     * Returns the url to the module image shown in content containers module section.
     * By default the main module image url is returned.
     * 
     * @param ContentContainerActiveRecord $container
     * @return string the url to the image
     */
    public function getContentContainerImage(ContentContainerActiveRecord $container)
    {
        return $this->getImage();
    }

    /**
     * Returns the url to configure this module in a content container
     * 
     * @param ContentContainerActiveRecord $container
     * @return string the config url
     */
    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        return "";
    }

    /**
     * Enables this module on the given content container
     * Override this method e.g. to set default settings.
     * 
     * @param ContentContainerActiveRecord $container
     */
    public function enableContentContainer(ContentContainerActiveRecord $container)
    {
        
    }

    /**
     * Disables module on given content container
     * Override this method to cleanup created data in content container context.
     * 
     * ~~~
     * public function disableContentContainer(ContentContainerActiveRecord $container)
     * {
     *      parent::disableContentContainer($container);
     *      foreach (MyContent::find()->contentContainer($container)->all() as $content) {
     *          $content->delete();
     *      }
     * }
     * ~~~
     * 
     * @param ContentContainerActiveRecord $container the content container
     */
    public function disableContentContainer(ContentContainerActiveRecord $container)
    {
        $this->settings->contentContainer($container)->deleteAll();
    }

    /**
     * Returns an array of all content containers where this module is enabled.
     * 
     * @param string $containerClass optional filter to specific container class
     * @return array of content container instances
     */
    public function getEnabledContentContainers($containerClass = "")
    {
        return [];
    }

}
