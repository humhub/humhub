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
 * SpaceDirectoryCard shows a space on spaces directory
 * 
 * @since 1.9
 * @author Luke
 */
class SpaceDirectoryCard extends Widget
{

    /**
     * @var Space
     */
    public $space;

    /**
     * @var string HTML wrapper around card
     */
    public $template = '<div class="card card-space col-lg-3 col-md-4 col-sm-6 col-xs-12">{card}</div>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $card = $this->render('spaceDirectoryCard', [
            'space' => $this->space
        ]);

        return str_replace('{card}', $card, $this->template);
    }

}
