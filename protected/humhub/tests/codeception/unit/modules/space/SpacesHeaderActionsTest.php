<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\space;

use humhub\modules\space\widgets\SpacesHeaderActions;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * The actions of the Spaces page header as the `PageHeader` `actions` data (the
 * generic conversion is covered by `MenuEntriesDataTest`).
 *
 * @since 1.20
 */
class SpacesHeaderActionsTest extends HumHubDbTestCase
{
    /**
     * @inheritdoc
     */
    protected $fixtureConfig = ['default'];

    public function testCreateSpaceIsAModalAction()
    {
        $this->becomeUser('Admin');

        $actions = (new SpacesHeaderActions())->getEntriesData();

        $this->assertCount(1, $actions);
        $this->assertSame('create-space-button', $actions[0]['id']);
        $this->assertSame('plus', $actions[0]['icon']);
        $this->assertSame('Create Space', $actions[0]['label']);
        $this->assertStringContainsString('space%2Fcreate%2Fcreate', $actions[0]['url']);
        $this->assertTrue($actions[0]['modal']);
        $this->assertTrue($actions[0]['primary']);
        $this->assertArrayNotHasKey('htmlOptions', $actions[0], 'the modal attributes are the `modal` flag, nothing else is left');
    }

    public function testNoCreateSpaceWithoutThePermission()
    {
        $this->becomeUser('User3');
        $this->assertSame([], array_values(array_filter(
            (new SpacesHeaderActions())->getEntriesData(),
            static fn(array $action) => $action['id'] === 'create-space-button',
        )));
    }
}
