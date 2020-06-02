<?php

namespace Drupal\fossee_forum_discussion\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Fossee Forum Discussion Comment Form' Block
 * @Block(
 * id = "fossee_forum_discussion_block2",
 * admin_label = @Translation("Fossee Forum Discussion Comment Form Block"),
 * )
 */
class CommentFormBlock extends BlockBase {

	/**
	 * {@inheritdoc}
	 */
	public function build() {

		$form = \Drupal::formBuilder()->getForm('Drupal\fossee_forum_discussion\Form\CommentForm');

		return $form;
	}
	
}



