<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\renderer;

/**
 * The MailTargetRenderer is used to render Notifications for the MailTarget.
 * 
 * A BaseNotification can overwrite the default view and layout by setting a specific $viewName and
 * defining the following files:
 * 
 * Overwrite default html view for this notification:
 * @module/views/notification/mail/viewname.php
 * 
 * Overwrite default mail layout for this notification:
 * @module/views/layouts/notification/mail/viewname.php
 * 
 * Overwrite default mail text layout for this notification:
 * @module/views/layouts/notification/mail/plaintext/viewname.php
 *
 * @see \humhub\modules\notification\targets\MailTarget
 * @author buddha
 */
class MailRenderer extends \humhub\components\rendering\MailRenderer
{

    /**
     * @inheritdoc
     */
    public $defaultView = '@notification/views/mails/default.php';

    /**
     * @inheritdoc
     */
    public $defaultViewPath = '@notification/views/mails';

    /**
     * @inheritdoc
     */
    public $defaultTextView = '@notification/views/mails/plaintext/default.php';

    /**
     * @inheritdoc
     */
    public $defaultTextViewPath = '@notification/views/mails/plaintext';

}
