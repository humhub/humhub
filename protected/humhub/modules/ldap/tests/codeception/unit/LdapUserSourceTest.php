<?php

namespace tests\codeception\unit;

use humhub\modules\ldap\connection\LdapConnectionConfig;
use humhub\modules\ldap\connection\LdapConnectionRegistry;
use humhub\modules\ldap\Module;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Tests for {@see LdapUserSource::findUser()} — the LDAP identity resolution
 * entry point with self-healing semantics.
 *
 * Covers:
 *  - primary lookup via user_auth (source + source_id)
 *  - fallback lookups by email / username / legacy user.guid
 *  - source_id rewrite when the LDAP unique id changes
 *  - missing-row creation when no user_auth row exists yet
 *  - idempotency when source_id is already current
 *  - null return when no match at all
 *
 * No real LDAP server is required.
 */
class LdapUserSourceTest extends HumHubDbTestCase
{
    private LdapUserSource $source;

    protected function _before(): void
    {
        parent::_before();
        $this->installFakeConnection();
        $this->source = new LdapUserSource([
            'connectionId' => 'ldap',
            'id' => 'ldap',
        ]);
        $this->source->init();
        Yii::$app->userSourceCollection->setUserSource('ldap', $this->source);
    }

    private function installFakeConnection(): void
    {
        $registry = new LdapConnectionRegistry();
        $registry->setConfigs([
            'ldap' => new LdapConnectionConfig([
                'usernameAttribute' => 'uid',
                'emailAttribute' => 'mail',
                'idAttribute' => 'objectguid',
            ]),
        ]);
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        $module->setConnectionRegistry($registry);
    }

    // ---------------------------------------------------------------------------
    // Primary lookup (user_auth hit)
    // ---------------------------------------------------------------------------

