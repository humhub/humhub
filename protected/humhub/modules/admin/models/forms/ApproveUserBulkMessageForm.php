<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\base\Model;

/**
 * A message to several registrations awaiting approval ("Send a message" of the approval table
 * for the selected rows): one subject and text, `{displayName}` and `{AdminName}` replaced for
 * each user, as the message of a single registration ({@see ApproveUserForm::sendMessage()}).
 *
 * @since 1.20
 */
class ApproveUserBulkMessageForm extends Model
{
    public string $subject = '';

    public string $message = '';

    /**
     * @var int[] the users to write to
     */
    public array $ids = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject', 'message'], 'required'],
            [['subject', 'message'], 'string'],
            ['ids', 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'subject' => Yii::t('AdminModule.user', 'Subject'),
            'message' => Yii::t('AdminModule.user', 'Message'),
        ];
    }

    /**
     * The texts of the "send a message" template, with their placeholders.
     */
    public function setDefaults(): void
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $this->subject = Yii::t('AdminModule.user', 'About the account request for \'{displayName}\'.', ['{displayName}' => '{displayName}']);
        $this->message = $module->settings->get('auth.registrationSendMessageMailContent')
            ?: ApproveUserForm::getDefaultSendMessageMailContent();
    }

    /**
     * Sends the message to each of the users.
     *
     * @param User[] $users
     * @return int how many messages were sent
     */
    public function send(array $users): int
    {
        $sent = 0;
        $admin = Yii::$app->user->getIdentity();
        foreach ($users as $user) {
            $placeholders = ['{displayName}' => $user->displayName, '{AdminName}' => $admin->displayName];
            $form = new ApproveUserForm($user->id);
            $form->subject = strtr($this->subject, $placeholders);
            $form->message = strtr($this->message, $placeholders);
            if ($form->sendMessage()) {
                $sent++;
            }
        }

        return $sent;
    }
}
