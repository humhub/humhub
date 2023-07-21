<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Space;
use Yii;

/**
 * SpaceDirectoryState shows status like "Archived" for spaces cards
 *
 * @since 1.16
 */
class SpaceDirectoryState extends Widget
{

    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->space->isArchived()) {
            return $this->render('SpaceDirectoryState', [
                'class' => 'label label-primary',
                'text' => Yii::t('SpaceModule.base', 'Archived'),
            ]);
        }
    }

}
