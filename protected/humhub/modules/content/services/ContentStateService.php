<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\services;

use humhub\components\StateServiceDeletableTrait;
use humhub\components\StateServiceDraftableTrait;
use humhub\components\StateServicePublishableTrait;
use humhub\components\StateServiceSchedulableTrait;
use humhub\modules\content\models\Content;
use humhub\services\StateService;

/**
 * This service is used to extend Content record for state features
 *
 * @since 1.14
 *
 * @property Content $content Deprecated since 1.16; use static::$record
 * @property Content $record
 */
class ContentStateService extends StateService
{
    use StateServiceDeletableTrait;
    use StateServicePublishableTrait;
    use StateServiceDraftableTrait;
    use StateServiceSchedulableTrait;

    /**
     * @inheritdoc
     */
    public function initStates(): self
    {
        $this->allowState(Content::STATE_PUBLISHED);
        $this->allowState(Content::STATE_DRAFT);
        $this->allowState(Content::STATE_SCHEDULED);
        $this->allowState(Content::STATE_DELETED);

        return parent::initStates();
    }

    /**
     * @param Content $content
     *
     * @return $this
     * @deprecated since v1.16; use static::setRecord()
     * @see static::setRecord()
     */
    public function setContent(Content $content): ContentStateService
    {
        return $this->setRecord($content);
    }

    /**
     * @return Content
     * @deprecated since v1.16; use static::getRecord()
     * @see static::getRecord()
     */
    public function getContent(): Content
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getRecord();
    }
}
