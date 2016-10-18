<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\libs;

use Yii;
use yii\base\Exception;
use humhub\modules\user\models\ProfileFieldCategory;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;

/**
 * InitialData
 *
 * @author luke
 */
class InitialData
{

    public static function bootstrap()
    {
        // Seems database is already initialized
        if (Yii::$app->settings->get('paginationSize') == 10) {
            return;
        }

        Yii::$app->settings->set('baseUrl', \yii\helpers\BaseUrl::base(true));
        Yii::$app->settings->set('paginationSize', 10);
        Yii::$app->settings->set('displayNameFormat', '{profile.firstname} {profile.lastname}');
        Yii::$app->settings->set('horImageScrollOnMobile', true);

        // Authentication
        Yii::$app->getModule('user')->settings->set('auth.ldap.refreshUsers', '1');
        Yii::$app->getModule('user')->settings->set('auth.needApproval', '0');
        Yii::$app->getModule('user')->settings->set('auth.anonymousRegistration', '1');
        Yii::$app->getModule('user')->settings->set('auth.internalUsersCanInvite', '1');

        // Mailing
        Yii::$app->settings->set('mailer.transportType', 'php');
        Yii::$app->settings->set('mailer.systemEmailAddress', 'social@example.com');
        Yii::$app->settings->set('mailer.systemEmailName', 'My Social Network');
        Yii::$app->getModule('activity')->settings->set('receive_email_activities', User::RECEIVE_EMAIL_DAILY_SUMMARY);
        Yii::$app->getModule('notification')->settings->set('receive_email_notifications', User::RECEIVE_EMAIL_WHEN_OFFLINE);

        // File
        Yii::$app->getModule('file')->settings->set('maxFileSize', '1048576' * 5);
        Yii::$app->getModule('file')->settings->set('maxPreviewImageWidth', '200');
        Yii::$app->getModule('file')->settings->set('maxPreviewImageHeight', '200');
        Yii::$app->getModule('file')->settings->set('hideImageFileInfo', '0');

        // Caching
        Yii::$app->settings->set('cache.class', 'yii\caching\FileCache');
        Yii::$app->settings->set('cache.expireTime', '3600');
        Yii::$app->getModule('admin')->settings->set('installationId', md5(uniqid("", true)));

        // Design
        Yii::$app->settings->set('theme', "HumHub");
        Yii::$app->getModule('space')->settings->set('spaceOrder', 0);

        // read and save colors from current theme
        \humhub\components\Theme::setColorVariables('HumHub');

        // Basic
        Yii::$app->getModule('tour')->settings->set('enable', 1);
        Yii::$app->settings->set('defaultLanguage', Yii::$app->language);

        // Notification
        Yii::$app->getModule('notification')->settings->set('enable_html5_desktop_notifications', 0);

        // Add Categories
        $cGeneral = new ProfileFieldCategory;
        $cGeneral->title = "General";
        $cGeneral->sort_order = 100;
        $cGeneral->visibility = 1;
        $cGeneral->is_system = 1;
        $cGeneral->description = '';
        if (!$cGeneral->save()) {
            throw new Exception(print_r($cGeneral->getErrors(), true));
        }

        $cCommunication = new ProfileFieldCategory;
        $cCommunication->title = "Communication";
        $cCommunication->sort_order = 200;
        $cCommunication->visibility = 1;
        $cCommunication->is_system = 1;
        $cCommunication->description = '';
        $cCommunication->save();

        $cSocial = new ProfileFieldCategory;
        $cSocial->title = "Social bookmarks";
        $cSocial->sort_order = 300;
        $cSocial->visibility = 1;
        $cSocial->is_system = 1;
        $cSocial->description = '';
        $cSocial->save();

        // Add Fields
        $field = new ProfileField();
        $field->internal_name = "firstname";
        $field->title = 'First name';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->ldap_attribute = 'givenName';
        $field->is_system = 1;
        $field->required = 1;
        $field->show_at_registration = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 20;
            $field->fieldType->save();
        } else {
            throw new Exception(print_r($field->getErrors(), true));
        }

        $field = new ProfileField();
        $field->internal_name = "lastname";
        $field->title = 'Last name';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->ldap_attribute = 'sn';
        $field->show_at_registration = 1;
        $field->required = 1;
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 30;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "title";
        $field->title = 'Title';
        $field->sort_order = 300;
        $field->ldap_attribute = 'title';
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 50;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "gender";
        $field->title = 'Gender';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Select::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->options = "male=>Male\nfemale=>Female\ncustom=>Custom";
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "street";
        $field->title = 'Street';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 150;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "zip";
        $field->title = 'Zip';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cGeneral->id;
        $field->is_system = 1;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        if ($field->save()) {
            $field->fieldType->maxLength = 10;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "city";
        $field->title = 'City';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "country";
        $field->title = 'Country';
        $field->sort_order = 700;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\CountrySelect::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "state";
        $field->title = 'State';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "birthday";
        $field->title = 'Birthday';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Birthday::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "about";
        $field->title = 'About';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cGeneral->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\TextArea::className();
        $field->is_system = 1;
        if ($field->save()) {
            #$field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "phone_private";
        $field->title = 'Phone Private';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "phone_work";
        $field->title = 'Phone Work';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "mobile";
        $field->title = 'Mobile';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "fax";
        $field->title = 'Fax';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_skype";
        $field->title = 'Skype Nickname';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "im_msn";
        $field->title = 'MSN';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->maxLength = 100;
            $field->fieldType->save();
        }


        $field = new ProfileField();
        $field->internal_name = "im_xmpp";
        $field->title = 'XMPP Jabber Address';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cCommunication->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'email';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url";
        $field->title = 'Url';
        $field->sort_order = 100;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_facebook";
        $field->title = 'Facebook URL';
        $field->sort_order = 200;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_linkedin";
        $field->title = 'LinkedIn URL';
        $field->sort_order = 300;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_xing";
        $field->title = 'Xing URL';
        $field->sort_order = 400;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_youtube";
        $field->title = 'Youtube URL';
        $field->sort_order = 500;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_vimeo";
        $field->title = 'Vimeo URL';
        $field->sort_order = 600;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_flickr";
        $field->title = 'Flickr URL';
        $field->sort_order = 700;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_myspace";
        $field->title = 'MySpace URL';
        $field->sort_order = 800;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_googleplus";
        $field->title = 'Google+ URL';
        $field->sort_order = 900;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $field = new ProfileField();
        $field->internal_name = "url_twitter";
        $field->title = 'Twitter URL';
        $field->sort_order = 1000;
        $field->profile_field_category_id = $cSocial->id;
        $field->field_type_class = \humhub\modules\user\models\fieldtype\Text::className();
        $field->is_system = 1;
        if ($field->save()) {
            $field->fieldType->validator = 'url';
            $field->fieldType->save();
        }

        $group = new Group();
        $group->name = "Users";
        $group->description = "Example Group by Installer";
        $group->save();
    }

}
