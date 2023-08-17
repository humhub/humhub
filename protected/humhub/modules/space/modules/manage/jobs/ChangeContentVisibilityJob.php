<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\queue\LongRunningActiveJob;

class ChangeContentVisibilityJob extends LongRunningActiveJob
{
    public int $contentContainerId;

    public int $visibility;

    public function run()
    {
        /** @var Content[] $contents */
        $contents = Content::find()
            ->where(['contentcontainer_id' => $this->contentContainerId])
            ->each();

        foreach ($contents as $content) {
            $content->visibility = $this->visibility;
            $content->save(false);
        }
    }
}
