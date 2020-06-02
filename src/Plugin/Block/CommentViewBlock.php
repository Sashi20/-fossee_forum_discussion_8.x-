<?php

namespace Drupal\fossee_forum_discussion\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Fossee Forum Discussion Comment View' Block
 * @Block(
 * id = "fossee_forum_discussion_block1",
 * admin_label = @Translation("Fossee Forum Discussion Comment View Block"),
 * )
 */
class CommentViewBlock extends BlockBase {

	/**
	 * {@inheritdoc}
	 */
	public function build() 
	{
		$current_path = \Drupal::request()->getPathInfo();
		$current_path_splitted = explode('/', $current_path);
    	$count = count($current_path_splitted);
		$ID = $current_path_splitted[$count-1];

		



		$query1 = \Drupal::database()->select('fossee_forum_discussion_comments', 'c');
		$query1->condition('c.forum_id', $ID);
		$query1->fields('c',['comment_id', 'user_name','user_email', 'comment_msg']);
		$results1 = $query1->execute()->fetchAll();

		$query2 = \Drupal::database()->select('fossee_forum_discussion_comment_replies', 'r');
		$query2->condition('r.forum_id', $ID);
		$query2->fields('r',['parent_comment_id', 'user_name','user_email', 'reply_message']);
		$results2 = $query2->execute()->fetchAll();

		$allcomments = array();
		$allreplies  = array();

		foreach($results1 as $result)
		{
			$allcomments[] = array(
				'comment_id' => $result->comment_id,
				'user_name' => $result->user_name,
				'user_email' => $result->user_email,
				'comment_msg' => $result->comment_msg,
			);
		}		
		foreach($results2 as $result)
		{
			$allreplies[] = array(
				'parent_comment_id' => $result->parent_comment_id,
				'user_name' => $result->user_name,
				'user_email' => $result->user_email,
				'reply_msg' => $result->reply_message,
			);
		}

		global $base_url;
		$redirect = $base_url.'/user';
		$status = \Drupal::currentUser()->isAuthenticated();
		$form = \Drupal::formBuilder()->getForm('Drupal\fossee_forum_discussion\Form\ReplyForm');
		return [
			'#theme' => 'commentview',
			'#comment' => $allcomments,
			'#reply' => $allreplies,
			'#form' => $form,
			'#status' => $status,
			'#redirect' => $redirect,
			'#cache' => [
				'max-age' => 0,
			],
		];
	}

	
}



