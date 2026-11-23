<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;

/**
 * The time limit of the session and its renewal, for the warning before it ends (`humhub.user.session.js`, WCAG 2.2.1)
 */
class SessionCest
{
    private const PASSWORD = 'user^humhub@PASS%worD!';

    public function testThePageTellsTheTimeLimitOfTheSession(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/dashboard/dashboard');

        $config = $this->grabConfig($I, 'user.session');
        Assert::assertSame((int)Yii::$app->user->authTimeout, $config['timeout']);
        Assert::assertGreaterThan(0, $config['timeout']);
        Assert::assertStringContainsString('user%2Fsession%2Fkeep-alive', $config['keepAliveUrl']);
        Assert::assertSame('Stay signed in', $config['text']['stay']);
        Assert::assertStringContainsString('{time}', $config['text']['warning']);
    }

    public function testARememberedLoginHasNoTimeLimit(FunctionalTester $I)
    {
        $I->amOnRoute('/user/auth/login');
        $I->fillField('Login[username]', 'User1');
        $I->click('#continue-button');
        $I->fillField('Login[password]', self::PASSWORD);
        $I->checkOption('#login-rememberme');
        $I->click('#login-button');

        $I->amOnRoute('/dashboard/dashboard');
        Assert::assertFalse($this->grabConfig($I, 'user')['isGuest']);
        Assert::assertSame(['timeout' => 0], $this->grabConfig($I, 'user.session'));
    }

    public function testAGuestHasNoTimeLimit(FunctionalTester $I)
    {
        $I->amOnRoute('/user/auth/login');

        Assert::assertSame(['timeout' => 0], $this->grabConfig($I, 'user.session'));
    }

    public function testKeepAliveExtendsTheSession(FunctionalTester $I)
    {
        $I->amUser1();
        $I->sendAjaxPostRequest('/index-test.php?r=user%2Fsession%2Fkeep-alive');

        $I->seeResponseCodeIs(200);
        Assert::assertSame(['success' => true], json_decode($I->grabPageSource(), true));
    }

    public function testKeepAliveNeedsASession(FunctionalTester $I)
    {
        // As the module asks: JSON, else a guest is sent to the sign in
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAjaxPostRequest('/index-test.php?r=user%2Fsession%2Fkeep-alive');

        $I->seeResponseCodeIs(403);
    }

    public function testKeepAliveIsAPost(FunctionalTester $I)
    {
        $I->amUser1();
        $I->sendAjaxGetRequest('/index-test.php?r=user%2Fsession%2Fkeep-alive');

        $I->seeResponseCodeIs(405);
    }

    /**
     * @return array the config of the module in the page's `humhub.config.set({...})`
     */
    private function grabConfig(FunctionalTester $I, string $module): array
    {
        $source = $I->grabPageSource();
        $start = strpos($source, '"' . $module . '":{');
        Assert::assertNotFalse($start, 'The page has no config of ' . $module);
        $start = strpos($source, '{', $start);

        // The object up to its closing brace, braces in strings left out
        $depth = 0;
        $inString = false;
        for ($i = $start, $length = strlen($source); $i < $length; $i++) {
            $char = $source[$i];
            if ($inString) {
                if ($char === '\\') {
                    $i++;
                } elseif ($char === '"') {
                    $inString = false;
                }
            } elseif ($char === '"') {
                $inString = true;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}' && --$depth === 0) {
                return json_decode(substr($source, $start, $i - $start + 1), true);
            }
        }

        Assert::fail('The config of ' . $module . ' is not closed');
    }
}
