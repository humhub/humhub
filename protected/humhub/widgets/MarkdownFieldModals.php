<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 18.07.2017
 * Time: 15:11
 */

namespace humhub\widgets;


use humhub\components\Widget;

class MarkdownFieldModals extends Widget
{
    public function run() {
        return $this->render('markdownFieldModals');
    }

}