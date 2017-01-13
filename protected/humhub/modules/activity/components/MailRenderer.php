<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use humhub\components\rendering\MailLayoutRenderer;

/**
 * MailRenderer for Activity models
 *
 * @since 1.2
 * @author buddha
 */
class MailRenderer extends MailLayoutRenderer
{

    /**
     * @inheritdoc
     */
    public $layout = '@activity/views/mails/activityLayout.php';

    /**
     * @inheritdoc
     */
    public $textLayout = "@activity/views/mails/plaintext/activityLayout.php";

}
