<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\table;

use humhub\components\listing\ListContext;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\components\UserTable;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * The server side of a data table (`humhub\components\table\DataTable`), on the administration's
 * user table: definitions, rows, and what a module changes on `EVENT_INIT`.
 *
 * @since 1.20
 */
class DataTableTest extends HumHubDbTestCase
{
    protected function tearDown(): void
    {
        Event::offAll();
        parent::tearDown();
    }

    public function testDefinitionsHaveColumnsActionsAndFilters()
    {
        $this->becomeUser('Admin');

        $definitions = (new UserTable())->definitions(ListContext::forCurrentUser());

        $this->assertSame(['name', 'username', 'groups', 'source', 'status', 'lastLogin', 'createdAt'], array_column($definitions['columns'], 'key'));
        // Shown until the user chooses: groups and last login
        $this->assertSame(['username', 'groups', 'createdAt'], array_keys(array_filter(array_column($definitions['columns'], 'visible', 'key'), static fn($visible) => $visible === false)));
        $this->assertSame(['key' => 'name', 'type' => 'main', 'label' => 'Name', 'priority' => 1, 'sort' => 'name'], $definitions['columns'][0]);
        $this->assertSame('group', $definitions['columns'][2]['filter']);
        // The destructive actions last.
        $this->assertSame(['edit', 'enable', 'disable', 'impersonate', 'profile', 'delete', 'delete-permanently'], array_column($definitions['actions'], 'id'));
        $this->assertTrue($definitions['actions'][5]['destructive']);
        $this->assertSame(['q', 'status', 'group', 'source'], array_column($definitions['filters'], 'key'));
    }

    public function testTheSourceColumnIsForAdministratorsOnly()
    {
        $this->becomeUser('User1');

        $this->assertNull((new UserTable())->getColumn('source'));
    }

    public function testARowHasItsCellsAndTheActionsItOffers()
    {
        $this->becomeUser('Admin');

        $row = (new UserTable())->row(User::findOne(3));

        $this->assertSame(3, $row['id']);
        $this->assertSame('Sara Tester', $row['cells']->name['text']);
        $this->assertSame('user2@example.com', $row['cells']->name['subtitle']);
        $this->assertSame([['text' => 'Moderators', 'value' => '3']], $row['cells']->groups);
        $this->assertSame(['edit', 'disable', 'impersonate', 'profile', 'delete'], array_column($row['actions'], 'id'));
        // `/api/v2/admin/user/3/disable` with pretty URLs
        $this->assertSame(\yii\helpers\Url::to(['/admin/api/user/disable', 'id' => 3]), $row['actions'][1]['url']);
    }

    public function testAModuleAddsAColumnAndAnActionAndRemovesOne()
    {
        $this->becomeUser('Admin');
        Event::on(UserTable::class, DataTable::EVENT_INIT, function (Event $event) {
            /** @var DataTable $table */
            $table = $event->sender;
            $table->addColumn(new ColumnDefinition('language', ColumnDefinition::TYPE_TEXT, 'Language', priority: 3, sortOrder: 350,
                value: static fn(User $user) => ['text' => $user->language]));
            $table->addAction(new RowAction('reset-twofa', 'Reset 2FA', RowAction::KIND_POST,
                static fn(User $user) => ['/twofa/admin/reset-user', 'id' => $user->id],
                confirm: 'Sure?', visible: static fn(User $user) => $user->id !== 1, sortOrder: 150));
            $table->removeAction('impersonate');
        });

        $table = new UserTable();
        $this->assertSame(['name', 'username', 'groups', 'source', 'status', 'lastLogin', 'language', 'createdAt'], array_map(static fn($column) => $column->key, $table->getColumns()));

        $row = $table->row(User::findOne(2));
        $this->assertSame(['text' => 'en-US'], $row['cells']->language);
        $this->assertSame(['edit', 'reset-twofa', 'disable', 'profile', 'delete'], array_column($row['actions'], 'id'));
        $this->assertNotContains('reset-twofa', array_column($table->row(User::findOne(1))['actions'], 'id'));
    }

    public function testANonAdministratorIsOfferedNothingOnASystemAdministrator()
    {
        $this->becomeUser('User1');

        $row = (new UserTable())->row(User::findOne(1));

        $this->assertSame(['profile'], array_column($row['actions'], 'id'));
    }
}
