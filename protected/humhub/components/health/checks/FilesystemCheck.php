<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\health\checks;

use humhub\components\health\HealthCheck;
use humhub\components\health\HealthResult;
use humhub\components\health\HealthStatus;
use Yii;

/**
 * Whether directories are writable for PHP.
 *
 * @since 1.20
 */
class FilesystemCheck extends HealthCheck
{
    /**
     * @var string[] paths or aliases of directories that must exist and be writable
     */
    public array $paths = [];

    /**
     * @var string[] paths or aliases of directories created at runtime on first use: a missing one counts as
     * writable when its parent directory is
     */
    public array $createdOnUse = [];

    public HealthStatus $failStatus = HealthStatus::Failure;

    public function run(): HealthResult
    {
        $notWritable = [];
        foreach ([...$this->paths, ...$this->createdOnUse] as $i => $path) {
            $path = Yii::getAlias($path, false) ?: $path;
            if (!$this->isWritable($path, $i >= count($this->paths))) {
                $notWritable[] = $path;
            }
        }

        if ($notWritable === []) {
            return HealthResult::ok();
        }

        return new HealthResult($this->failStatus, 'Not writable: ' . implode(', ', $notWritable), ['notWritable' => $notWritable]);
    }

    private function isWritable(string $path, bool $createdOnUse): bool
    {
        if (is_dir($path)) {
            return is_writable($path);
        }

        return $createdOnUse && !file_exists($path) && is_dir(dirname($path)) && is_writable(dirname($path));
    }
}
