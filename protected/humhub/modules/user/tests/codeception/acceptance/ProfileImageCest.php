<?php

namespace user\acceptance;

use user\AcceptanceTester;

class ProfileImageCest
{
    /**
     * Create Private Spaces
     *
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testBannerUploadAndDelete(AcceptanceTester $I)
    {
        $I->wantToTest('the user banner upload');
        $I->amGoingTo('upload a new user banner');

        $I->amUser1();
        $I->amOnProfile();

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-banner-image-container');

        $I->waitForElementVisible('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-banner-image-container .image-upload-buttons .profile-image-upload');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-delete');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-crop');

        $I->attachFile('.profile-banner-image-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->dontSeeElement('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-banner-image-container .image-upload-buttons .profile-image-upload');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .profile-image-delete');
        $I->seeElement('.profile-banner-image-container .image-upload-buttons .profile-image-crop');

        $I->wantToTest('the deletion of the space banner');
        $I->amGoingTo('press the delete button');
        $I->click('.profile-banner-image-container .image-upload-buttons .profile-image-delete');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');
        $I->waitForElementVisible('.profile-banner-image-container .img-profile-header-background[src="/static/img/default_banner.jpg"]');
        $I->jsShow('.profile-banner-image-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-banner-image-container .image-upload-buttons .profile-image-upload');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-delete');
        $I->dontSeeElement('.profile-banner-image-container .image-upload-buttons .profile-image-crop');
    }

    /**
     * @param AcceptanceTester $I
     * @throws \Exception
     */
    public function testProfileImageUploadAndDelete(AcceptanceTester $I)
    {
        $I->wantToTest('the user banner upload');
        $I->amGoingTo('upload a new user profile image');

        $I->amUser1();
        $I->amOnProfile();

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-user-photo-container');

        $I->waitForElementVisible('.profile-user-photo-container .img-profile-header-background[src="/static/img/default_user.jpg"]');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-user-photo-container .image-upload-buttons .profile-image-upload');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-delete');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-crop');

        $I->attachFile('.profile-user-photo-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->dontSeeElement('.profile-user-photo-container .img-profile-header-background[src="/static/img/default_user.jpg"]');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-user-photo-container .image-upload-buttons .profile-image-upload');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .profile-image-delete');
        $I->seeElement('.profile-user-photo-container .image-upload-buttons .profile-image-crop');

        $I->wantToTest('the deletion of the space banner');
        $I->amGoingTo('press the delete button');
        $I->click('.profile-user-photo-container .image-upload-buttons .profile-image-delete');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');
        $I->waitForElementVisible('.profile-user-photo-container .img-profile-header-background[src="/static/img/default_user.jpg"]');
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->waitForElementVisible('.profile-user-photo-container .image-upload-buttons .profile-image-upload');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-delete');
        $I->dontSeeElement('.profile-user-photo-container .image-upload-buttons .profile-image-crop');
    }

    public function testBannerCrop(AcceptanceTester $I)
    {
        $I->wantToTest('the user banner upload');
        $I->amGoingTo('upload a new user banner');

        $I->amUser1();
        $I->amOnProfile();

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
        $I->wantToTest('the space banner upload');
        $I->amGoingTo('upload a new space banner');

        $I->amUser1();
        $I->amOnProfile();

        // Just to make sure there is no banner
        $this->deleteImage($I, '.profile-user-photo-container');

        $I->waitForElementVisible('.profile-user-photo-container .img-profile-header-background[src="/static/img/default_user.jpg"]');
        $I->attachFile('.profile-user-photo-container .profile-upload-input', 'test.jpg');
        $I->wait(2);
        $I->jsShow('.profile-user-photo-container .image-upload-buttons');
        $I->seeElement('.profile-user-photo-container .profile-image-crop');
        $I->click('.profile-user-photo-container .profile-image-crop');

        $I->waitForText('Modify image', null, '#globalModal');
        $I->click('Save', '#globalModal');
        $I->seeSuccess();
    }

    public function testUploadInvalidFile(AcceptanceTester $I)
    {
        $I->wantToTest('the user banner upload');
        $I->amGoingTo('upload a new user banner');

        $I->amUser1();
        $I->amOnProfile();

        // Just to make sure there is no banner
        $I->attachFile('.profile-banner-image-container .profile-upload-input', 'test.txt');
        $I->seeError();
    }

    private function deleteImage(AcceptanceTester $I, $containerClass)
    {
        $I->jsClick($containerClass.' .image-upload-buttons .btn-danger');
        $I->waitForText('Confirm image deletion', null,'#globalModalConfirm');
        $I->click('Delete', '#globalModalConfirm');
    }
}
