<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\KeyStore;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use tests\codeception\_support\HumHubDbTestCase;

class KeyStoreTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();

        // The settings fixture is reset per test and drops the keys the migration stored
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    public function testMigrationCreatedKeysAndTheSystemClient()
    {
        $keyStore = Module::instance()->getKeyStore();

        $this->assertStringStartsWith('-----BEGIN PRIVATE KEY-----', $keyStore->getPrivateKey());
        $this->assertStringStartsWith('-----BEGIN PUBLIC KEY-----', $keyStore->getPublicKey());
        $this->assertMatchesRegularExpression('/^hh-[0-9a-f]{8}$/', $keyStore->getKeyId());
        $keyStore->getEncryptionKey();

        $client = Client::findByClientId(Client::SYSTEM_CLIENT_ID);
        $this->assertNotNull($client);
        $this->assertSame(Client::TYPE_SYSTEM, $client->type);
        $this->assertFalse($client->isConfidential());
        $this->assertSame(['com.humhub.app:/oauth/callback'], $client->getRedirectUris());
    }

    public function testRotationChangesTheKeyIdAndKeepsTheEncryptionKey()
    {
        $keyStore = Module::instance()->getKeyStore();
        $kid = $keyStore->getKeyId();
        $encryptionKey = $keyStore->getEncryptionKey()->saveToAsciiSafeString();

        $keyStore->rotateKeyPair();

        $this->assertNotSame($kid, $keyStore->getKeyId());
        $this->assertSame($encryptionKey, $keyStore->getEncryptionKey()->saveToAsciiSafeString());
    }

    public function testModulePropertiesOverrideTheStoredKeys()
    {
        [$privateKey, $publicKey] = KeyStore::createKeyPair();
        $file = tempnam(sys_get_temp_dir(), 'oauth');
        file_put_contents($file, $publicKey);

        $module = Module::instance();
        $module->privateKey = $privateKey;
        $module->publicKey = 'file://' . $file;

        try {
            $keyStore = new KeyStore($module);
            $this->assertSame($privateKey, $keyStore->getPrivateKey());
            $this->assertSame($publicKey, $keyStore->getPublicKey());
            $this->assertTrue($keyStore->isOverridden());
        } finally {
            $module->privateKey = null;
            $module->publicKey = null;
            unlink($file);
        }
    }
}
