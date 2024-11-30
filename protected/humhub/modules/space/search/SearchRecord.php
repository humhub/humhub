<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\search;

use humhub\interfaces\MetaSearchResultInterface;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;

/**
 * Search Record for Space
 *
 * @author luke
 * @since 1.16
 */
class SearchRecord implements MetaSearchResultInterface
{
    public ?Space $space = null;

    public function __construct(Space $space)
    {
        $this->space = $space;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): string
    {
        return Image::widget([
            'space' => $this->space,
            'width' => 36,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->space->getDisplayName();
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->space->getDisplayNameSub();
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->space->getUrl();
    }
}
