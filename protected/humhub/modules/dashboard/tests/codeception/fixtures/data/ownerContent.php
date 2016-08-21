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
return [
    ['id' => 1, 'element_name' => 'test_content', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\Template', 'owner_id' => 1, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\RichtextContent', 'content_id' => 1],
    
    // Container of Layout1
    ['id' => 2, 'element_name' => 'container', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\TemplateInstance', 'owner_id' => 2, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\ContainerContent', 'content_id' => 1],
    
    // Sub Container
    ['id' => 3,'element_name' => 'container', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\ContainerContentItem', 'owner_id' => 1, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\ContainerContent', 'content_id' => 2],
    ['id' => 4,'element_name' => 'text', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\ContainerContentItem', 'owner_id' => 1, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\RichtextContent', 'content_id' => 2],
    
    ['id' => 5, 'element_name' => 'text', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\ContainerContentItem', 'owner_id' => 2, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\RichtextContent', 'content_id' => 3],
    ['id' => 6, 'element_name' => 'text', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\ContainerContentItem', 'owner_id' => 3, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\RichtextContent', 'content_id' => 4],
    ['id' => 7, 'element_name' => 'text', 'owner_model' => 'humhub\modules\custom_pages\modules\template\models\ContainerContentItem', 'owner_id' => 4, 'content_type' => 'humhub\\modules\\custom_pages\\modules\\template\\models\\RichtextContent', 'content_id' => 5],
];
