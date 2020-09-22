<?php

namespace space\acceptance;

use space\AcceptanceTester;

class ProfileImageCest
{

    /**
     * Create private space
     *
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testBannerUploadAndDelete(AcceptanceTester $I)
    {
        $I->amSpaceAdmin();

        $I->wantToTest('the space banner upload');
        $I->amGoingTo('upload a new space banner');

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-banner-image-container');

        $I->waitForElementVisible('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .profile-image-upload');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-edit');

        $I->attachFile('.profile-banner-image-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->dontSeeElement('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .profile-image-upload');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .profile-image-edit');

        $I->wantToTest('the deletion of the space banner');
        $I->amGoingTo('press the delete button');
        $I->click('.profile-banner-image-container .image-upload-buttons .btn-danger');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');
        $I->waitForElementVisible('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .btn-info');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-edit');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testProfileImageUploadAndDelete(AcceptanceTester $I)
    {
        $I->amSpaceAdmin();

        $I->wantToTest('the space profile image upload');
        $I->amGoingTo('upload a new space profile image');

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-user-photo-container');

        $I->waitForElementVisible('.profile-user-photo-container .space-acronym');
        $I->wait(2); // wait for animation
        $I->see('S2','.profile-user-photo-container .space-acronym');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .btn-info');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-edit');

        $I->attachFile('.profile-user-photo-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->dontSeeElement('.profile-user-photo-container .space-profile-acronym-2 space-acronym');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .btn-info');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .profile-image-edit');

        $I->wantToTest('the deletion of the space profile image');
        $I->amGoingTo('press the delete button');

        $I->click('.profile-user-photo-container .image-upload-buttons .btn-danger');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');

        $I->waitForElementVisible('.profile-user-photo-container .space-acronym');
        $I->wait(2); // wait for animation
        $I->see('S2','.profile-user-photo-container .space-acronym');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .btn-info');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-edit');
    }

    public function testUploadInvalidFile(AcceptanceTester $I)
    {
        $I->wantToTest('the user banner upload');
        $I->amGoingTo('upload a new user banner');

        $I->amSpaceAdmin();

        // Just to make sure there is no banner
        $I->attachFile('.profile-banner-image-container .profile-upload-input', 'test.txt');
        $I->seeError();
    }

    public function testBannerCrop(AcceptanceTester $I)
    {
        $I->amSpaceAdmin();

        $I->wantToTest('the space banner upload');
        $I->amGoingTo('upload a new space banner');

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-banner-image-container');

        $I->waitForElementVisible('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->attachFile('.profile-banner-image-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->seeElement('.profile-banner-image-container .profile-image-crop');
        $I->click('.profile-banner-image-container .profile-image-crop');

        $I->waitForText('Modify image', null, '#globalModal');
        $I->click('Save', '#globalModal');
        $I->seeSuccess();
    }

    public function testProfileImageCrop(AcceptanceTester $I)
    {
        $I->amSpaceAdmin();

        $I->wantToTest('the space banner upload');
        $I->amGoingTo('upload a new space banner');

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-user-photo-container');

        $I->waitForElementVisible('.profile-user-photo-container .space-acronym');
        $I->attachFile('.profile-user-photo-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->seeElement('.profile-user-photo-container .profile-image-crop');
        $I->click('.profile-user-photo-container .profile-image-crop');

        $I->waitForText('Modify image', null, '#globalModal');
        $I->click('Save', '#globalModal');
        $I->seeSuccess();
    }

    private function deleteImage(AcceptanceTester $I, $containerClass)
    {
        $I->jsClick($containerClass.' .image-upload-buttons .btn-danger');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');
    }
}
