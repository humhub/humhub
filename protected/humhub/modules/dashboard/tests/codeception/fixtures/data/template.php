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
    ['id' => 1, 'name' => 'testTemplate', 'source' => '<div>{{ test_content }}</div>{{ test_text }}', 'description' => 'Template with two richtext elements', 'type' => 'layout'],
    ['id' => 2, 'name' => 'layout1', 'source' => '<div>{{ container }}</div>', 'description' => 'Layout with one container element', 'type' => 'layout'],
    ['id' => 3, 'name' => 'containerText', 'source' => '{{ container }} {{ text }}', 'description' => 'Cotnainer template with other container inside', 'type' => 'container'],
    ['id' => 4, 'name' => 'simpleText', 'source' => '<div>{{ text }}</div>', 'description' => 'Simple text', 'type' => 'container'],
];
