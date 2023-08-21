<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\content\services;

use humhub\modules\content\components\ContentActiveRecord;

/**
 * This service is used to extend Content record for state features
 * @since 1.14
 *
 * @property ContentActiveRecord $record
 */
class ActiveContentStateService extends ContentStateService
{
}
