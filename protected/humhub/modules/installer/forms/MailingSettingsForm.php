<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\forms;

use humhub\modules\admin\models\forms\MailingSettingsForm as BaseMailingSettingsForm;
use Yii;

/**
 * MailingSettingsForm is a thin installer wrapper around the admin mailing
 * settings form. It reuses all transport handling and only adds a temporary
 * recipient used by the in-installer "send test email" action, since no admin
 * account exists yet at this early setup step.
 *
 * @since 1.19
 */
class MailingSettingsForm extends BaseMailingSettingsForm
{
    /**
     * @var string|null temporary recipient for the installer test email
     */
    public $testEmail;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // The persisted default (`php`, seeded by InitialData) may not be
        // selectable in every environment (e.g. under Docker). Fall back to the
        // first available transport so the rendered dropdown and the model agree.
        $available = array_keys($this->getTransportTypes());
        if (!in_array($this->transportType, $available, true)) {
            $this->transportType = reset($available) ?: null;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['testEmail', 'email'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'testEmail' => Yii::t('InstallerModule.base', 'Send a test email to'),
        ]);
    }
}
