<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\mails;

use humhub\modules\space\models\Space;

/**
 * MailContentContainerImage renders the profile image of a ContentContainer.
 *
 * @author buddha
 * @since 1.2
 */
class MailContentContainerImage extends \yii\base\Widget
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
        $url = ($this->container instanceof Space) 
                ? $this->container->createUrl('/space/space', [], true)
                : $this->container->createUrl('/user/profile', [], true);
        
        return $this->render('mailContentContainerImage', [
                    'container' => $this->container,
                    'url' => $url,
        ]);
    }

}

?>