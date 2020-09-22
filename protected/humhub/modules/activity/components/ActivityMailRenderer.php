<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use Yii;
use humhub\components\rendering\MailLayoutRenderer;

/**
 * MailRenderer for Activity models
 *
 * @since 1.2
 * @author buddha
 */
class ActivityMailRenderer extends MailLayoutRenderer
{

    /**
     * @inheritdoc
     */
    public $subPath = 'mail';

    /**
     * @inheritdoc
     */
    public $layout = '@activity/views/layouts/mail.php';

    /**
     * @inheritdoc
     */
    public $textLayout = '@activity/views/layouts/mail_plaintext.php';

}
