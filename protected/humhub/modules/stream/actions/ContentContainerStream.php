<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\stream\actions;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\stream\models\ContentContainerStreamQuery;
use yii\base\InvalidConfigException;

/**
 * ContentContainerStream is used to stream contentcontainers (space or users) content.
 *
 * Used to stream contents of a specific a content container.
 *
 * @since 0.11
 * @author luke
 */
class ContentContainerStream extends Stream
{

    /**
     * @inheritdoc
     */
    public $streamQueryClass = ContentContainerStreamQuery::class;

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function initQuery($options = [])
    {
        $options['container'] = $this->contentContainer;
        return parent::initQuery($options);
    }
}
