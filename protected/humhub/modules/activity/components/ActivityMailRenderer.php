<?php
namespace humhub\modules\activity\components;

use humhub\components\rendering\MailLayoutRenderer;

/**
 * Description of ActivityMailRenderer
 *
 * @author buddha
 */
class ActivityMailRenderer extends MailLayoutRenderer
{
    /**
     * @var string layout file path
     */
    public $layout = '@activity/views/layouts/mail.php';

    /**
     * @var string text layout file 
     */
    public $textLayout = "@activity/views/layouts/mail_plaintext.php";

}
