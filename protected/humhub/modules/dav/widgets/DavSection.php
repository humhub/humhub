<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\widgets;

use humhub\modules\dav\controllers\ServerController;
use humhub\modules\dav\Module;
use humhub\modules\oauth\widgets\TokensSection;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\security\SecuritySection;
use Yii;

/**
 * How to connect files as a network drive: the address of the endpoint and the username, in
 * the "Devices & Access" tab next to the tokens. Shown to those who may create a token, when
 * a module offers them a mount.
 *
 * @since 1.20
 */
class DavSection extends SecuritySection
{
    public function getTitle(): string
    {
        return Yii::t('DavModule.base', 'Network drive (WebDAV)');
    }

    public function getDescription(): string
    {
        return Yii::t('DavModule.base', 'Connect your files in Finder, Windows Explorer or any WebDAV client.');
    }

    public function isVisible(): bool
    {
        $user = Yii::$app->user->getIdentity();

        return $user instanceof User
            && TokensSection::canCreate()
            && Module::instance()->getMounts($user) !== [];
    }

    protected function renderContent(): string
    {
        return $this->render('dav-section', [
            'address' => Yii::$app->urlManager->getHostInfo() . ServerController::baseUri(),
            'username' => (string)Yii::$app->user->getIdentity()->username,
        ]);
    }
}
