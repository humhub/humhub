<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use DateTime;
use DateTimeZone;
use humhub\modules\content\models\Content;
use humhub\modules\queue\ActiveJob;

class PublishScheduledContents extends ActiveJob
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));

        /* @var Content[] $contents*/
        $contents = Content::find()
            ->where(['state' => Content::STATE_SCHEDULED])
            ->andWhere(['<=', 'scheduled_at', $now->format('Y-m-d H:i:s')])
            ->all();

        foreach ($contents as $content) {
            $content->setState(Content::STATE_PUBLISHED);
            $content->save();
        }
    }

}
