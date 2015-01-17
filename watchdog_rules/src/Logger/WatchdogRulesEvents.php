<?php

namespace Drupal\watchdog_rules\Logger;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

Class WatchdogRulesEvents implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    foreach ($message as $key => $value) {
      if (is_null($value)) {
        $message[$key] = '';
      }
    }
    #rules_invoke_event() rules API still not stable will
    # need to figure it out to about this function
  }

}
