<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace activity\functional;


use humhub\modules\comment\activities\NewComment;
use humhub\modules\content\activities\ContentCreated;
use humhub\modules\like\activities\Liked;
use humhub\modules\space\models\Space;
use activity\FunctionalTester;
use yii\helpers\Url;

class ActivitySettingsCest
{
    public function testSimpleActivityLink(FunctionalTester $I)
    {
        $I->wantTo('the activity link works');
        $I->amAdmin();
        $I->amOnRoute('/activity/admin/defaults');

        $I->submitForm('.panel-body form', [
            'MailSummaryForm[interval]' => '1',
            'MailSummaryForm[limitSpacesMode]' => '1',
            'MailSummaryForm[limitSpaces][]' => Space::findOne(1)->guid,
            'MailSummaryForm[activities]' => '',
            'MailSummaryForm[activities][]' => ContentCreated::class
        ]);

        $I->amOnRoute('/activity/user');
        $I->seeInField('#mailsummaryform-interval', '1' );
        $I->seeCheckboxIsChecked('[name="MailSummaryForm[limitSpacesMode]"]', '1');
        $I->seeOptionIsSelected('#mailsummaryform-limitspaces', 'Space 1');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . NewComment::class . '\']');
        $I->seeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . ContentCreated::class .'\']');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . Liked::class . '\']');

        $I->submitForm('.panel-body form', [
            'MailSummaryForm[interval]' => '2',
            'MailSummaryForm[limitSpacesMode]' => '',
            'MailSummaryForm[limitSpaces][]' => '',
            'MailSummaryForm[activities]' => '',
            'MailSummaryForm[activities][]' => NewComment::class
        ]);

        $I->seeInField('#mailsummaryform-interval', '2' );
        $I->dontSeeCheckboxIsChecked('[name="MailSummaryForm[limitSpacesMode]"]', '0');
        $I->dontSeeCheckboxIsChecked('[name="MailSummaryForm[limitSpacesMode]"]', '1');
        $I->dontSeeOptionIsSelected('#mailsummaryform-limitspaces', 'Space 1');
        $I->seeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . NewComment::class .'\']');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . ContentCreated::class . '\']');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . Liked::class . '\']');

        $I->see('Reset to defaults');
        $I->sendAjaxPostRequest(Url::toRoute('/activity/user/reset'));
        $I->amOnRoute('/activity/user');

        $I->seeInField('#mailsummaryform-interval', '1' );
        $I->seeCheckboxIsChecked('[name="MailSummaryForm[limitSpacesMode]"]', '1');
        $I->seeOptionIsSelected('#mailsummaryform-limitspaces', 'Space 1');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . NewComment::class .'\']');
        $I->seeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . ContentCreated::class . '\']');
        $I->dontSeeCheckboxIsChecked('input[name="MailSummaryForm[activities][]"][value=\'' . Liked::class . '\']');
    }

}
