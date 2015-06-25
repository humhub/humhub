<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
namespace humhub\core\post\widgets;

/**
 * Description of Wall
 *
 * @author luke
 */
class Wall extends \yii\base\Widget
{

    public $post;

    public function run()
    {
        return $this->render('wall', array('post' => $this->post));
    }

}
