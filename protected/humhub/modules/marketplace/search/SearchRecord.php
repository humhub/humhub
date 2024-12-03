<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\search;

use humhub\interfaces\MetaSearchResultInterface;
use humhub\libs\Html;
use humhub\modules\marketplace\models\Module as ModelModule;

/**
 * Search Record for Marketplace Module
 *
 * @author luke
 * @since 1.16
 */
class SearchRecord implements MetaSearchResultInterface
{
    public ?ModelModule $module = null;

    public function __construct(ModelModule $module)
    {
        $this->module = $module;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): string
    {
        return Html::img($this->module->image, [
            'class' => 'media-object img-rounded',
            'data-src' => 'holder.js/36x36',
            'alt' => '36x36',
            'style' => 'width:36px;height:36px',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->module->name;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->module->description;
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->module->marketplaceUrl;
    }
}
