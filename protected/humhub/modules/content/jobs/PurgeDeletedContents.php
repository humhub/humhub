<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\queue\ActiveJob;

class PurgeDeletedContents extends ActiveJob
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach (Content::findAll(['content.state' => Content::STATE_DELETED]) as $content) {
            $content->delete();
        }
    }

}
