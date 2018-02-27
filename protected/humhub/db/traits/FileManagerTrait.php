<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\db\traits;

use humhub\modules\file\components\FileManager;

/**
 * Trait FileManagerTrait
 * @property FileManager $fileManager
 */
trait FileManagerTrait
{
    private $fileManagerTraitAttribute;

    /**
     * @return FileManager the file manager instance
     */
    public function getFileManager()
    {
        if ($this->fileManagerTraitAttribute === null) {
            $this->fileManagerTraitAttribute = new FileManager(['record' => $this,]);
        }

        return $this->fileManagerTraitAttribute;
    }
}
