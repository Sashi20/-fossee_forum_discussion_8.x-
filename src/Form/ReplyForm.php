<?php

namespace Drupal\fossee_forum_discussion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class ReplyForm extends FormBase 
{

  /**
   * {@inheritdoc}
   */
  public function getFormId() 
  {
    return 'fossee_forum_discussion_reply_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) 
  {
    $form['reply_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Enter your reply :'),
        '#attributes' => array(
            'placeholder' => t('Your reply goes here.....'),
          ),
    );
    $form['cid'] = array(
      '#type' => 'hidden'
    );
    // Submit.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reply'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) 
  {
    $config     = \Drupal::config('fossee_forum_discussion.settings');
    $minlength  = $config->get('fossee_forum_discussion_minlength');
    $maxlength  = $config->get('fossee_forum_discussion_maxlength');
    $limit      = $maxlength+1;

    $reply_msg = $form_state->getValue('reply_msg');
    $reply_msg = trim(preg_replace('/\s+/',' ', $reply_msg));
    $current_length = strlen($reply_msg);

    if($current_length < $minlength)
		{
      $form_state->setErrorByName('Comment', t('Please enter a minimum of '.$minlength.' characters.'));
		}
		elseif($current_length > $maxlength)
		{
      $form_state->setErrorByName('Comment', t('Please enter lesss than '.$limit.' characters.'));
		}
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $current_path = \Drupal::request()->getPathInfo();
    $current_path_splitted = explode('/', $current_path);
    $count = count($current_path_splitted);
    $ID = $current_path_splitted[$count-1];

    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $user_email = $user->get('mail')->value;
    $user_name = $user->get('name')->value;
    $user_id = $user->get('uid')->value;

    

    $reply_msg = $form_state->getValue('reply_msg');
    $parent_comment_id = $form_state->getValue('cid');

    db_insert('fossee_forum_discussion_comment_replies')
    ->fields(array(
        'parent_comment_id' => $parent_comment_id,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'user_email' => $user_email,
        'reply_message' => $reply_msg,
        'forum_id' => $ID,
    ))
    ->execute();

    $mailManager = \Drupal::service('plugin.manager.mail');

    $module = 'fossee_forum_discussion';
    $key = 'reply_email_to_author';
    $to = $user_email;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $config = \Drupal::config('fossee_forum_discussion.settings');
    $from = $config->get('fossee_forum_discussion_from_email'); 
    $bcc = $config->get('fossee_forum_discussion_bcc_emails');
    $cc = $config->get('fossee_forum_discussion_cc_emails');
    $params['reply_email_to_author']['From'] = $from;
    $params['reply_email_to_author']['Bcc'] = $bcc;
    $params['reply_email_to_author']['Cc'] = $cc;
    $params['reply_email_to_author']['reply_msg'] = $reply_msg;
    
    $send = true;
    $result1 = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);


    $results = db_query("select user_email from {fossee_forum_discussion_comments} where comment_id='$parent_comment_id'");
		$comment_author_email = "";
		foreach($results as $result)
  	{
  		$comment_author_email.= $result->user_email;
    }
    
    $results = db_query("select distinct user_email from {fossee_forum_discussion_comment_replies} where parent_comment_id='$parent_comment_id'");
	  $output = array();
	  $emails = "";
  	foreach($results as $result)
  	{
  		$output[] = $result->user_email;
  		$emails.= $result->user_email.",";
    }
    $filter = array($comment_author_email.",", $user_email.",");
    $emails = str_replace($filter, '', $emails);
    $emails = substr($emails, 0, -1);
    $emails = $emails.','.$bcc;


    $key = 'reply_email_to_forum_members';
    $to = $comment_author_email;
    $params['reply_email_to_forum_members']['From'] = $from;
    $params['reply_email_to_forum_members']['Bcc'] = $emails;
    $params['reply_email_to_forum_members']['Cc'] = $cc;
    $params['reply_email_to_forum_members']['reply_msg'] = $reply_msg;
    $result2 = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    drupal_set_message("DONE");
  }

}