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
        if (isset($this->contentContainers[$container->id])) {
            return $this->contentContainers[$container->id];
        }

        $this->contentContainers[$container->id] = new ContentContainerSettingsManager([
            'moduleId' => $this->moduleId,
            'contentContainer' => $container,
        ]);

        return $this->contentContainers[$container->id];
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
        if (isset(Yii::$app->params['fixed-settings'][$this->moduleId][$name])) {
            return true;
        }

        return false;
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
