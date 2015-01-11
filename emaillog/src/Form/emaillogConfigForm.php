<?php

/**
 * @file
 * Contains \Drupal\emaillog\Form\emaillogConfigForm.
 */

namespace Drupal\emaillog\Form;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

class emaillogConfigForm extends ConfigFormBase {
  public function getFormId() {
    return 'email_log_config_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $severity_levels = RfcLogLevel::getLevels();

    $form['emaillog'] = array(
      '#type'           => 'fieldset',
      '#title'          => t('Email addresses for each severity level'),
      '#description'    => t('Enter an email address for each severity level. For example, you may want to get emergency and critical levels to your pager or mobile phone, while notice level messages can go to a regular email. If you leave the email address blank for a severity level, no email messages will be sent for that severity level.'),
      '#collapsible'    => TRUE,
      '#collapsed'      => FALSE,
    );
    foreach ($severity_levels as $severity => $level) {
      $key = 'emaillog_' . $severity;
      $form['emaillog'][$key] = array(
        '#type'           => 'textfield',
        '#title'          => t('Email address for severity %description', array('%description' => Unicode::ucfirst($level->render()))),
        '#default_value'  => $this->config('emaillog.settings')->get($key),
        '#description'    => t('The email address to send log entries of severity %description to.', array('%description' => Unicode::ucfirst($level->render()))),
      );
    }

    $form['debug_info'] = array(
      '#type'           => 'fieldset',
      '#title'          => t('Additional debug info'),
      '#description'    => t('Additional debug information that should be attached to email alerts. Note that this information could be altered by other modules using <em>hook_emaillog_debug_info_alter(&$debug_info)</em>'),
      '#collapsible'    => TRUE,
      '#collapsed'      => TRUE,
      '#tree'           => TRUE,
    );
    $debug_info_settings = $this->config('emaillog.settings')->get('emaillog_debug_info');
    $status = array();

    foreach (_emaillog_get_debug_info_callbacks() as $debug_info_key => $debug_info_callback) {
      $options[$debug_info_key] = '';
      $form['debug_info']['variable'][$debug_info_key] = array(
        '#type' => 'item',
        '#markup' => $debug_info_callback,
      );
      foreach (array_keys($severity_levels) as $level_id) {
        // Builds arrays for checked boxes for each role
        if (!empty($debug_info_settings[$level_id][$debug_info_key])) {
          $status[$level_id][] = $debug_info_key;
        }
      }
    }
    foreach ($severity_levels as $level_id => $description) {
      $form['debug_info'][$level_id] = array(
        '#title' => String::checkPlain($description),
        '#type' => 'checkboxes',
        '#options' => $options,
        '#default_value' => isset($status[$level_id]) ? $status[$level_id] : array(),
      );
    }
    $form['debug_info']['emaillog_backtrace_replace_args'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Replace debug_backtrace() argument values with types'),
      '#description'    => t('By default <em>debug_backtrace()</em> will return full variable information in the stack traces that it produces. Variable information can take quite a bit of resources, both while collecting and adding to the alert email, therefore here by default all variable values are replaced with their types only. Warning - unchecking this option could cause your site to crash when it tries to send an alert email with too big stack trace!'),
      '#default_value'  => $this->config('emaillog.settings')->get('emaillog_backtrace_replace_args'),
      '#weight'         => 1,
    );
    $form['limits'] = array(
      '#type' => 'fieldset',
      '#title'          => t('Email sending limits'),
      '#collapsible'    => TRUE,
      '#collapsed'      => TRUE,
    );
    $form['limits']['emaillog_max_similar_emails'] = array(
      '#type'           => 'textfield',
      '#title'          => t('Maximum number of allowed consecutive similar email alerts'),
      '#description'    => t('Upper limit of email alerts sent consecutively with the same or very similar message. Leave empty for no limit.'),
      '#default_value'  => $this->config('emaillog.settings')->get('emaillog_max_similar_emails'),
    );
    $form['limits']['emaillog_max_consecutive_timespan'] = array(
      '#type'           => 'textfield',
      '#title'          => t('Email alerts should be considered "consecutive" if sent within'),
      '#field_suffix'   => t('minutes from each other'),
      '#description'    => t('Longest possible period between two email alerts being sent to still be considered consecutive. Leave empty for no limit.'),
      '#default_value'  => $this->config('emaillog.settings')->get('emaillog_max_consecutive_timespan'),
    );
    $form['limits']['emaillog_max_similarity_level'] = array(
      '#type'           => 'textfield',
      '#title'          => t('Maximum allowed similarity level between consecutive email alerts'),
      '#description'    => '<p>' . t('Highest similarity level above which new email alerts will not be sent anymore if "Maximum number of allowed consecutive similar email alerts" has been reached and email alerts are considered "consecutive" (time period between each previous and next one is smaller than defined above). Possible values range from 0 to 1, where 1 stands for two identical emails.') . '</p>'
        . '<p>' . t('For example setting "Maximum number of allowed consecutive similar email alerts" to 5, "Email alerts should be considered consecutive if sent within" to 5 minutes and "Similarity level" to 0.9 would mean that only 5 email alerts would be sent within 5 minutes if Watchdog entries are similar in at least 90%.') . '</p>'
        . '<p>' . t("(Note that similarity level is calculated using PHP's <a href='@similar_text_url'>similar_text()</a> function, with all its complexity and implications.)", array('@similar_text_url' => Url::fromUri('http://php.net/similar_text'))) . '</p>',
      '#default_value'  => $this->config('emaillog.settings')->get('emaillog_max_similarity_level'),
    );

    $form['legacy'] = array(
      '#type' => 'fieldset',
      '#title'          => t('Legacy settings'),
      '#collapsible'    => TRUE,
      '#collapsed'      => TRUE,
    );

    $form['legacy']['emaillog_legacy_subject'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Use legacy email subject'),
      '#description'    => t('Older versions of this module were using email subject "%subject", while currently it is being set to beginning of Watchdog message. This option allows to switch back to previous version of email subject.', array(
        '%subject'        => t('[@site_name] @severity_desc: Alert from your web site'),
      )),
      '#default_value'  => $this->config('emaillog.settings')->get('emaillog_legacy_subject'),
    );

    $form['#theme'] = 'emaillog_admin_settings';
    return $form;
//    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $userInputValues = $form_state->getUserInput();
    $config = $this->configFactory->get('emaillog.settings');

    $config->save();
    parent::submitForm($form, $form_state);
  }
}
