<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\User;
use humhub\modules\user\source\GenericUserSource;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The form of the "General" tab of the account settings: username, e-mail address and introduction tour in one form
 */
class AccountSettingsTest extends HumHubDbTestCase
{
    private function form(?User $user = null): AccountSettings
    {
        return new AccountSettings(['user' => $user ?? User::findOne(['id' => 2])]);
    }

    public function testStartsWithTheCurrentValues(): void
    {
        $form = $this->form();

        $this->assertSame('User1', $form->username);
        $this->assertSame('user1@example.com', $form->email);
        $this->assertFalse($form->changesCredentials());
    }

    public function testOnlyTheUsernameOrTheEmailAddressChangeCredentials(): void
    {
        $form = $this->form();
        $form->load(['AccountSettings' => ['show_introduction_tour' => 1, 'username' => ' User1 ']]);
        $this->assertFalse($form->changesCredentials(), 'the tour, and the username as it is with blanks around it');

        $form->load(['AccountSettings' => ['email' => 'user1-new@example.com']]);
        $this->assertTrue($form->isEmailChanged());
        $this->assertFalse($form->isUsernameChanged());
        $this->assertTrue($form->changesCredentials());
    }

    public function testAnUnchangedUsernameIsNotValidatedAgain(): void
    {
        // A username from before the current rules (forbidden today) does not keep other settings from being saved
        $userModule = Yii::$app->getModule('user');
        $forbidden = $userModule->forbiddenUsernames;
        $userModule->forbiddenUsernames = ['user1'];

        try {
            $form = $this->form();
            $form->load(['AccountSettings' => ['show_introduction_tour' => 1]]);
            $this->assertTrue($form->validate(), implode(' ', $form->getErrorSummary(true)));

            $form->load(['AccountSettings' => ['username' => 'USER1']]);
            $this->assertTrue($form->isUsernameChanged());
            $this->assertFalse($form->validate());
            $this->assertTrue($form->hasErrors('username'));
        } finally {
            $userModule->forbiddenUsernames = $forbidden;
        }
    }

    public function testTheOwnAddressInOtherCaseIsNoConflict(): void
    {
        $form = $this->form();
        $form->load(['AccountSettings' => ['email' => 'USER1@example.com', 'username' => 'user1']]);

        $this->assertTrue($form->validate(), implode(' ', $form->getErrorSummary(true)));
    }

    public function testAnAddressOfAnotherUserIsInUse(): void
    {
        $form = $this->form();
        $form->load(['AccountSettings' => ['email' => 'user2@example.com']]);

        $this->assertFalse($form->validate());
        $this->assertTrue($form->hasErrors('email'));
    }

    public function testValuesManagedByTheUserSourceAreNotLoaded(): void
    {
        Yii::$app->userSourceCollection->setUserSource('ext-source', new GenericUserSource([
            'id' => 'ext-source',
            'managedAttributes' => ['username', 'email'],
        ]));
        $user = User::findOne(['id' => 2]);
        $user->user_source = 'ext-source';

        $form = $this->form($user);
        $this->assertFalse($form->canChangeUsername());
        $this->assertFalse($form->canChangeEmail());

        $form->load(['AccountSettings' => ['username' => 'Renamed', 'email' => 'renamed@example.com', 'show_introduction_tour' => 1]]);

        $this->assertSame('User1', $form->username);
        $this->assertSame('user1@example.com', $form->email);
        $this->assertEquals(1, $form->show_introduction_tour);
        $this->assertFalse($form->changesCredentials());
    }
}
