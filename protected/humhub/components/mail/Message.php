<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\mail;

use humhub\libs\GoAppService;
use yii\symfonymailer\Message as BaseMessage;

/**
 * Message
 *
 * @since 1.2
 * @author Luke
 */
class Message extends BaseMessage
{
    private ?GoAppService $goAppService = null;

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html): BaseMessage
    {
        return BaseMessage::setHtmlBody($this->getGoAppService()->processLinks($html));
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text): BaseMessage
    {
        return BaseMessage::setTextBody($this->getGoAppService()->processUrls($text));
    }

    private function getGoAppService(): GoAppService
    {
        return $this->goAppService ?? new GoAppService();
    }
}
