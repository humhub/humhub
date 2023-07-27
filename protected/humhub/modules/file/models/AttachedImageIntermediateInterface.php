<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use yii\db\IntegrityException;

interface AttachedImageIntermediateInterface
{
    /**
     * @return AttachedImageOwnerInterface|null
     * @throws IntegrityException
     * @since 1.4
     */
    public function findImageOwner(): ?AttachedImageOwnerInterface;

    /**
     * @return AttachedImageIntermediateInterface|AttachedImageOwnerInterface|string
     */
    public static function getImageOwnerClass(): string;

}
