<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\DAV\TemporaryFileFilterPlugin;
use yii\helpers\FileHelper;

/**
 * Keeps the files clients write for themselves away from the modules: Finder's `._*` and
 * `.DS_Store`, Windows' `desktop.ini` and `Thumbs.db`, editor swap files, and in addition the
 * owner files of Microsoft Office (`~$*`) and the lock files of LibreOffice (`.~lock.*#`).
 * They are kept in a folder of the user for a while (see `Module::$tempFileLifetime`), so
 * the client finds them again, and never become content.
 *
 * The folder is per user: the path of such a file names a space, not a user, and Sabre names
 * the file after its path.
 *
 * @since 1.20
 */
class TemporaryFilePlugin extends TemporaryFileFilterPlugin
{
    public function __construct(string $dataDir)
    {
        FileHelper::createDirectory($dataDir);
        parent::__construct($dataDir);

        $this->temporaryFilePatterns[] = '/^~\$/';
        $this->temporaryFilePatterns[] = '/^\.~lock\..*#$/';
    }
}
