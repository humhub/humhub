<?php

namespace humhub\modules\notification\components;

/**
 * The MailTargetRenderer is used to render Notifications for the MailNotificationTarget.
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
 * @author buddha
 */
class MailTargetRenderer extends \humhub\components\rendering\MailRenderer
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