    public function testPrimaryLookupByUserAuthHits(): void
    {
        $user = $this->createLdapUser('jane', 'jane@example.com');
        $this->createAuthRow($user->id, 'guid-1');

        $found = $this->source->findUser([
            'id' => 'guid-1',
            'email' => 'jane@example.com',
            'username' => 'jane',
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
    }

    // ---------------------------------------------------------------------------
    // Fallbacks: email / username / legacy guid
    // ---------------------------------------------------------------------------

    public function testFallbackByEmailHitsAndReassignsSourceId(): void
    {
        $user = $this->createLdapUser('jane', 'jane@example.com');
        $this->createAuthRow($user->id, 'guid-OLD');

        // Login with a new LDAP unique id but the same email — typical case
        // when the LDAP entry was deleted and re-created.
        $found = $this->source->findUser([
            'id' => 'guid-NEW',
            'email' => 'jane@example.com',
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
        $this->assertSame('guid-NEW', $this->findAuthSourceId($user->id));
    }

    public function testFallbackByUsernameHitsAndReassignsSourceId(): void
    {
        $user = $this->createLdapUser('jane', 'jane@example.com');
        $this->createAuthRow($user->id, 'guid-OLD');

        $found = $this->source->findUser([
            'id' => 'guid-NEW',
            'username' => 'jane',
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
        $this->assertSame('guid-NEW', $this->findAuthSourceId($user->id));
    }

    public function testFallbackByLegacyGuidHits(): void
    {
        // Pre-1.19 install: LDAP unique id was stored on user.guid before
        // user_auth existed. findUser() must still recognise such users
        // when the user_auth row is missing entirely.
        $user = $this->createLdapUser('jane', 'jane@example.com', 'guid-stored-in-user-guid');

        $found = $this->source->findUser([
            'id' => 'guid-stored-in-user-guid',
            // intentionally no email / username — only the legacy guid path.
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
        // user_auth row didn't exist before — should be created with the
        // new source_id.
        $this->assertSame('guid-stored-in-user-guid', $this->findAuthSourceId($user->id));
    }

    // ---------------------------------------------------------------------------
    // Misses & edge cases
    // ---------------------------------------------------------------------------

    public function testReturnsNullWhenNoMatchAtAll(): void
    {
        $this->createLdapUser('jane', 'jane@example.com');
        // No Auth row, no matching email/username.

        $found = $this->source->findUser([
            'id' => 'guid-unknown',
            'email' => 'someone-else@example.com',
            'username' => 'someone-else',
        ]);

        $this->assertNull($found);
    }

    public function testReassignCreatesUserAuthRowWhenMissing(): void
    {
        // User exists with user_source='ldap' but has no user_auth row at all
        // — happens for users imported pre-1.19 or via direct DB seed.
        $user = $this->createLdapUser('jane', 'jane@example.com');

        $found = $this->source->findUser([
            'id' => 'guid-NEW',
            'email' => 'jane@example.com',
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
        $this->assertSame('guid-NEW', $this->findAuthSourceId($user->id));
    }

    public function testReassignIsIdempotent(): void
    {
        $user = $this->createLdapUser('jane', 'jane@example.com');
        $this->createAuthRow($user->id, 'guid-1');

        // Same id already on file — primary lookup hits, no reassign needed.
        $this->source->findUser([
            'id' => 'guid-1',
            'email' => 'jane@example.com',
        ]);

        // Only one user_auth row, source_id unchanged.
        $authRows = Auth::find()
            ->where(['user_id' => $user->id, 'source' => 'ldap'])
            ->all();
        $this->assertCount(1, $authRows);
        $this->assertSame('guid-1', $authRows[0]->source_id);
    }

    public function testNoReassignWhenIdAttributeIsMissing(): void
    {
        // Attribute set without a normalised 'id' value (e.g. connection
        // with no idAttribute configured). findUser() must still resolve
        // the user via the fallback, but must NOT touch user_auth.
        $user = $this->createLdapUser('jane', 'jane@example.com');

        $found = $this->source->findUser([
            'email' => 'jane@example.com',
        ]);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
        // No source_id available → no user_auth row should have been touched.
        $this->assertNull($this->findAuthSourceId($user->id));
    }

    public function testReturnsNullWhenAttributesAreEmpty(): void
    {
        $this->createLdapUser('jane', 'jane@example.com');

        $found = $this->source->findUser([]);

        $this->assertNull($found);
    }

    public function testOnlyMatchesOwnUserSource(): void
    {
        // A local user with the same email must not be claimed by the
        // LDAP source's fallback.
        $local = $this->createUser('jane', 'jane@example.com', 'local');

        $found = $this->source->findUser([
            'id' => 'guid-NEW',
            'email' => 'jane@example.com',
        ]);

        $this->assertNull($found);
        // And the local user's user_source must not have been touched.
        $local->refresh();
        $this->assertSame('local', $local->user_source);
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function createLdapUser(string $username, string $email, ?string $guid = null): User
    {
        return $this->createUser($username, $email, 'ldap', $guid);
    }

    /**
     * Direct DB insert — bypasses {@see User::afterSave()} which would
     * otherwise try to attach a default group and a ContentContainer
     * record. We don't load those fixtures for this test suite, and
     * findUser() only cares about plain SELECTs against the user /
     * user_auth tables.
     */
    private function createUser(string $username, string $email, string $source, ?string $guid = null): User
    {
        $guid = $guid ?? sprintf('00000000-0000-0000-0000-%012s', substr(md5($username . $source), 0, 12));
        $now = date('Y-m-d H:i:s');

        Yii::$app->db->createCommand()->insert('user', [
            'username' => $username,
            'email' => $email,
            'guid' => $guid,
            'user_source' => $source,
            'status' => User::STATUS_ENABLED,
            'language' => 'en-US',
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return User::findOne((int) Yii::$app->db->getLastInsertID());
    }

    private function createAuthRow(int $userId, string $sourceId, string $source = 'ldap'): void
    {
        Yii::$app->db->createCommand()->insert('user_auth', [
            'user_id' => $userId,
            'source' => $source,
            'source_id' => $sourceId,
        ])->execute();
    }

    private function findAuthSourceId(int $userId, string $source = 'ldap'): ?string
    {
        $row = Auth::find()
            ->where(['user_id' => $userId, 'source' => $source])
            ->one();
        return $row?->source_id;
    }
}
