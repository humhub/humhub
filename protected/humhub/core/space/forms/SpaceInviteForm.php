<?php

/**
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class SpaceInviteForm extends CFormModel {

    /**
     * Field for Invite GUIDs
     *
     * @var type
     */
    public $invite;

    /**
     * Field for external e-mails to invite
     *
     * @var type
     */
    public $inviteExternal;

    /**
     * Current Space
     *
     * @var type
     */
    public $space;

    /**
     * Parsed Invites with User Objects
     *
     * @var type
     */
    public $invites = array();

    /**
     * Parsed list of E-Mails of field inviteExternal
     */
    public $invitesExternal = array();

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            array('invite', 'checkInvite'),
            array('inviteExternal', 'checkInviteExternal'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'invite' => Yii::t('SpaceModule.forms_SpaceInviteForm', 'Invites'),
            'inviteExternal' => Yii::t('SpaceModule.forms_SpaceInviteForm', 'New user by e-mail (comma separated)'),
        );
    }

    /**
     * Form Validator which checks the invite field
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkInvite($attribute, $params) {

        // Check if email field is not empty
        if ($this->$attribute != "") {

            $invites = explode(",", $this->$attribute);

            foreach ($invites as $userGuid) {
                $userGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $userGuid);

                if ($userGuid == "")
                    continue;

                // Try load user
                $user = User::model()->findByAttributes(array('guid' => $userGuid));
                if ($user != null) {
                    $membership = SpaceMembership::model()->findByAttributes(array('space_id' => $this->space->id, 'user_id' => $user->id));
                    if ($membership != null && $membership->status == SpaceMembership::STATUS_MEMBER) {
                        $this->addError($attribute, Yii::t('SpaceModule.forms_SpaceInviteForm', "User is already member!"));
                        continue;
                    }
                } else {
                    $this->addError($attribute, Yii::t('SpaceModule.forms_SpaceInviteForm', "User not found!"));
                    continue;
                }


                $this->invites[] = $user;
            }
        }
    }

    /**
     * Checks a comma separated list of e-mails which should invited to space.
     * E-Mails needs to be valid and not already registered.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkInviteExternal($attribute, $params) {

        // Check if email field is not empty
        if ($this->$attribute != "") {
            $emails = explode(",", $this->$attribute);

            // Loop over each given e-mail
            foreach ($emails as $email) {
                $email = trim($email);
                $validator = new CEmailValidator;
                if (!$validator->validateValue($email)) {
                    $this->addError($attribute, Yii::t('SpaceModule.forms_SpaceInviteForm', "{email} is not valid!", array("{email}" => $email)));
                    continue;
                }

                $user = User::model()->findByAttributes(array('email' => $email));
                if ($user != null) {
                    $this->addError($attribute, Yii::t('SpaceModule.forms_SpaceInviteForm', "{email} is already registered!", array("{email}" => $email)));
                    continue;
                }

                $this->invitesExternal[] = $email;
            }
        }
    }

    /**
     * Returns an Array with selected recipients
     */
    public function getInvites() {
        return $this->invites;
    }

    /**
     * Returns an Array with selected recipients
     */
    public function getInvitesExternal() {
        return $this->invitesExternal;
    }

}
