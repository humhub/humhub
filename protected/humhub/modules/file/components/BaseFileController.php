<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\actions\UploadAction;
use humhub\modules\file\libs\FileControllerInterface;
use humhub\modules\file\libs\FileControllerTrait;

/**
 * BaseFileController provides handles file up- and downloads and deletions
 *
 * @since 1.15
 *
 * @property-read array[][] $accessRules
 */
class BaseFileController extends Controller implements FileControllerInterface
{
    use FileControllerTrait;

    // protected properties
    protected static ?string $downloadActionClass = DownloadAction::class;
    protected static ?string $uploadActionClass   = UploadAction::class;

    /**
     * NOTE: Child controller MUST define access rules!
     *
     * @inheritdoc
     * @return \array[][]
     */
    public function getAccessRules(): array
    {
        return [
            [ControllerAccess::RULE_ACCESS_DENIED],
        ];
    }
}
