<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components\health;

use humhub\components\health\checks\CronCheck;
use humhub\components\health\checks\DocumentRootCheck;
use humhub\components\health\checks\MaintenanceCheck;
use humhub\components\health\checks\QueueCheck;
use humhub\components\health\HealthStatus;
use humhub\modules\queue\driver\MySQL;
use humhub\services\DocumentRootProbeService;
use humhub\services\DocumentRootService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Component;

/**
 * @since 1.20
 */
class OperationalChecksTest extends HumHubDbTestCase
{
    private const NOW = 1795603260; // 2026-11-25T10:41:00Z

    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->cache->delete(DocumentRootProbeService::CACHE_KEY);
    }

    private function cron(int $lastRun): CronCheck
    {
        return new CronCheck(['lastRun' => $lastRun, 'now' => self::NOW]);
    }

    public function testCron(): void
    {
        $ok = $this->cron(self::NOW - 60)->run();
        $this->assertSame(HealthStatus::Ok, $ok->status);
        $this->assertSame('2026-11-25T10:40:00Z', $ok->data['lastRun']);

        $this->assertSame(HealthStatus::Warning, $this->cron(0)->run()->status);
        $this->assertSame(HealthStatus::Warning, $this->cron(self::NOW - 3601)->run()->status);
        $this->assertSame(HealthStatus::Failure, $this->cron(self::NOW - 86401)->run()->status);
    }

    public function testCronFailureCanBeTurnedOff(): void
    {
        $check = new CronCheck(['lastRun' => self::NOW - 999999, 'now' => self::NOW, 'failureAge' => 0]);

        $this->assertSame(HealthStatus::Warning, $check->run()->status);
    }

    public function testCronReadsTheSetting(): void
    {
        Yii::$app->settings->set('cronLastRun', time());

        $this->assertSame(HealthStatus::Ok, (new CronCheck())->run()->status);
    }

    public function testQueueOfAnotherDriverIsNotMeasurable(): void
    {
        $result = (new QueueCheck(['queue' => new Component()]))->run();

        $this->assertSame(HealthStatus::Ok, $result->status);
        $this->assertStringContainsString('not measurable', $result->output);
    }

    public function testQueueWarnsAboutJobsWaitingTooLong(): void
    {
        // The test app runs the Instant driver, so measure a database queue on the same table
        $queue = new MySQL();
        $queue->db->createCommand()->delete($queue->tableName, ['channel' => $queue->channel])->execute();

        $this->assertSame(0, (new QueueCheck(['queue' => $queue]))->run()->data['waiting']);

        $queue->db->createCommand()->insert($queue->tableName, [
            'channel' => $queue->channel,
            'job' => 'x',
            'pushed_at' => time() - 1300,
            'ttr' => 300,
            'delay' => 0,
            'priority' => 1024,
            'attempt' => 0,
        ])->execute();

        $result = (new QueueCheck(['queue' => $queue]))->run();
        $this->assertSame(HealthStatus::Warning, $result->status);
        $this->assertSame(1, $result->data['waiting']);

        $this->assertSame(HealthStatus::Ok, (new QueueCheck(['queue' => $queue, 'waitThreshold' => 3600]))->run()->status);
    }

    public function testDelayedJobsThatAreNotDueAreNotWaiting(): void
    {
        $queue = new MySQL();
        $queue->db->createCommand()->delete($queue->tableName, ['channel' => $queue->channel])->execute();
        $queue->db->createCommand()->insert($queue->tableName, [
            'channel' => $queue->channel,
            'job' => 'x',
            'pushed_at' => time(),
            'ttr' => 300,
            'delay' => 86400,
            'priority' => 1024,
            'attempt' => 0,
        ])->execute();

        $result = (new QueueCheck(['queue' => $queue]))->run();

        $this->assertSame(HealthStatus::Ok, $result->status);
        $this->assertSame(0, $result->data['waiting']);
    }

    private function probe(DocumentRootService $documentRoot, string $baseUrl, ?bool $served): DocumentRootProbeService
    {
        return new DocumentRootProbeService(
            documentRoot: $documentRoot,
            baseUrl: $baseUrl,
            fetch: fn(string $url) => match ($served) {
                true => ['status' => 200, 'content' => '# HumHub', 'contentType' => 'text/markdown'],
                false => ['status' => 404, 'content' => '', 'contentType' => 'text/html'],
                null => null,
            },
        );
    }

    public function testDocumentRoot(): void
    {
        $root = new DocumentRootService('/var/www/humhub');
        $legacy = new DocumentRootService('/var/www/humhub', legacyEntryScriptPath: '/var/www/humhub');

        $ok = new DocumentRootCheck(['documentRoot' => $root, 'probe' => $this->probe($root, 'https://example.com', false)]);
        $this->assertNull($ok->getFinding());
        $this->assertSame(HealthStatus::Ok, $ok->run()->status);

        Yii::$app->cache->delete(DocumentRootProbeService::CACHE_KEY);
        $exposed = new DocumentRootCheck(['documentRoot' => $root, 'probe' => $this->probe($root, 'https://example.com/public', true)]);
        $this->assertSame(DocumentRootCheck::FINDING_DOCUMENT_ROOT_EXPOSED, $exposed->getFinding());
        $this->assertSame(HealthStatus::Failure, $exposed->run()->status);

        $legacyCheck = new DocumentRootCheck(['documentRoot' => $legacy, 'probe' => $this->probe($legacy, 'https://example.com', true)]);
        $this->assertSame(DocumentRootCheck::FINDING_LEGACY_ENTRY_SCRIPT, $legacyCheck->getFinding());
        $this->assertSame(HealthStatus::Warning, $legacyCheck->run()->status);

        Yii::$app->cache->delete(DocumentRootProbeService::CACHE_KEY);
        $unverifiable = new DocumentRootCheck(['documentRoot' => $root, 'probe' => $this->probe($root, 'https://example.com/public', null)]);
        $this->assertSame(HealthStatus::Ok, $unverifiable->run()->status);
    }

    public function testMaintenance(): void
    {
        $off = (new MaintenanceCheck(['active' => false]))->run();
        $this->assertSame(HealthStatus::Ok, $off->status);
        $this->assertSame('off', $off->output);
        $this->assertFalse($off->notable);
        $this->assertSame(HealthStatus::Warning, (new MaintenanceCheck(['active' => true]))->run()->status);
    }
}
