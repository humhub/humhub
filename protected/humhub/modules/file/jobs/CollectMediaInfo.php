<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\jobs;

use humhub\modules\file\libs\MediaHelper;
use humhub\modules\file\models\File;
use humhub\modules\queue\ActiveJob;

/**
 * Collects the size and duration of a video or audio file that is not on a local path, which an upload request doesn't
 * read.
 *
 * @since 1.20
 */
class CollectMediaInfo extends ActiveJob
{
    public int $fileId;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $file = File::findOne(['id' => $this->fileId]);

        if ($file !== null && $file->store->has()) {
            MediaHelper::collectMediaInfo($file, true);
        }
    }
}
