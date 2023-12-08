<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace tests\codeception\_support;

use humhub\components\mail\Mailer;
use humhub\interfaces\MailerInterface;

/**
 * @since 1.15
 */
class TestMailer extends \Codeception\Lib\Connector\Yii2\TestMailer implements MailerInterface
{
    public function compose($view = null, array $params = [])
    {
        $message = parent::compose($view, $params);

        return Mailer::ensureHumHubDefaultFromValues($message);
    }
}
