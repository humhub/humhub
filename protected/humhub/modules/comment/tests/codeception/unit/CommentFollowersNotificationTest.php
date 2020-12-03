<?php namespace comment;

use Codeception\Specify;
use humhub\modules\comment\notifications\NewComment;
use humhub\modules\comment\models\Comment;
use humhub\modules\post\models\Post;
use tests\codeception\_support\HumHubDbTestCase;

class CommentFollowersNotificationTest extends HumHubDbTestCase
{
    use Specify;

    public function testCommentNotification()
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
        $this->assertNotEmpty($comment->getCommentedRecord()->getFollowers(null, true, true), 'Followers for this comment not found');

        $this->assertHasNotification(NewComment::class, $comment);

        //check up for sent emails at least one
        $this->assertMailSent(1, 'Comment Notification Mail sent');

    }

    public function testSubCommentReplyNotification()
    {
        //test comment on base content
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User2 comment!',
            'object_model' => Post::class,
            'object_id' => 11
        ]);

        $comment->save();
        $commentId = $comment->id;

        $this->assertHasNotification(NewComment::class, $comment);

        //test comment on base content
        $this->becomeUser('User2');

        $comment = new Comment([
            'message' => 'User3 comment!',
            'object_model' => Comment::class,
            'object_id' => $commentId
        ]);

        $comment->save();

        //check up followers
        $this->assertNotEmpty($comment->getCommentedRecord()->getFollowers(null, true, true), 'Followers for this comment not found');

        //check up parent object of current comment
        /** @var Comment $parent*/
        $parent = $comment->getPolymorphicRelation();

        $this->assertNotEmpty($parent->created_by, 'Parent object created by user id is empty!');

        //check up the user author of commented record
        $this->assertNotEmpty($comment->getCommentedRecord()->owner, 'Author of the commented record of this comment not found!');

        //check up for sent emails at least one
        $this->assertMailSent(1, 'Comment Notification Mail sent');

    }
}
