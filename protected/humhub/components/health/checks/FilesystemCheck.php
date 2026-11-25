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
     * @var string[] paths or aliases
     */
    public array $paths = [];

    /**
     * @var bool whether a missing directory counts as writable when its parent is (created on first use)
     */
    public bool $allowMissing = false;

    public HealthStatus $failStatus = HealthStatus::Failure;

    public function run(): HealthResult
    {
        $notWritable = [];
        foreach ($this->paths as $path) {
            $path = Yii::getAlias($path, false) ?: $path;
            if (!$this->isWritable($path)) {
                $notWritable[] = $path;
            }
        }

        if ($notWritable === []) {
            return HealthResult::ok();
        }

        return new HealthResult($this->failStatus, 'Not writable: ' . implode(', ', $notWritable), ['notWritable' => $notWritable]);
    }

    private function isWritable(string $path): bool
    {
        if (is_dir($path)) {
            return is_writable($path);
        }

        return $this->allowMissing && !file_exists($path) && is_writable(dirname($path));
    }
}
