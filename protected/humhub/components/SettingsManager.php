<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use humhub\libs\BaseSettingsManager;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerSettingsManager;

/**
 * SettingsManager application component
 *
 * @since 1.1
 * @author Luke
 */
class SettingsManager extends BaseSettingsManager
{

    /**
     * @var ContentContainerSettingsManager[] already loaded content container settings managers
     */
    protected $contentContainers = [];

    /**
     * Returns content container
     *
     * @param ContentContainerActiveRecord $container
     * @return ContentContainerSettingsManager
     */
    public function contentContainer(ContentContainerActiveRecord $container)
    {
        if (isset($this->contentContainers[$container->contentcontainer_id])) {
            return $this->contentContainers[$container->contentcontainer_id];
        }

        $this->contentContainers[$container->contentcontainer_id] = new ContentContainerSettingsManager([
            'moduleId' => $this->moduleId,
            'contentContainer' => $container,
        ]);

        return $this->contentContainers[$container->contentcontainer_id];
    }

    /**
     * Returns ContentContainerSettingsManager for the given $user or current logged in user
     * @return ContentContainerSettingsManager
     */
    public function user($user = null)
    {
        if(!$user) {
            $user = Yii::$app->user->getIdentity();
        }

        return $this->contentContainer($user);
    }

    /**
     * Returns ContentContainerSettingsManager for the given $space or current controller space
     * @return ContentContainerSettingsManager
     */
    public function space($space = null)
    {
        if ($space != null) {
            return $this->contentContainer($space);
        } elseif (Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController) {
            if (Yii::$app->controller->contentContainer instanceof \humhub\modules\space\models\Space) {
                return $this->contentContainer(Yii::$app->controller->contentContainer);
            }
        }
    }

    /**
     * Indicates this setting is fixed in configuration file and cannot be
     * changed at runtime.
     *
     * @param string $name
     * @return boolean
     */
    public function isFixed($name)
    {
        return isset(Yii::$app->params['fixed-settings'][$this->moduleId][$name]);
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        if ($this->isFixed($name)) {
            return Yii::$app->params['fixed-settings'][$this->moduleId][$name];
        }

        return parent::get($name, $default);
    }

}
