<?php

namespace Drupal\fossee_forum_discussion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CommentForm extends FormBase 
{

  /**
   * {@inheritdoc}
   */
  public function getFormId() 
  {
    return 'fossee_forum_discussion_comment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) 
  {
    $logged_in = \Drupal::currentUser()->isAuthenticated();

    if($logged_in)
    {
      $form['comment_msg'] = array(
        '#type' => 'textarea',
        '#title' => t('Enter your comment :'),
        '#attributes' => array(
            'placeholder' => t('Your comment goes here.....'),
          ),
        '#ajax' => array(
          'callback' => array($this, 'checkCountAjax'),
          'event' => 'change',
      		'wrapper' => 'count-div',
      		'method' => 'replace',
      		'effect' => 'fade',
    		),
      );
      $form['count'] = array(
        '#type' => 'textfield',
        '#title' => t('Count :'),
    		'#default_value' => 0,
        '#value' => strlen(trim(preg_replace('/\s+/',' ', $form_state->getValue('comment_msg')))),
    		'#prefix' => '<div id="count-div">',
    		'#suffix' => '</div>',
    		'#attributes' => array('readonly' => 'readonly'),
    	);

      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Post'),
      );
    }

    else
    {
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Add a Comment'),
      );
    }

    return $form;
  }
  public function checkCountAjax(array &$form, FormStateInterface $form_state)
  {
    return $form['count'];
  }
  public function validateForm(array &$form, FormStateInterface $form_state) 
  {
    $logged_in = \Drupal::currentUser()->isAuthenticated();

    if($logged_in)
    {
      $config     = \Drupal::config('fossee_forum_discussion.settings');
      $minlength  = $config->get('fossee_forum_discussion_minlength');
      $maxlength  = $config->get('fossee_forum_discussion_maxlength');
      $limit      = $maxlength+1;

      $comment_msg = $form_state->getValue('comment_msg');
      $comment_msg = trim(preg_replace('/\s+/',' ', $comment_msg));
      $current_length = strlen($comment_msg);

      if($current_length < $minlength)
		  {
        $form_state->setErrorByName('Comment', t('Please enter a minimum of '.$minlength.' characters.'));
		  }
		  elseif($current_length > $maxlength)
		  {
        $form_state->setErrorByName('Comment', t('Please enter lesss than '.$limit.' characters.'));
		  }
    }
    elseif(!$logged_in)
    {
      $form_state->setErrorByName('Comment', t('You are not logged in.'));
      $response = new RedirectResponse(\Drupal::url('user.page'));
      $response->send();
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

    $comment_msg = $form_state->getValue('comment_msg');

    db_insert('fossee_forum_discussion_comments')
    ->fields(array(
        'user_id'     => $user_id,
        'user_name'   => $user_name,
        'user_email'  => $user_email,
        'comment_msg' => $comment_msg,
        'forum_id'    => $ID,
    ))
    ->execute();

    $mailManager = \Drupal::service('plugin.manager.mail');

    $module = 'fossee_forum_discussion';
    $key = 'comment_email_to_author';
    $to = $user_email;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $config = \Drupal::config('fossee_forum_discussion.settings');
    $from = $config->get('fossee_forum_discussion_from_email'); 
    $bcc = $config->get('fossee_forum_discussion_bcc_emails');
    $cc = $config->get('fossee_forum_discussion_cc_emails');
    $params['comment_email_to_author']['From'] = $from;
    $params['comment_email_to_author']['Bcc'] = $bcc;
    $params['comment_email_to_author']['Cc'] = $cc;
    $params['comment_email_to_author']['comment_msg'] = $comment_msg;
    
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    drupal_set_message("DONE");
  }

}