<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

use humhub\libs\Helpers;

/**
 * MailContentContainerInfoBox for rendering a simple info box with contentcotnainer image,name and description.
 *
 * @author buddha
 * @since 1.2
 */
class MailContentContainerInfoBox extends \yii\base\Widget
{
    /**
     * @var \humhub\modules\content\components\ContentContainerActiveRecord
     */
    public $container;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->container instanceof \humhub\modules\space\models\Space) {
            return $this->render('mailContentContainerInfoBox', [
                        'container' => $this->container,
                        'url' => $this->container->createUrl('/space/space', [], true),
                        'description' => Helpers::trimText($this->container->description, 60)
                        
            ]);
        } else if ($this->container instanceof \humhub\modules\user\models\User) {
            return $this->render('mailContentContainerInfoBox', [
                        'container' => $this->container,
                        'url' => $this->container->createUrl('/user/profile', [], true),
                        'description' => Helpers::trimText($this->container->profile->title, 60)
                        
            ]);
        }
    }
}
