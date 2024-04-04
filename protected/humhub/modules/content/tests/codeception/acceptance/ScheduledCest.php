<?php

use content\AcceptanceTester;

class ScheduledCest
{
    public const DATE_FORMAT = 'short';

    public function testCreateDraftPost(AcceptanceTester $I)
    {
        $I->amSpaceAdmin(false, 3);

        $I->wantTo('create a scheduled post.');
        $I->waitForText('What\'s on your mind?');
        $I->click('#contentFormBody .humhub-ui-richtext[contenteditable]');
        $postContent = 'Sample text for a scheduled post';
        $I->fillField('#contentFormBody .humhub-ui-richtext[contenteditable]', $postContent);
        $I->click('#contentFormBody ul.preferences');
        $datetime = (new Datetime('tomorrow'))->setTime(19, 15);
        $this->updateSchedulingOptions($I, $datetime);
        $I->see('Save scheduling', '#post_submit_button');
        $I->click('#post_submit_button', '#contentFormBody');

        $I->wantTo('ensure the scheduled content has a proper badge.');
        $I->waitForText($postContent, null, '.wall-entry');
        $I->see($this->getLabelText($datetime), '//div[@class="wall-entry"][1]');
        $I->wantTo('ensure author can see the scheduled content on dashboard.');
        $I->amOnDashboard();
        $I->waitForText($postContent, null, '[data-stream-entry="1"]');
        $I->waitForText($this->getLabelText($datetime), null, '[data-stream-entry="1"]');

        $I->wantTo('ensure the scheduled content is not visible for other users.');
        $I->amUser2(true);
        $I->amOnSpace3();
        $I->dontSee($postContent);
        $I->amOnDashboard();
        $I->waitForElementVisible('[data-stream-entry="1"]');
        $I->dontSee($postContent);

        $I->wantTo('update scheduled options of the existing content');
        $I->amSpaceAdmin(true, 3);
        $I->waitForText($postContent);
        $I->jsClick('.wall-entry:first .dropdown-toggle');
        $datetime = (new Datetime('today'))->setTime(7, 45);
        $this->updateSchedulingOptions($I, $datetime, '.label-state-scheduled');

        $I->wantTo('ensure the scheduled content can be modified to draft');
        $I->jsClick('.wall-entry:first .dropdown-toggle');
        $this->disableSchedulingOptions($I);
    }

    private function getLabelText(?Datetime $datetime = null): string
    {
        return $datetime instanceof DateTime
            ? 'SCHEDULED FOR ' . Yii::$app->formatter->asDatetime($datetime, self::DATE_FORMAT)
            : 'DRAFT';
    }

    private function updateSchedulingOptions(AcceptanceTester $I, ?Datetime $datetime = null, $labelSelector = '.label-content-state')
    {
        $I->waitForText('Schedule publication');
        $I->jsClick('.dropdown.open [data-action-click=scheduleOptions]');
        $I->waitForText('Scheduling Options', null, '#globalModal');
        if ($datetime instanceof DateTime) {
            $I->checkOption('#scheduleoptionsform-enabled');
            $I->fillField('ScheduleOptionsForm[date]', Yii::$app->formatter->asDate($datetime, self::DATE_FORMAT));
            $I->fillField('ScheduleOptionsForm[time]', Yii::$app->formatter->asTime($datetime, self::DATE_FORMAT));
            $I->click('.field-scheduleoptionsform-time');// to unfocus a datepicker in order to make the "Save" button visible
        } else {
            $I->uncheckOption('#scheduleoptionsform-enabled');
        }
        $I->click('Save');
        $I->waitForText($this->getLabelText($datetime), 5, $labelSelector);
    }

    private function disableSchedulingOptions(AcceptanceTester $I)
    {
        $this->updateSchedulingOptions($I, null, '.label-state-draft');
    }
}
