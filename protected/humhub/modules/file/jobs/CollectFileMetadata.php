<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\jobs;

use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\libs\MediaHelper;
use humhub\modules\file\models\File;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use Yii;
use yii\queue\RetryableJobInterface;

/**
 * Collects the metadata of files stored before it was collected on upload: the dimensions of images and the
 * size and duration of videos and audio files. Enqueued by a
 * migration, `php yii file/collect-metadata` runs the same.
 *
 * @since 1.20
 */
class CollectFileMetadata extends LongRunningActiveJob implements ExclusiveJobInterface, RetryableJobInterface
{
    private const BATCH_SIZE = 100;

    /**
     * Attempts of a run that fails or exceeds its TTR: each one resumes, as it skips the files that have their metadata
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * @inheritdoc
     */
    public function getExclusiveJobId()
    {
        return 'file.collect-metadata';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        static::collect();
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return Yii::$app->getModule('queue')->longRunningJobTtr;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < self::MAX_ATTEMPTS;
    }

    /**
     * @param callable|null $onFile called with each file looked at
     * @return int the number of files looked at
     */
    public static function collect(?callable $onFile = null): int
    {
        $count = 0;
        $lastId = 0;

        do {
            // Batches by id: no result set of the whole table in memory, and a file that can't be read isn't retried
            $files = File::find()
                ->where(['>', 'id', $lastId])
                // The serialized keys: a value mentioning "width" doesn't count
                ->andWhere([
                    'or',
                    ['and', ['mime_type' => ImageHelper::DIMENSION_MIME_TYPES], self::lacks('width')],
                    [
                        'and',
                        ['or', ['like', 'mime_type', 'video/%', false], ['like', 'mime_type', 'audio/%', false]],
                        self::lacks('duration'),
                    ],
                ])
                ->orderBy(['id' => SORT_ASC])
                ->limit(self::BATCH_SIZE)
                ->all();

            foreach ($files as $file) {
                // Without stored content (an orphaned record) there is nothing to read
                if ($file->store->has()) {
                    if (in_array($file->mime_type, ImageHelper::DIMENSION_MIME_TYPES, true)) {
                        ImageHelper::collectDimensions($file);
                    } else {
                        MediaHelper::collectMediaInfo($file, true);
                    }
                }
                $lastId = $file->id;
                $count++;

                if ($onFile !== null) {
                    $onFile($file);
                }
            }
        } while (count($files) === self::BATCH_SIZE);

        return $count;
    }

    /**
     * @return array Condition: the metadata has no serialized key of that name
     */
    private static function lacks(string $key): array
    {
        return ['or', ['metadata' => null], ['not like', 'metadata', 's:' . strlen($key) . ':"' . $key . '";']];
    }
}
