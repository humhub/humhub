<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use Yii;
use humhub\components\rendering\Viewable;

/**
 * The ActivityWebRenderer is used to render BaseActivity instances for the Activity Stream.
 *
 * A BaseActivity can overwrite the default view and layout by setting a specific $viewName and
 * defining the following files:
 *
 * Overwrite default view for this Activity:
 * @module/activities/views/[viewname].php
 *
 * Overwrite default layout for this Activity:
 * @module/activities/views/layout/[viewname].php
 *
 * @author buddha
 * @since 1.2
 */
class ActivityWebRenderer extends \humhub\components\rendering\LayoutRenderer
{

    /**
     * @var string default view path
     */
    public $defaultViewPath = '@activity/views';

    /**
     * @var string default layout
     */
    public $defaultLayout = '@activity/views/layouts/web.php';

    /**
     * @inheritdoc
     */
    public function render(Viewable $viewable, $params = [])
    {
        if (!$this->getViewFile($viewable)) {
            $params['content'] = $viewable->html();
        }

        return parent::render($viewable, $params);
    }

    /**
     * Returns the view file for the given Viewable Notification.
     *
     * This function will search for the view file defined in the Viewable within the module/views/mail directory of
     * the viewable module.
     *
     * If the module view does not exist we search for the viewName within the default notification viewPath.
     *
     * If this view also does not exist we return the base notification view file.
     *
     * @param \humhub\modules\notification\components\Viewable $viewable
     * @return string view file of this notification
     */
    public function getViewFile(Viewable $viewable)
    {
        $viewFile = parent::getViewFile($viewable);

        if (!file_exists($viewFile)) {
            $viewFile = Yii::getAlias($this->defaultViewPath) . '/' . $this->suffix($viewable->getViewName());
        }

        if (!file_exists($viewFile)) {
            return null;
        }

        return $viewFile;
    }

    /**
     * Returns the layout for the given Notification Viewable.
     *
     * This function will search for a layout file under `@module/views/layouts/mail` with the view name defined
     * by $viewable.
     *
     * If this file does not exists the default layout will be returned.
     *
     * @param \humhub\modules\notification\components\Viewable $viewable
     * @return type
     */
    public function getLayout(Viewable $viewable)
    {
        $layout = $this->getViewPath($viewable) . '/layouts/' . $this->suffix($viewable->getViewName());

        if (!file_exists($layout)) {
            $layout = Yii::getAlias($this->defaultLayout);
        }

        return $layout;
    }

}

