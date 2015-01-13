<?php

/**
 * @file
 * Contains \Drupal\watchdog_triggers\Form\LoggingAlertsForm
 */

namespace Drupal\watchdog_triggers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Class LoggingAlertsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'watchdog_triggers_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();
    // Check against the following features of watchdog.
    $form['watchdog_triggers_info'] = array(
      '#type' => 'item',
      '#description' => t('The settings set here apply to all watchdog actions. These are cumulative filters. ' .
      'The more filters you set the narrower your selection of watchdog events will be. If you need more fine ' .
      'control or multiple configurations then you need to upgrade to the Rules integration for Watchdog.'),
    );

    // type (module name)
    $form['watchdog_triggers_type'] = array(
      '#type' => 'textarea',
      '#title' => t('Message type'),
      '#default_value' => $this->config('watchdog_triggers.settings')->get('watchdog_triggers_type'),
      '#description' => t('Enter each type to trigger against, usually the module of origin, separated by a comma.'),
    );

    // user
    $form['watchdog_triggers_user'] = array(
      '#type' => 'textarea',
      '#title' => t('User generating message'),
      '#default_value' => $this->config('watchdog_triggers.settings')->get('watchdog_triggers_user'),
      '#description' => t('Enter each user name to trigger against, separated by a comma.'),
    );

      // request uri
    $form['watchdog_triggers_request_uri'] = array(
      '#type' => 'textarea',
      '#title' => t('Message request uri'),
      '#default_value' => $this->config('watchdog_triggers.settings')->get('watchdog_triggers_request_uri'),
      '#description' => t('Enter each regular expression to match the requesting uri against, separated by a comma.'),
    );

    // referer
    $form['watchdog_triggers_referer'] = array(
      '#type' => 'textarea',
      '#title' => t('Message referer'),
      '#default_value' => $this->config('watchdog_triggers.settings')->get('watchdog_triggers_referer'),
      '#description' => t('Enter each regular expression to match the refering page against, separated by a comma.'),
    );

    // ip
    $form['watchdog_triggers_ip'] = array(
      '#type' => 'textarea',
      '#title' => t('IP generating message'),
      '#default_value' => $this->config('watchdog_triggers.settings')->get('watchdog_triggers_ip'),
      '#description' => t('Enter each regular expression to match the IP against, separated by a comma.'),
    );


    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

