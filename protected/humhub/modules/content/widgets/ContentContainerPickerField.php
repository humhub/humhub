<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\space\models\Space;
use humhub\modules\user\assets\UserVueAsset;
use humhub\modules\user\models\User;
use humhub\widgets\form\BasePicker;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Mutliselect input field for selecting Space guids or current user Profile
 *
 * This widget has no default route, so the `url` param is required.
 *
 * @since 1.17.2
 */
class ContentContainerPickerField extends BasePicker
{
    /**
     * @inheritdoc
     * The 'guid' value is default for UserPickerField
     */
    public $itemKey = 'guid';

    /**
     * @inheritdoc
     */
    public $itemClass = ContentContainer::class;

    /**
     * @inheritdoc
     * Registers the user and space Vue bundles: their picker sources and image types.
     * @since 1.20
     */
    public function run()
    {
        UserVueAsset::register($this->getView());
        SpaceVueAsset::register($this->getView());

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    protected function getItemText($item)
    {
        return $this->getContainer($item)?->displayName ?? '';
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        $container = $this->getContainer($item);

        if ($container instanceof User) {
            return [
                'type' => 'user',
                'guid' => $container->guid,
                'displayName' => $container->displayName,
                'imageUrl' => $container->image->getUrl(),
            ];
        }

        if ($container instanceof Space) {
            return [
                'type' => 'space',
                'id' => $container->id,
                'name' => $container->name,
                'color' => $container->color,
                'imageUrl' => $container->image->exists() ? $container->image->getUrl() : null,
            ];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function getItemSubText($item): ?string
    {
        $container = $this->getContainer($item);

        if ($container instanceof User) {
            return $container->getDisplayNameSub() ?: null;
        }

        return $container instanceof Space ? ($container->description ?: null) : null;
    }

    /**
     * Whether this picker searches through its own endpoint and therefore stays on the legacy
     * search URL instead of a named source: $url is set, $defaultRoute differs from the one
     * $class declares, or a subclass of $class overrides getUrl().
     *
     * @param string $class the picker class declaring the default route and getSource()
     * @since 1.20
     */
    protected function hasOwnSearch(string $class): bool
    {
        if ($this->url) {
            return true;
        }

        if ($this->defaultRoute !== (new ReflectionProperty($class, 'defaultRoute'))->getDefaultValue()) {
            return true;
        }

        return is_subclass_of((new ReflectionMethod($this, 'getUrl'))->getDeclaringClass()->getName(), $class);
    }

    private function getContainer($item)
    {
        return $this->itemClass === ContentContainer::class ? $item->getPolymorphicRelation() : $item;
    }
}
