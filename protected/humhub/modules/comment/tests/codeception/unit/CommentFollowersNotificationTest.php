<?php namespace comment;

use Codeception\Specify;
use humhub\modules\comment\models\Comment;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class CommentFollowersNotificationTest extends HumHubDbTestCase
{
    use Specify;

    public function testCommentFollowersNotification()
    {
        //test comment on base content
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $comment->save();

        //check up followers
        $this->assertNotEmpty($comment->content->getPolymorphicRelation()->getFollowers(null, true, true), 'Followers for this comment not found');

        //check up the current comment author
        $this->assertNotEmpty($comment->user, 'Current comment user is empty!');

        //check up parent object of current comment
        $parent = $comment->object_model::findOne(['id', $comment->object_id]);
        $parentUser = User::findOne(['id' => $parent->created_by]);
        $this->assertNotEmpty($parentUser, 'Parent object of current comment not found!');

        //check up the user author of commented record
        $this->assertNotEmpty($comment->getCommentedRecord()->owner, 'Author of the commented record of this comment not found!');

        //check up for sent emails at least one
        $this->assertMailSent(1, 'Comment Notification Mail sent');

        /**
         * Send notifications to only these followers:
         * author of the parent comment
         * author of the commented record
         */
        $followersShouldReceiveNotification = [
            $parentUser, // author of the parent comment
            $comment->getCommentedRecord()->owner, // author of the commented record
        ];

        $this->assertNotEmpty($followersShouldReceiveNotification, 'Followers who should receive notification array is empty!');
    }
}
