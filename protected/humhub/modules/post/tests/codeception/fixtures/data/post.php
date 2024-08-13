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
    [
        'id' => '1', 'message' => 'User 1 Profile Post Private', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 1, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],
    [
        'id' => '2', 'message' => 'User 1 Profile Post Public', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 1, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],
    [
        'id' => '3', 'message' => 'User 2 Profile Post Private', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '2',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 2, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '2', 'stream_channel' => 'default'],
    ],
    [
        'id' => '4', 'message' => 'User 2 Profile Post Public', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '2',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 2, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '2', 'stream_channel' => 'default'],
    ],
    [
        'id' => '5', 'message' => 'User 3 Profile Post Private', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '3',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 3, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '3', 'stream_channel' => 'default'],
    ],

    // Posts to Space 1 (Id: 1) of User 1 & 3
    [
        'id' => '6', 'message' => 'User 3 Profile Post Public', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '3',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 3, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '3', 'stream_channel' => 'default'],
    ],


    // Posts to Space 1 (Id: 1) of User 1 & 3
    [
        'id' => '7', 'message' => 'User 1 Space 1 Post Public ', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 4, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],
    [
        'id' => '8', 'message' => 'User 1 Space 1 Post Private ', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 4, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],
    [
        'id' => '9', 'message' => 'User 3 Space 1 Post Public ', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '3',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 4, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '3', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '3', 'stream_channel' => 'default'],
    ],


    // Posts to Space 2 of User 2  (Public & Private)
    [
        'id' => '10', 'message' => 'User 2 Space 2 Post Public', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '2',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 5, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '2', 'stream_channel' => 'default'],
    ],
    [
        'id' => '11', 'message' => 'User 2 Space 2 Post Private', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '2',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 5, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '2', 'stream_channel' => 'default'],
    ],

    [
        'id' => '12', 'message' => 'Admin Space 2 Post Public', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '1', 'contentcontainer_id' => 5, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],
    [
        'id' => '13', 'message' => 'Admin Space 2 Post Private', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '1',
        'content' => ['visibility' => '0', 'contentcontainer_id' => 5, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '1', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '1', 'stream_channel' => 'default'],
    ],

    // Posts to Space 2 of User 2 (Archived)
    [
        'id' => '14', 'message' => 'User 2 Space 2 Post Archived', 'url' => null, 'created_at' => '2014-08-08 05:36:06', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:06', 'updated_by' => '2',
        'content' => ['visibility' => '1', 'archived' => '1', 'contentcontainer_id' => 5, 'created_at' => '2014-08-08 05:36:05', 'created_by' => '2', 'updated_at' => '2014-08-08 05:36:05', 'updated_by' => '2', 'stream_channel' => 'default'],
    ],
];
