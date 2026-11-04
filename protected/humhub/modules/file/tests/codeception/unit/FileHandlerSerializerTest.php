<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\file;

use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\file\handler\UploadImageFileHandler;
use humhub\modules\file\serializers\FileHandlerSerializer;
use humhub\widgets\Icon;
use tests\codeception\_support\HumHubDbTestCase;

class FileHandlerSerializerTest extends HumHubDbTestCase
{
    public function testAddHandlersAreTheUsersImportAndCreateHandlers()
    {
        $this->becomeUser('User2');

        $actions = array_column(array_column(FileHandlerSerializer::addHandlers(), 'attributes'), 'data-action-click');

        // The platform's own "upload this type" entries are always among them
        $this->assertContains('file.uploadByType', $actions);
    }

    public function testUploadHandlerAsData()
    {
        $data = FileHandlerSerializer::handler(new UploadImageFileHandler());

        $this->assertSame('camera', $data['icon']);
        $this->assertStringNotContainsString('<', $data['label']);
        $this->assertNotSame('', $data['label']);
        $this->assertSame('file.uploadByType', $data['attributes']['data-action-click']);
        $this->assertSame('{"type":"image/*"}', $data['attributes']['data-action-params']);
    }

    public function testModuleHandlerLabelReducedToTextAndLegacyIconResolved()
    {
        $handler = new class extends BaseFileHandler {
            public string $icon = 'plus-square';

            public function getLinkAttributes()
            {
                return [
                    'label' => Icon::get($this->icon) . 'Create file <small>(Text, Log, XML)</small>',
                    'class' => 'ignored',
                    'url' => '/create',
                    'data-action-click' => 'ui.modal.load',
                    'data-modal-close' => '',
                ];
            }
        };

        $data = FileHandlerSerializer::handler($handler);

        $this->assertSame('Create file (Text, Log, XML)', $data['label']);
        $this->assertNotSame('plus-square', $data['icon']);
        $this->assertSame([
            'data-action-click' => 'ui.modal.load',
            'data-modal-close' => '',
            'href' => '/create',
        ], $data['attributes']);
    }

    public function testHandlerWithoutIcon()
    {
        $handler = new class extends BaseFileHandler {
            public function getLinkAttributes()
            {
                return ['label' => 'Link from Documenta', 'data-action-click' => 'ui.modal.load'];
            }
        };

        $this->assertNull(FileHandlerSerializer::handler($handler)['icon']);
    }
}
