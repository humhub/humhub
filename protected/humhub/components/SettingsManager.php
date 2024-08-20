<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\libs\BaseSettingsManager;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Throwable;
use Yii;

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
    protected array $contentContainers = [];

    /**
     * Returns content container
     *
     * @param ContentContainerActiveRecord $container
     * @return ContentContainerSettingsManager
     */
    public function contentContainer(ContentContainerActiveRecord $container): ContentContainerSettingsManager
    {
        if ($contentContainers = $this->contentContainers[$container->contentcontainer_id] ?? null) {
            return $contentContainers;
        }

        return $this->contentContainers[$container->contentcontainer_id] = new ContentContainerSettingsManager([
            'moduleId' => $this->moduleId,
            'contentContainer' => $container,
        ]);
    }


    /**
     * Clears runtime cached content container settings
     *
     * @param ContentContainerActiveRecord|null $container if null all content containers will be flushed
     *
     * @noinspection PhpUnused
     */
    public function flushContentContainer(ContentContainerActiveRecord $container = null)
    {
        if ($container === null) {
            $containers = $this->contentContainers;
            $this->contentContainers = [];
        } else {
            // need to create an instance, if it does not already exist, in order to then flush the underlying cache
            $containers = [$this->contentContainer($container)] ?? null;
            unset($this->contentContainers[$container->contentcontainer_id]);
        }

        array_walk($containers, static fn(ContentContainerSettingsManager $container) => $container->invalidateCache());
    }

    /**
     * Returns ContentContainerSettingsManager for the given $user or current logged-in user
     *
     * @param User|null $user
     *
     * @return ContentContainerSettingsManager|null
     * @throws Throwable
     */
    public function user(?User $user = null): ?ContentContainerSettingsManager
    {
        if ($user === null) {
            $user = Yii::$app->user->getIdentity();
            if (!$user instanceof User) {
                return null;
            }
        }

        return $this->contentContainer($user);
    }

    /**
     * Returns ContentContainerSettingsManager for the given $space or current controller space
     *
     * @param Space|null $space
     *
     * @return ContentContainerSettingsManager
     */
    public function space(?Space $space = null): ?ContentContainerSettingsManager
    {
        if ($space !== null) {
            return $this->contentContainer($space);
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if (
            ($controller = Yii::$app->controller) instanceof ContentContainerController
            && ($space = $controller->contentContainer) instanceof Space
        ) {
            return $this->contentContainer($space);
        }

        return null;
    }

    /**
     * Indicates this setting is fixed in configuration file and cannot be
     * changed at runtime.
     *
     * @param string $name
     * @return bool
     */
    public function isFixed(string $name): bool
    {
        return $this->getFixed($name) !== null;
    }

    /**
     * Get the fixed setting value from configuration file.
     *
     * @param string $name
     * @return mixed
     */
    public function getFixed(string $name)
    {
        if (!isset(Yii::$app->params['fixed-settings'][$this->moduleId][$name])) {
            return null;
        }

        $value = Yii::$app->params['fixed-settings'][$this->moduleId][$name];

        if (is_callable($value)) {
            return call_user_func($value, $this);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function get(string $name, $default = null)
    {
        $fixedValue = $this->getFixed($name);

        return $fixedValue ?? parent::get($name, $default);
    }
}
