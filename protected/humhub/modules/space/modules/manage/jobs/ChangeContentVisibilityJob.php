<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\queue\ActiveJob;

class ChangeContentVisibilityJob extends ActiveJob
{
    public int $contentContainerId;

    public int $visibility;

    public function run()
    {
        Content::updateAll(
            ['visibility' => $this->visibility],
            ['contentcontainer_id' => $this->contentContainerId]
        );
    }
}
