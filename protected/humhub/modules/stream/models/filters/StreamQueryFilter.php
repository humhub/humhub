<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;


use humhub\modules\stream\models\ContentContainerStreamQuery;
use humhub\modules\stream\models\StreamQuery;
use humhub\modules\ui\filter\models\QueryFilter;

abstract class StreamQueryFilter extends QueryFilter
{
    /**
     * @var StreamQuery | ContentContainerStreamQuery
     */
    public $streamQuery;

    /**
     * @inheritDoc
     */
    public $autoLoad = self::AUTO_LOAD_GET;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isLoaded && !parent::validate()) {
            $this->streamQuery->addErrors($this->getErrors());
        }
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        return $this->formName ?: 'StreamQuery';
    }

}
