<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseContentActivity;

/**
 * A content activity overriding every per-output method deprecated in 1.20, as a 1.19 module does.
 */
class TestLegacyContentActivity extends BaseContentActivity
{
    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} {$params['content']} [{$params['channel']}]";
    }

    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), ['channel' => 'web']);
    }

    protected function getMessageParamsMailText(): array
    {
        return array_merge(parent::getMessageParamsMailText(), ['channel' => 'text']);
    }

    protected function getMessageParamsMailHtml(): array
    {
        return array_merge(parent::getMessageParamsMailHtml(), ['channel' => 'html']);
    }
}
