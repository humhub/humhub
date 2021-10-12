<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 * MIME types.
 *
 * This file fixes MIME types which are not detected correctly by function finfo_file()
 * http://svn.apache.org/viewvc/httpd/httpd/trunk/docs/conf/mime.types?view=markup
 */

use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;

$baseMimeTypes = require Yii::getAlias(BaseFileHelper::$mimeMagicFile);

return ArrayHelper::merge($baseMimeTypes, [
    'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
]);