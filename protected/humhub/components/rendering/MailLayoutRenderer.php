<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rendering;

/**
 * MailLayoutRenderer extends the LayoutRenderer with a renderText function.
 *
 * @author buddha
 * @since 1.2
 */
class MailLayoutRenderer extends LayoutRenderer
{

    public $subPath = 'mails';

    /**
     * @var string Layout file path
     */
    public $textLayout;

    /**
     * Used for rendering text mail content, by embedding the rendered view into
     * a $textLayout and removing all html elemtns.
     *
     * @param \humhub\components\rendering\Viewable $viewable
     * @return type
     */
    public function renderText(Viewable $viewable, $params = [])
    {
        $textRenderer = new LayoutRenderer([
            'subPath' => $this->subPath . '/plaintext',
            'parent' => $this->parent,
            'layout' => $this->getTextLayout($viewable)
        ]);

        // exclude the view only embed the viewable text to the textlayout.
        $params['content'] = $viewable->text();

        return strip_tags($textRenderer->render($viewable, $params));
    }

    /**
     * Returns the $textLayout for the given $viewable.
     *
     * @param \humhub\components\rendering\Viewable $viewable
     * @return type
     */
    public function getTextLayout(Viewable $viewable)
    {
        return $this->textLayout;
    }

}
