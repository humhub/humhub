<?php


namespace humhub\modules\content\widgets\stream;


use Exception;
use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;

/**
 * Class WallStreamModuleEntryWidget
 * @package humhub\modules\content\widgets\stream
 * @since 1.7
 */
abstract class WallStreamModuleEntryWidget extends WallStreamEntryWidget
{
    const DEFAULT_ICON = 'comment';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->renderOptions->enableSubHeadlineAuthor();
        $this->renderOptions->enableContainerInformationInTitle(false);
    }

    /**
     * @return string the title part of this wall entry used in the header section. Note, the return value will NOT be encoded.
     * Therefore you can pass in HTML as links. By default the [[getTitle()]] within a permalink to the content model is returned.
     */
    protected function renderTitle()
    {
        return Html::a(Html::encode($this->getTitle()), $this->getPermaLink());
    }

    /**
     * @return string by default, renders the icon provided by [[getIcon()]]
     * @throws Exception
     */
    protected function renderHeadImage()
    {
        return Html::a( $this->renderIconImage(), $this->getPermaLink());
    }

    /**
     * Renders the icon used in the widget header provided by [[getIcon()]].
     * @return string
     * @throws Exception
     */
    private function renderIconImage()
    {
        $icon =  Icon::get($this->getIcon(), ['fixedWidth' => true]);
        return $icon ? $icon->asString() : '';
    }

    /**
     * Returns an icon name e.g. 'calendar', 'tasks'.
     *
     * By default the [[ContentActiveRecord::getIcon()]] is used to determine
     * the icon of this stream entry.
     *
     * Subclasses may want to overwrite this
     *
     * @return string icon name e.g. 'calendar', 'tasks'
     * @see Icon
     */
    protected function getIcon()
    {
        return $this->model->getIcon() ?? static::DEFAULT_ICON;
    }

    /**
     * @return string a non encoded plain text title (no html allowed) used in the header of the widget
     */
    protected abstract function getTitle();
}
