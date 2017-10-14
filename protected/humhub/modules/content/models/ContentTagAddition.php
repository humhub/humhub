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
 * Date: 23.07.2017
 * Time: 21:38
 */

namespace humhub\modules\content\models;


use yii\db\ActiveRecord;

/**
 * Class ContentTagAddition
 *
 * @property integer $id
 * @perperty integer $tag_id
 *
 * @since 1.2.2
 * @author buddha
 */
class ContentTagAddition extends ActiveRecord
{

    public function setTag(ContentTag $tag)
    {
        $this->tag_id = $tag->id;
    }


}