<?php

namespace humhub\modules\user\tests\codeception\unit;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\NewComment;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\notifications\Mentioned;
use tests\codeception\_support\HumHubDbTestCase;

class MentionTest extends HumHubDbTestCase
{

    /**
     * @throws \yii\base\Exception
     */
    public function testPostMention()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 1]);

        $post = new Post(['message' => ' [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")']);
        $post->content->container = $space;
        $post->save();

        ProsemirrorRichText::getProcessor($post->message, $post)->parseMentioning();

        $this->assertHasNotification(Mentioned::class, $post);
        $this->assertMailSent(1, 'Mentioned Notification');
    }

    public function testMentionAuthor()
    {
        $this->becomeUser('User2');

        // Mention Admin in Space 1 (Admin is author of post)
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839c "url")',
            'object_model' => Post::class,
            'object_id' => 7
        ]);

        $comment->save();

        $this->assertHasNotification(Mentioned::class, $comment);

        // We expect only the Mentioned mail
        $this->assertMailSent(1, 'Comment Notification Mail sent');
    }

    public function testMentionNonAuthor()
    {
        $this->becomeUser('User2');

        // Mention User1 in post
        $comment = new Comment([
            'message' => 'Hi [url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839d "url")',
            'object_model' => Post::class,
            'object_id' => 7
        ]);

        $comment->save();

        $this->assertHasNotification(Mentioned::class, $comment);

        // Commented mail for Admin and Mentioned mail for User1
        $this->assertMailSent(2, 'Comment Notification Mail sent');
    }

    /**
     * Admin create a private post, admin mention a non-member user in its comment
     */
    public function testPostAuthorMentionNonMemberInPrivateComment()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'Admin', 0);

        $this->assertHasNoNotification(Mentioned::class, $comment);
        $this->assertHasNoNotification(NewComment::class, $comment);
    }

    /**
     * Admin create a private post, a member mentions a non-member user in its comment
     */
    public function testOtherMemberMentionNonMemberInPrivateComment()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $this->assertHasNoNotification(Mentioned::class, $comment);
        $this->assertHasNoNotification(NewComment::class, $comment, 2, 4);
        $this->assertHasNotification(NewComment::class, $comment, 2, 1);
    }

    /**
     * Admin create a public post, a member mentions a non-member user in its comment
     */
    public function testOtherMemberMentionNonMemberInPublicComment()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 1);

        $this->assertHasNotification(Mentioned::class, $comment, 2, 4);
        $this->assertHasNoNotification(NewComment::class, $comment, 2, 4);
        $this->assertHasNotification(NewComment::class, $comment, 2, 1);
    }

    /**
     * Admin creates a private post, Admin mentions a non-member user in its comment, Admin replies that comment
     */
    public function testMentionInCommentNotificationsCase1()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'Admin', 0);

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment);
    }

    /**
     * Admin creates a private post, Admin mentions a non-member user in its comment, a member replies that comment
     */
    public function testMentionInCommentNotificationsCase2()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'Admin', 0);

        $this->becomeUser('User1');

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment, 2, 4);
        $this->assertHasNotification(NewComment::class, $replyComment, 2, 1);
    }

    /**
     * Admin creates a private post, a member mentions a non-member user in its comment, Admin replies that comment
     */
    public function testMentionInCommentNotificationsCase3()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $this->becomeUser('Admin');

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment, 1, 4);
        $this->assertHasNotification(NewComment::class, $replyComment, 1, 2);
    }

    /**
     * Admin creates a private post, a member mentions a non-member user in its comment,the same member replies that comment
     */
    public function testMentionInCommentNotificationsCase4()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment, 2, 4);
        $this->assertHasNotification(NewComment::class, $replyComment, 2, 1);
    }

    /**
     * Admin creates a private post, a member mentions a non-member user in its comment, another member replies that comment
     */
    public function testMentionInCommentNotificationsCase5()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $this->becomeUser('User2');

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment, 3, 4);
        $this->assertHasNotification(NewComment::class, $replyComment, 3, 1);
        $this->assertHasNotification(NewComment::class, $replyComment, 3, 2);
    }

    /**
     * Admin creates a private post, another member mentions a non-member user in its comment, Admin creates a new comment
     */
    public function testMentionInCommentNotificationsCase6()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $this->becomeUser('Admin');

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->assertHasNoNotification(Mentioned::class, $replyComment);
        $this->assertHasNoNotification(NewComment::class, $replyComment, 1, 4);
        $this->assertHasNotification(NewComment::class, $replyComment, 1, 2);
    }

    /**
     * Admin creates a private post, a member mentions a non-member user in its comment, another member replies that comment
     * Admin changes post visibility to public, admin creates a new comment for it
     */
    public function testMentionInCommentNotificationsCase7()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 0);

        $this->becomeUser('User2');

        $replyComment = new Comment([
            'message' => 'Hey',
            'object_model' => Comment::class,
            'object_id' => $comment->id
        ]);
        $replyComment->save();

        $this->becomeUser('Admin');

        $post = Post::findOne(['id' => $comment->object_id]);
        $post->content->visibility = 1;
        $post->save();

        $comment = new Comment([
            'message' => 'Hi',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $comment->save();

        $this->assertHasNoNotification(Mentioned::class, $comment);
        $this->assertHasNotification(NewComment::class, $comment, 1, 2);
        $this->assertHasNotification(NewComment::class, $comment, 1, 3);
        $this->assertHasNotification(NewComment::class, $comment, 1, 4);
    }

    /**
     * Admin creates a public post, a member mentions a non-member user in its comment
     * Admin changes post visibility to private, admin creates a new comment for it
     */
    public function testMentionInCommentNotificationsCase8()
    {
        $comment = $this->createPostAndMentionNonMemberInItsComment('Admin', 'User1', 1);

        $this->becomeUser('Admin');

        $post = Post::findOne(['id' => $comment->object_id]);
        $post->content->visibility = 0;
        $post->save();

        $comment = new Comment([
            'message' => 'Hi',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $comment->save();

        $this->assertHasNoNotification(Mentioned::class, $comment);
        $this->assertHasNotification(NewComment::class, $comment, 1, 2);
        $this->assertHasNoNotification(NewComment::class, $comment, 1, 4);
    }

    /**
     * @param $postAuthor
     * @param $commentAuthor
     * @param $visibility
     * @return Comment
     */
    public function createPostAndMentionNonMemberInItsComment($postAuthor, $commentAuthor, $visibility)
    {
        $this->becomeUser($postAuthor);
        $space = Space::findOne(['id' => 6]);

        $post = new Post(['message' => 'Post']);
        $post->content->container = $space;
        $post->content->visibility = $visibility;
        $post->save();

        $this->becomeUser($commentAuthor);

        $comment = new Comment([
            'message' => '[url](mention:01e50e0d-82cd-41fc-8b0c-552392f5839f "url")',
            'object_model' => Post::class,
            'object_id' => $post->id
        ]);
        $comment->save();

        return $comment;
    }
}
