<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

use humhub\modules\user\models\fieldtype\Birthday;
use humhub\modules\user\models\fieldtype\Text;

return [
    [
        'id' => 1,
        'profile_field_category_id' => 1,
        'module_id' => null,
        'field_type_class' => Text::class,
        'field_type_config' => json_encode([
            'minLength' => null,
            'maxLength' => 20,
            'validator' => null,
            'default' => null,
            'regexp' => null,
            'regexpErrorMessage' => null,
            'fieldTypes' => []
        ]),
        'internal_name' => 'firstname',
        'title' => 'First name',
        'description' => null,
        'sort_order' => 100,
        'required' => 1,
        'show_at_registration' => 1,
        'editable' => 1,
        'visible' => 1,
        'created_at' => '2019-04-02 11:24:02',
        'created_by' => null,
        'updated_at' => '2019-04-02 11:24:02',
        'updated_by' => null,
        'ldap_attribute' => 'givenName',
        'translation_category' => null,
        'is_system' => 1,
        'searchable' => 1
    ],
    [
        'id' => 2,
        'profile_field_category_id' => 1,
        'module_id' => null,
        'field_type_class' => Text::class,
        'field_type_config' => json_encode([
            'minLength' => null,
            'maxLength' => 30,
            'validator' => null,
            'default' => null,
            'regexp' => null,
            'regexpErrorMessage' => null,
            'fieldTypes' => []
        ]),
        'internal_name' => 'lastname',
        'title' => 'Last name',
        'description' => null,
        'sort_order' => 200,
        'required' => 1,
        'show_at_registration' => 1,
        'editable' => 1,
        'visible' => 1,
        'created_at' => '2019-04-02 11:24:02',
        'created_by' => null,
        'updated_at' => '2019-04-02 11:24:02',
        'updated_by' => null,
        'ldap_attribute' => 'sn',
        'translation_category' => null,
        'is_system' => 1,
        'searchable' => 1
    ],
    [
        'id' => 3,
        'profile_field_category_id' => 2,
        'module_id' => null,
        'field_type_class' => Text::class,
        'field_type_config' => json_encode([
            'minLength' => null,
            'maxLength' => 100,
            'validator' => null,
            'default' => null,
            'regexp' => null,
            'regexpErrorMessage' => null,
            'fieldTypes' => []
        ]),
        'internal_name' => 'mobile',
        'title' => 'Mobile',
        'description' => null,
        'sort_order' => 100,
        'required' => 0,
        'show_at_registration' => 0,
        'editable' => 1,
        'visible' => 1,
        'created_at' => '2019-04-02 11:24:02',
        'created_by' => null,
        'updated_at' => '2019-04-02 11:24:02',
        'updated_by' => null,
        'ldap_attribute' => null,
        'translation_category' => null,
        'is_system' => 1,
        'searchable' => 1
    ],
    [
        'id' => 4,
        'profile_field_category_id' => 3,
        'module_id' => null,
        'field_type_class' => Text::class,
        'field_type_config' => json_encode([
            'minLength' => null,
            'maxLength' => 255,
            'validator' => null,
            'default' => null,
            'regexp' => null,
            'regexpErrorMessage' => null,
            'fieldTypes' => []
        ]),
        'internal_name' => 'url',
        'title' => 'Url',
        'description' => null,
        'sort_order' => 100,
        'required' => 0,
        'show_at_registration' => 0,
        'editable' => 1,
        'visible' => 1,
        'created_at' => '2019-04-02 11:24:02',
        'created_by' => null,
        'updated_at' => '2019-04-02 11:24:02',
        'updated_by' => null,
        'ldap_attribute' => null,
        'translation_category' => null,
        'is_system' => 1,
        'searchable' => 1
    ],
    [
        'id' => 5,
        'profile_field_category_id' => 1,
        'module_id' => null,
        'field_type_class' => Birthday::class,
        'internal_name' => 'birthday',
        'title' => 'Birthday',
        'description' => null,
        'sort_order' => 300,
        'required' => 0,
        'show_at_registration' => 0,
        'editable' => 1,
        'visible' => 1,
        'created_at' => '2019-04-02 11:24:02',
        'created_by' => null,
        'updated_at' => '2019-04-02 11:24:02',
        'updated_by' => null,
        'ldap_attribute' => null,
        'translation_category' => null,
        'is_system' => 1,
        'searchable' => 1
    ],
];
