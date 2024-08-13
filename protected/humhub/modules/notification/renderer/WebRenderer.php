<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\renderer;

/**
 * The WebTargetRenderer is used to render Notifications for the WebTarget.
 * 
 * @see \humhub\modules\notification\targets\WebTarget
 * @author buddha
 */
class WebRenderer extends \humhub\components\rendering\DefaultViewPathRenderer
{

    /**
     * @inheritdoc
     */
    public $defaultView = '@notification/views/default.php';

    /**
     * @inheritdoc
     */
    public $defaultViewPath = '@notification/views';

}
