<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\components\Widget;

/**
 * SpaceTags lists all tags of the Space
 */
class SpaceTags extends Widget
{

    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritDoc
     */
    public function run()
    {
        return $this->render('spaceTags', ['space' => $this->space]);
    }

}

?>
