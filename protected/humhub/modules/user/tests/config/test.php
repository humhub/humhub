<?php

return [
    'humhub_root' => '/home/developer/workspace/humhub',
    'fixtures' => [
        'default',
        'user_follow' => 'humhub\modules\user\tests\codeception\fixtures\UserFollowFixture',
        'user_mentioning' => 'humhub\modules\user\tests\codeception\fixtures\UserMentioningFixture'
    ]
];
