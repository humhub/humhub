<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\mail;

use humhub\services\MailLinkService;

/**
 * Message
 *
 * @since 1.2
 * @author Luke
 */
class Message extends \yii\symfonymailer\Message
{
    private ?MailLinkService $linkService = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->linkService = new MailLinkService();
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html): Message
    {
        return parent::setHtmlBody($this->linkService->processLinks($html));
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text): Message
    {
        return parent::setTextBody($this->linkService->processUrls($text));
    }
}
