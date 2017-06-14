<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.06.2017
 * Time: 14:18
 */

namespace humhub\modules\content\tests\codeception\unit;


use humhub\libs\BasePermission;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\post\models\Post;

class TestContentManagePermission extends ManageContent
{

}