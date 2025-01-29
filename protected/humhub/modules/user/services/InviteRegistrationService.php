<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\services;

use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Invite;
use Yii;

/**
 * InviteRegistrationService is responsible for registrations (Global or by Space) using a mail invite.
 *
 * @since 1.15
 */
final class InviteRegistrationService
{
    private ?string $token;

    public function __construct(?string $token)
    {
        $this->token = $token;
    }

    public static function createFromRequestOrEmail(?string $email): InviteRegistrationService
    {
        $token = (string)Yii::$app->request->get('token');

        if (!$token) {
            $invite = Invite::findOne(['email' => $email]);
            $token = $invite->token ?? null;
        }

        return new InviteRegistrationService($token);
    }

    public function isValid(): bool
    {
        return ($this->getInvite() !== null);
    }

    private function getInvite(): ?Invite
    {
        return Invite::findOne(['token' => $this->token]);
    }

    public function populateRegistration(Registration $registration): void
    {
        $invite = $this->getInvite();
        if ($invite !== null) {
            if (Yii::$app->request->post('ChooseLanguage') === null) {
                Yii::$app->setLanguage($invite->language);
            }
            $registration->getUser()->email = $invite->email;
        }
    }

}
