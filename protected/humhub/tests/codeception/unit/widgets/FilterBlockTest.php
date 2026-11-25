<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\filter\CheckboxFilterInput;
use humhub\widgets\filter\DatePickerFilterInput;
use humhub\widgets\filter\FilterBlock;
use humhub\widgets\filter\PickerFilterInput;
use humhub\widgets\filter\TextFilterInput;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * A field of a filter shows no title, and its placeholder is no name: it is named by its title, else its block's
 * (WCAG 4.1.2, WAVE "Missing form label").
 */
class FilterBlockTest extends HumHubDbTestCase
{
    public function testFieldsAreNamedByTheBlockTitle()
    {
        $this->assertMatchesRegularExpression('/<input[^>]* aria-label="Date from"/', $this->block('Date from', [
            'class' => DatePickerFilterInput::class,
            'id' => 'date_from',
            'category' => 'date_filter_from',
        ]));

        $this->assertMatchesRegularExpression('/<select[^>]* aria-label="Author"/', $this->block('Author', [
            'class' => PickerFilterInput::class,
            'id' => 'originators',
            'picker' => UserPickerField::class,
            'pickerOptions' => ['name' => 'stream-user-picker'],
        ]));
    }

    public function testFieldsKeepTheirOwnTitle()
    {
        $this->assertMatchesRegularExpression('/<input[^>]* aria-label="Title"/', $this->block('Search', [
            'class' => TextFilterInput::class,
            'id' => 'title',
            'title' => 'Title',
        ]));
    }

    public function testCheckboxesAreNamedByTheirText()
    {
        $html = $this->block('Content', [
            'class' => CheckboxFilterInput::class,
            'id' => 'entry_mine',
            'title' => 'Created by me',
        ]);

        $this->assertStringContainsString('Created by me', $html);
        $this->assertStringNotContainsString('aria-label', $html);
    }

    private function block(string $title, array $filter): string
    {
        return FilterBlock::widget(['title' => $title, 'filters' => [$filter]]);
    }
}
