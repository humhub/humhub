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
    ['id' => 1, 'name' => 'test_content', 'template_id' => 1, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\RichtextContent'],
    ['id' => 2, 'name' => 'test_text', 'template_id' => 1, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\RichtextContent'],
    ['id' => 3, 'name' => 'container', 'template_id' => 2, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\ContainerContent'],
    ['id' => 4, 'name' => 'container', 'template_id' => 3, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\ContainerContent'],
    ['id' => 5, 'name' => 'text', 'template_id' => 3, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\RichtextContent'],
    ['id' => 6, 'name' => 'text', 'template_id' => 4, 'content_type' => 'humhub\modules\custom_pages\modules\template\models\RichtextContent'],
];
