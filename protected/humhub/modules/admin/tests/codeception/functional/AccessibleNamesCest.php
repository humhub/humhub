<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace admin\functional;

use admin\FunctionalTester;

/**
 * Form fields and table cells of the administration are named for screen readers (WCAG 1.3.1, 4.1.2)
 */
class AccessibleNamesCest
{
    public function testProfileFieldFlagsAreTextNotDisabledCheckboxes(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/user-profile');

        $I->dontSeeElement('.grid-view input[type="checkbox"]');
        $I->see('Yes', '.grid-view td .visually-hidden');
        $I->see('No', '.grid-view td .visually-hidden');
        $I->see('Actions', '.grid-view th.action-column .visually-hidden');
        $I->seeElement('.grid-view > .table-responsive > table');
    }

    public function testPermissionDropdownsAreNamed(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/user-permissions');

        $I->seeElement('select[aria-label="Filter by module"]');
        $I->seeElement('.permission-grid-editor select.editableCell[aria-label="View your about page"]');
        $I->dontSeeElement('.permission-grid-editor select.editableCell:not([aria-label])');
        $I->dontSeeElement('.permission-grid-editor select[class*="form-controleditableCell"]');
    }

    public function testLogFilterFieldsHaveLabels(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/logging');

        foreach (['term' => 'Search term', 'day' => 'Day', 'levels' => 'Level', 'category' => 'Category'] as $attribute => $label) {
            $I->see($label, 'label.visually-hidden[for="' . $attribute . '"]');
        }
    }
}
