<?php

namespace Drupal\errorlog\ErrorLogFormatter;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class ErrorLogMessageFormatter implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * A configuration object containin syslog settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Stores whether there is a system logger connection opened or not.
   *
   * @var bool
   */
  protected $connectionOpened = FALSE;

  /**
   * Constructs a SysLog object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The configuration factory object.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(ConfigFactory $config_factory, LogMessageParserInterface $parser) {
    $this->config = $config_factory->get('errorlog.settings');
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    // Do stuff
    if ($this->config->get('emaillog_' . $level)) {
    // Make sure that $log['variables'] is always an array to avoid
    // errors like in issue http://drupal.org/node/1325938
    if (!is_array($log['variables'])) {
      $log['variables'] = array();
    }

    // Send themed alert to the web server's log.
    if (drupal_bootstrap() >= DRUPAL_BOOTSTRAP_FULL) {
      $errorlog_theme_element = array(
        '#theme' => 'errorlog_format',
        '#log' => $params
      );
      $message = render($errorlog_theme_element);;
    }
    // On earlier bootstrap stages not all theme functions are available.
    else {
      $message = theme_errorlog_format($log);
    }
    error_log($message);
  }
  }
}

