<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use user\FunctionalTester;

/**
 * The purpose of the profile fields, for autofill and assistive technology (`autocomplete`, WCAG 1.3.5)
 */
class ProfileAutocompleteCest
{
    public function testTheOwnProfileNamesThePurposeOfItsFields(FunctionalTester $I)
    {
        $I->amUser1();
        $I->amOnRoute('/user/account/edit');

        $I->seeElement('#profile-firstname', ['autocomplete' => 'given-name']);
        $I->seeElement('#profile-lastname', ['autocomplete' => 'family-name']);
        // The date picker keeps the purpose instead of turning the browser's suggestions off
        $I->seeElement('#profile-birthday', ['autocomplete' => 'bday']);
        // A field without a known purpose has none
        $I->dontSeeElement('#profile-birthday_hide_year[autocomplete]');
    }

    public function testAnAdminEditingSomeoneElseGetsNoAutofill(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/user/edit', ['id' => 2]);

        $I->seeElement('#profile-firstname');
        // Autofill would fill in the admin's own data
        $I->dontSeeElement('#profile-firstname[autocomplete]');
        $I->dontSeeElement('#profile-birthday[autocomplete="bday"]');
    }
}
