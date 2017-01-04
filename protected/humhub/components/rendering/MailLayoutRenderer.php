<?php

namespace humhub\components\rendering;

use Yii;

/**
 * Description of MailLayoutRenderer
 *
 * @author buddha
 */
class MailLayoutRenderer extends LayoutRenderer
{

    /**
     * Layout for text rendering.
     * @var type 
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
            'layout' => $this->getTextLayout($viewable)
        ]);

        $params['content'] = $viewable->text();

        return strip_tags($textRenderer->render($viewable, $params));
    }

    public function getTextLayout(Viewable $viewable)
    {
        return $this->textLayout;
    }

}
