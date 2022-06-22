<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;

use humhub\libs\Html;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;

/**
 * This parser can be used to convert HumHub richtext directly to email html in order to view images from email inbox where
 * user is not logged in so access is restricted.
 *
 * @since 1.8.2
 */
class RichTextToEmailHtmlConverter extends RichTextToHtmlConverter
{

    /**
     * Option key used for rendering images as HTML tag with token
     * for receiver user to allow see image from email readers
     */
    const OPTION_RECEIVER_USER = 'receiver';

    /**
     * @inheritdoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock): string
    {
        return parent::renderPlainImage($this->tokenizeBlock($linkBlock));
    }

    /**
     * Append a param 'token' to the URL in order to allow see it when user is not logged in e.g. from email inbox
     *
     * @param LinkParserBlock $linkBlock
     * @return LinkParserBlock
     */
    protected function tokenizeBlock(LinkParserBlock $linkBlock): LinkParserBlock
    {
        /* @var User $receiver */
        $receiver = $this->getOption('receiver');

        if (!($receiver && $linkBlock->getUrl() && $linkBlock->getFileId())) {
            return $linkBlock;
        }

        $token = '';
        if ($linkBlock->getFileId() !== null) {
            $file = File::findOne(['id' => $linkBlock->getFileId()]);
            if ($file !== null) {
                $token = DownloadAction::generateDownloadToken($file, $receiver);
            }
        }

        $linkBlock->setUrl($linkBlock->getUrl() . (strpos($linkBlock->getUrl(), '?') === false ? '?' : '&') . 'token=' . $token);

        return $linkBlock;
    }

    /**
     * @inheritdoc
     */
    protected function parseLinkOrImage($markdown)
    {
        $parsedUrl = parent::parseLinkOrImage($markdown);

        if (is_array($parsedUrl) && isset($parsedUrl[0])) {
            $parsedUrl[0] = ' ' . $parsedUrl[0] . ' ';
        }

        return $parsedUrl;
    }

    /**
     * @inheritdoc
     */
    protected function renderAutoUrl($block)
    {
        return Html::a($block[1], $block[1], ['target' => '_blank']);
    }

    /**
     * @inheritdoc
     */
    protected function renderParagraph($block)
    {
        return '<p>' . nl2br($this->renderAbsy($block['content'])) . "</p>\n";
    }
}
