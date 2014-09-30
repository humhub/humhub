<?php

/**
 * This widget is used to show a single comment.
 *
 * It will used by the CommentsWidget and the CommentController to show comments.
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
class ShowCommentWidget extends HWidget {

	/**
	 * @var Comment object to display
	 */
	public $comment = null;

	/**
	 * Executes the widget.
	 */
	public function run()
	{
		$user = $this->comment->user;
		$this->render('showComment', array(
			'comment' => $this->comment,
			'user' => $user,
		));
	}

}