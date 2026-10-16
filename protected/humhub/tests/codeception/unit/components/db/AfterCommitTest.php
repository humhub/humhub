<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\db;

use humhub\components\db\AfterCommit;
use PDO;
use RuntimeException;
use tests\codeception\_support\HumHubDbTestCase;
use yii\db\Connection;
use yii\log\Logger;

/**
 * Runs on an own in-memory connection: the transaction the test harness wraps around each test
 * is never committed.
 */
class AfterCommitTest extends HumHubDbTestCase
{
    private Connection $db;

    private array $calls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new Connection(['dsn' => 'sqlite::memory:']);
        $this->db->open();
        // the harness begins a transaction on every connection it sees opening
        while ($this->db->getTransaction()?->getIsActive()) {
            $this->db->getTransaction()->rollBack();
        }
        AfterCommit::$immediate = false;
    }

    public function testRunsAtOnceWithoutTransaction()
    {
        AfterCommit::run($this->record('a'), $this->db);

        $this->assertSame(['a'], $this->calls);
    }

    public function testRunsOnceAfterTheOutermostCommitInRegistrationOrder()
    {
        $transaction = $this->db->beginTransaction();
        AfterCommit::run($this->record('a'), $this->db);

        $nested = $this->db->beginTransaction();
        AfterCommit::run($this->record('b'), $this->db);
        $nested->commit();
        $this->assertSame([], $this->calls);

        $transaction->commit();
        $this->assertSame(['a', 'b'], $this->calls);

        $this->db->beginTransaction()->commit();
        $this->assertSame(['a', 'b'], $this->calls);
    }

    public function testOutermostRollbackDropsTheCallbacks()
    {
        $transaction = $this->db->beginTransaction();
        AfterCommit::run($this->record('a'), $this->db);
        $transaction->rollBack();

        $this->db->beginTransaction()->commit();
        $this->assertSame([], $this->calls);
    }

    public function testRollbackToASavepointKeepsTheCallbacks()
    {
        $transaction = $this->db->beginTransaction();
        $nested = $this->db->beginTransaction();
        AfterCommit::run($this->record('a'), $this->db);
        $nested->rollBack();
        $transaction->commit();

        $this->assertSame(['a'], $this->calls);
    }

    public function testAFailingCallbackIsLoggedAndDoesNotStopTheOthers()
    {
        $transaction = $this->db->beginTransaction();
        AfterCommit::run(fn() => throw new RuntimeException('boom'), $this->db);
        AfterCommit::run($this->record('b'), $this->db);

        static::logInitialize();
        $transaction->commit();

        $this->assertSame(['b'], $this->calls);
        static::assertLogCount(1, null, Logger::LEVEL_ERROR, ['db']);
    }

    public function testCallbacksOfAClosedConnectionAreDroppedOnTheNextBegin()
    {
        $this->db->beginTransaction();
        AfterCommit::run($this->record('stale'), $this->db);
        $this->db->close();
        // a fresh PDO: reopening would get the harness's cached one with its open transaction
        $this->db->pdo = new PDO('sqlite::memory:');

        $transaction = $this->db->beginTransaction();
        AfterCommit::run($this->record('a'), $this->db);
        $transaction->commit();

        $this->assertSame(['a'], $this->calls);
    }

    public function testImmediateIgnoresTheTransaction()
    {
        AfterCommit::$immediate = true;
        $transaction = $this->db->beginTransaction();
        AfterCommit::run($this->record('a'), $this->db);

        $this->assertSame(['a'], $this->calls);
        $transaction->rollBack();
    }

    private function record(string $name): callable
    {
        return function () use ($name) {
            $this->calls[] = $name;
        };
    }
}
