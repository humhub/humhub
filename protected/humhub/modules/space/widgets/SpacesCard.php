<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\space\models\Space;

/**
 * SpacesCard shows a space on spaces directory
 * 
 * @since 1.9
 * @author Luke
 */
class SpacesCard extends Widget
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
        return $this->render('spacesCard', [
            'space' => $this->space
        ]);
    }

}
