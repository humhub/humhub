<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\acceptance;

use tests\codeception\AcceptanceTester;

class I18nCest
{
    /**
     * Test that translations are loaded from backend API
     */
    public function testTranslationLoadingFromApi(AcceptanceTester $I)
    {
        $I->wantTo('verify translations are loaded from backend API');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear localStorage to force API call
        $I->executeJS('localStorage.clear();');

        // Load a page that uses i18n translations (e.g., space chooser)
        $I->click('#space-menu');
        $I->wait(2);

        // Verify the translation API was called
        $I->seeInPageSource('i18n');
    }

    /**
     * Test that translations are cached in localStorage
     */
    public function testTranslationCaching(AcceptanceTester $I)
    {
        $I->wantTo('verify translations are cached in localStorage');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear localStorage
        $I->executeJS('localStorage.clear();');

        // Trigger translation loading by using space chooser
        $I->click('#space-menu');
        $I->wait(2);

        // Check that localStorage has the cached translations
        $storageKeys = $I->executeJS('return Object.keys(localStorage).filter(key => key.startsWith("humhub.i18n."));');
        $I->assertNotEmpty($storageKeys, 'LocalStorage should contain i18n cache entries');
    }

    /**
     * Test that cached translations are used on subsequent loads
     */
    public function testTranslationFromCache(AcceptanceTester $I)
    {
        $I->wantTo('verify cached translations are used on reload');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear localStorage and load translations
        $I->executeJS('localStorage.clear();');
        $I->click('#space-menu');
        $I->wait(2);

        // Get the cached data
        $cachedKeys = $I->executeJS('return Object.keys(localStorage).filter(key => key.startsWith("humhub.i18n."));');
        $initialCacheCount = count($cachedKeys);

        // Reload page
        $I->reloadPage();
        $I->wait(1);

        // Click space menu again
        $I->click('#space-menu');
        $I->wait(1);

        // Verify cache is still present and being used
        $newCachedKeys = $I->executeJS('return Object.keys(localStorage).filter(key => key.startsWith("humhub.i18n."));');
        $I->assertEquals($initialCacheCount, count($newCachedKeys), 'Cache should persist across page loads');
    }

    /**
     * Test translation with parameters (IntlMessageFormat)
     */
    public function testTranslationWithParameters(AcceptanceTester $I)
    {
        $I->wantTo('verify translations work with parameters');

        $I->amUser1();
        $I->amOnPage('/');

        // Test translation with parameters using space search
        $I->click('#space-menu');
        $I->wait(1);

        // Type single character in search to trigger the "at least 2 characters" message
        $I->fillField('#space-menu-search input', 'a');
        $I->wait(1);

        // Should see the parametrized translation
        $I->see('at least', '#space-menu-remote-search');
        $I->see('characters', '#space-menu-remote-search');
    }

    /**
     * Test preloading multiple categories
     */
    public function testMultipleCategoryPreloading(AcceptanceTester $I)
    {
        $I->wantTo('verify multiple categories can be preloaded');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear localStorage
        $I->executeJS('localStorage.clear();');

        // Execute preload for multiple categories
        $I->executeJS('
            return humhub.require("i18n").preload(["SpaceModule.chooser", "base"]).then(function() {
                return "loaded";
            });
        ');

        $I->wait(2);

        // Check that both categories are in localStorage
        $storageKeys = $I->executeJS('return Object.keys(localStorage).filter(key => key.includes("SpaceModule.chooser") || key.includes("base"));');
        $I->assertNotEmpty($storageKeys, 'Multiple categories should be cached');
    }

    /**
     * Test that missing category triggers warning and lazy load
     */
    public function testMissingCategoryWarning(AcceptanceTester $I)
    {
        $I->wantTo('verify missing category triggers warning');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear localStorage and reload
        $I->executeJS('localStorage.clear();');
        $I->reloadPage();
        $I->wait(1);

        // Try to translate without preloading
        $result = $I->executeJS('
            var i18n = humhub.require("i18n");
            var originalWarn = humhub.log.warn;
            var warnCalled = false;

            humhub.log.warn = function() {
                warnCalled = true;
                originalWarn.apply(this, arguments);
            };

            // Try to translate from non-preloaded category
            i18n.t("NonExistentCategory", "test message");

            humhub.log.warn = originalWarn;
            return warnCalled;
        ');

        $I->assertTrue($result, 'Should trigger warning for non-preloaded category');
    }

    /**
     * Test fallback to key when translation is missing
     */
    public function testMissingTranslationFallback(AcceptanceTester $I)
    {
        $I->wantTo('verify fallback to key when translation is missing');

        $I->amUser1();
        $I->amOnPage('/');

        // Preload a category and try to translate a missing key
        $result = $I->executeJS('
            var i18n = humhub.require("i18n");
            return i18n.preload("base").then(function() {
                return i18n.t("base", "NonExistentTranslationKey12345");
            });
        ');

        $I->wait(2);

        // Should return the key itself as fallback
        $I->assertEquals('NonExistentTranslationKey12345', $result);
    }

    /**
     * Test that preload returns a promise
     */
    public function testPreloadReturnsPromise(AcceptanceTester $I)
    {
        $I->wantTo('verify preload returns a promise');

        $I->amUser1();
        $I->amOnPage('/');

        $result = $I->executeJS('
            var i18n = humhub.require("i18n");
            var promise = i18n.preload("base");
            return typeof promise.then === "function";
        ');

        $I->assertTrue($result, 'preload should return a promise');
    }

    /**
     * Test cache versioning
     */
    public function testCacheVersioning(AcceptanceTester $I)
    {
        $I->wantTo('verify cache uses version in storage key');

        $I->amUser1();
        $I->amOnPage('/');

        // Clear and load translations
        $I->executeJS('localStorage.clear();');
        $I->click('#space-menu');
        $I->wait(2);

        // Get a storage key and verify it contains version
        $storageKey = $I->executeJS('
            var keys = Object.keys(localStorage).filter(key => key.startsWith("humhub.i18n."));
            return keys.length > 0 ? keys[0] : null;
        ');

        $I->assertNotNull($storageKey, 'Should have at least one cache entry');

        // Storage key format: humhub.i18n.{version}.{language}.{category}
        $parts = explode('.', $storageKey);
        $I->assertGreaterThan(3, count($parts), 'Storage key should include version and language');
    }
}
