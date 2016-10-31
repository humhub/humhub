<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file;

/**
 * File Module
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @see components\StorageManagerInterface
     * @var string storage manager class for files
     */
    public $storageManagerClass = '\humhub\modules\file\components\StorageManager';

    /**
     * @var array mime types to show inline instead of download
     */
    public $inlineMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'image/gif',
        'image/png',
        'image/jpeg'
    ];

}
