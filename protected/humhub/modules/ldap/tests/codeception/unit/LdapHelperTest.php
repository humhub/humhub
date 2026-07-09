<?php

namespace tests\codeception\unit;

use Codeception\Test\Unit;
use humhub\modules\ldap\helpers\LdapHelper;

/**
 * Unit tests for {@see LdapHelper}.
 * These tests are pure-PHP and do not require a database or LDAP server.
 */
class LdapHelperTest extends Unit
{
    // ---------------------------------------------------------------------------
    // isBinary
    // ---------------------------------------------------------------------------

    public function testIsBinaryReturnsTrueForNullByte(): void
    {
        $this->assertTrue(LdapHelper::isBinary("binary\x00data"));
    }

    public function testIsBinaryReturnsTrueForInvalidUtf8(): void
    {
        $this->assertTrue(LdapHelper::isBinary("\xff\xfe"));
    }

    public function testIsBinaryReturnsFalseForRegularString(): void
    {
        $this->assertFalse(LdapHelper::isBinary('hello world'));
    }

    public function testIsBinaryReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(LdapHelper::isBinary(''));
    }

    // ---------------------------------------------------------------------------
    // dropMultiValues
    // ---------------------------------------------------------------------------

    public function testDropMultiValuesCollapsesArrayToFirstElement(): void
    {
        $input = ['cn' => ['John Doe', 'Johnny']];
        $result = LdapHelper::dropMultiValues($input);

        $this->assertSame('John Doe', $result['cn']);
    }

    public function testDropMultiValuesKeepsListedKeyAsArray(): void
    {
        $input = [
            'memberof' => ['cn=group1,dc=example', 'cn=group2,dc=example'],
        ];
        $result = LdapHelper::dropMultiValues($input, ['memberof']);

        $this->assertSame(['cn=group1,dc=example', 'cn=group2,dc=example'], $result['memberof']);
    }

    public function testDropMultiValuesPreservesScalarValues(): void
    {
        $input = [
            'uid'  => 'john.doe',
            'mail' => 'john@example.org',
        ];
        $result = LdapHelper::dropMultiValues($input);

        $this->assertSame($input, $result);
    }

    public function testDropMultiValuesSingleElementArrayIsUnwrapped(): void
    {
        $input = ['mail' => ['john@example.org']];
        $result = LdapHelper::dropMultiValues($input);

        $this->assertSame('john@example.org', $result['mail']);
    }

    // ---------------------------------------------------------------------------
    // cleanLdapResponse
    // ---------------------------------------------------------------------------

    public function testCleanLdapResponseRemovesIntegerKeys(): void
    {
        $raw = [
            0       => 'cn',
            1       => 'mail',
            'count' => 2,
            'cn'    => ['count' => 1, 0 => 'John Doe'],
            'mail'  => ['count' => 1, 0 => 'john@example.org'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertArrayNotHasKey(0, $result);
        $this->assertArrayNotHasKey(1, $result);
    }

    public function testCleanLdapResponseNormalizesAttributeKeysToLowercase(): void
    {
        $raw = [
            'CN'   => ['count' => 1, 0 => 'John Doe'],
            'MAIL' => ['count' => 1, 0 => 'john@example.org'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertArrayHasKey('cn', $result);
        $this->assertArrayHasKey('mail', $result);
        $this->assertArrayNotHasKey('CN', $result);
        $this->assertArrayNotHasKey('MAIL', $result);
    }

    public function testCleanLdapResponseUnwrapsSingleValueArrays(): void
    {
        $raw = [
            'cn'   => ['count' => 1, 0 => 'John Doe'],
            'mail' => ['count' => 1, 0 => 'john@example.org'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertSame('John Doe', $result['cn']);
        $this->assertSame('john@example.org', $result['mail']);
    }

    public function testCleanLdapResponseKeepsMultiValueArray(): void
    {
        $raw = [
            'title' => ['count' => 2, 0 => 'Engineer', 1 => 'Architect'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertIsArray($result['title']);
        $this->assertCount(2, $result['title']);
        $this->assertSame(['Engineer', 'Architect'], $result['title']);
    }

    public function testCleanLdapResponseNormalizesMemberofToLowercaseArray(): void
    {
        $raw = [
            'memberof' => ['count' => 2, 0 => 'CN=Group1,DC=example', 1 => 'CN=Group2,DC=example'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertIsArray($result['memberof']);
        $this->assertSame(['cn=group1,dc=example', 'cn=group2,dc=example'], $result['memberof']);
    }

    public function testCleanLdapResponseNormalizesSingleMemberofToArray(): void
    {
        $raw = [
            'memberof' => ['count' => 1, 0 => 'CN=Admins,DC=example'],
        ];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertIsArray($result['memberof']);
        $this->assertSame(['cn=admins,dc=example'], $result['memberof']);
    }

    public function testCleanLdapResponsePassesThroughScalarValues(): void
    {
        $raw = ['dn' => 'uid=john,ou=users,dc=example,dc=org'];

        $result = LdapHelper::cleanLdapResponse($raw);

        $this->assertSame('uid=john,ou=users,dc=example,dc=org', $result['dn']);
    }
}
