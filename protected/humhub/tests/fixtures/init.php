<?php

Yii::app()->params['installed'] = false;

// Migrate up the database
Yii::import('application.commands.shell.ZMigrateCommand');
ZMigrateCommand::AutoMigrate();

Yii::app()->params['installed'] = true;

// Create empty dynamic configuration file
$content = "<" . "?php return ";
$content .= var_export(array(), true);
$content .= "; ?" . ">";
file_put_contents(Yii::app()->params['dynamicConfigFile'], $content);

foreach ($this->getFixtures() as $tableName => $fixturePath) {
    $this->resetTable($tableName);
    $this->loadFixture($tableName);
}

// initialize a controller (which defaults to null in tests)
$c = new CController('phpunit');
$c->setAction(new CInlineAction($c, 'urltest'));
Yii::app()->setController($c);




// Add Categories
$cGeneral = new ProfileFieldCategory;
$cGeneral->title = "General";
$cGeneral->sort_order = 100;
$cGeneral->visibility = 1;
$cGeneral->is_system = true;
$cGeneral->description = '';
$cGeneral->save();

$cCommunication = new ProfileFieldCategory;
$cCommunication->title = "Communication";
$cCommunication->sort_order = 200;
$cCommunication->visibility = 1;
$cCommunication->is_system = true;
$cCommunication->description = '';
$cCommunication->save();

$cSocial = new ProfileFieldCategory;
$cSocial->title = "Social bookmarks";
$cSocial->sort_order = 300;
$cSocial->visibility = 1;
$cSocial->is_system = true;
$cSocial->description = '';
$cSocial->save();

// Add Fields
$field = new ProfileField();
$field->internal_name = "firstname";
$field->title = 'Firstname';
$field->sort_order = 100;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->ldap_attribute = 'givenName';
$field->is_system = 1;
$field->required = 1;
$field->show_at_registration = 1;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}


$field = new ProfileField();
$field->internal_name = "lastname";
$field->title = 'Lastname';
$field->sort_order = 200;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->ldap_attribute = 'sn';
$field->show_at_registration = 1;
$field->required = 1;
$field->is_system = 1;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "title";
$field->title = 'Title';
$field->sort_order = 300;
$field->ldap_attribute = 'title';
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "gender";
$field->title = 'Gender';
$field->sort_order = 300;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeSelect';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->options = "male=>Male\nfemale=>Female\ncustom=>Custom";
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "street";
$field->title = 'Street';
$field->sort_order = 400;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 150;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "zip";
$field->title = 'Zip';
$field->sort_order = 500;
$field->profile_field_category_id = $cGeneral->id;
$field->is_system = true;
$field->field_type_class = 'ProfileFieldTypeNumber';
if ($field->save()) {
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "city";
$field->title = 'City';
$field->sort_order = 600;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "country";
$field->title = 'Country';
$field->sort_order = 700;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}


$field = new ProfileField();
$field->internal_name = "state";
$field->title = 'State';
$field->sort_order = 800;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "birthday";
$field->title = 'Birthday';
$field->sort_order = 900;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeBirthday';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "about";
$field->title = 'About';
$field->sort_order = 900;
$field->profile_field_category_id = $cGeneral->id;
$field->field_type_class = 'ProfileFieldTypeTextArea';
$field->is_system = true;
if ($field->save()) {
    #$field->fieldType->maxLength = 100;
    $field->fieldType->save();
}


$field = new ProfileField();
$field->internal_name = "phone_private";
$field->title = 'Phone Private';
$field->sort_order = 100;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "phone_work";
$field->title = 'Phone Work';
$field->sort_order = 200;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "mobile";
$field->title = 'Mobile';
$field->sort_order = 300;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "fax";
$field->title = 'Fax';
$field->sort_order = 400;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "im_skype";
$field->title = 'Skype Nickname';
$field->sort_order = 500;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "im_msn";
$field->title = 'MSN';
$field->sort_order = 600;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->maxLength = 100;
    $field->fieldType->save();
}


$field = new ProfileField();
$field->internal_name = "im_icq";
$field->title = 'ICQ Number';
$field->sort_order = 700;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeNumber';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "im_xmpp";
$field->title = 'XMPP Jabber Address';
$field->sort_order = 800;
$field->profile_field_category_id = $cCommunication->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'email';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url";
$field->title = 'Url';
$field->sort_order = 100;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_facebook";
$field->title = 'Facebook URL';
$field->sort_order = 200;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_linkedin";
$field->title = 'LinkedIn URL';
$field->sort_order = 300;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_xing";
$field->title = 'Xing URL';
$field->sort_order = 400;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_youtube";
$field->title = 'Youtube URL';
$field->sort_order = 500;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_vimeo";
$field->title = 'Vimeo URL';
$field->sort_order = 600;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_flickr";
$field->title = 'Flickr URL';
$field->sort_order = 700;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_myspace";
$field->title = 'MySpace URL';
$field->sort_order = 800;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_googleplus";
$field->title = 'Google+ URL';
$field->sort_order = 900;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}

$field = new ProfileField();
$field->internal_name = "url_twitter";
$field->title = 'Twitter URL';
$field->sort_order = 1000;
$field->profile_field_category_id = $cSocial->id;
$field->field_type_class = 'ProfileFieldTypeText';
$field->is_system = true;
if ($field->save()) {
    $field->fieldType->validator = 'url';
    $field->fieldType->save();
}
