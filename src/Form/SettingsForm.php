<?php  

/**  
 * @file  
 * Contains Drupal\fossee_forum_discussion\Form\MessagesForm.  
 */  

namespace Drupal\fossee_forum_discussion\Form;  

use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class SettingsForm extends ConfigFormBase {  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'fossee_forum_discussion.settings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId()
  {
    return 'fossee_forum_discussion_settings_form';  
  }  

  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('fossee_forum_discussion.settings');  

    $form['fossee_forum_discussion_minlength'] = array(
        '#type' => 'textfield',
        '#title' => t('Minimum length for comment message :'),
        '#default_value' => $config->get('fossee_forum_discussion_minlength'),
        '#required' => TRUE,
      );
    $form['fossee_forum_discussion_maxlength'] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum length for comment message :'),
      '#value' => $config->get('fossee_forum_discussion_maxlength'),
      '#default_value' => 20,
      '#required' => TRUE,
    );
    $form['fossee_forum_discussion_from_email'] = array(
      '#type' => 'textfield',
      '#title' => t('Senders Email ID:'),
      '#description' => t('Enter only one Email ID.'),
      '#default_value' => $config->get('fossee_forum_discussion_from_email'),
      '#required' => TRUE,
    );
    $form['fossee_forum_discussion_bcc_emails'] = array(
      '#type' => 'textfield',
      '#title' => t('Emails to recieve BCC :'),
      '#description' => t('Enter comma separated Emails IDs.'),
      '#default_value' => $config->get('fossee_forum_discussion_bcc_emails'),
      '#required' => TRUE,
    );
    $form['fossee_forum_discussion_cc_emails'] = array(
      '#type' => 'textfield',
      '#title' => t('Emails to recieve CC :'),
      '#description' => t('Enter comma separated Emails IDs.'),
      '#default_value' => $config->get('fossee_forum_discussion_cc_emails'),
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);  
  }  

  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  

    $this->config('fossee_forum_discussion.settings')  
      ->set('fossee_forum_discussion_minlength', $form_state->getValue('fossee_forum_discussion_minlength'))
      ->set('fossee_forum_discussion_maxlength', $form_state->getValue('fossee_forum_discussion_maxlength'))
      ->set('fossee_forum_discussion_from_email', $form_state->getValue('fossee_forum_discussion_from_email'))
      ->set('fossee_forum_discussion_bcc_emails', $form_state->getValue('fossee_forum_discussion_bcc_emails'))
      ->set('fossee_forum_discussion_cc_emails', $form_state->getValue('fossee_forum_discussion_cc_emails'))
      ->save();  
  }  



}  