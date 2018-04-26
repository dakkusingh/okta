<?php

namespace Drupal\okta_import\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ValidateEvent
 *
 * @package Drupal\okta_import\Event
 */
class ValidateEvent extends Event {

  const OKTA_IMPORT_VALIDATE = 'okta_import.validate';

  protected $indexConfig;
  protected $indexName;

  /**
   * PrepareIndexEvent constructor.
   *
   * @param $indexConfig
   * @param $indexName
   */
  public function __construct($indexConfig, $indexName) {
    $this->indexConfig = $indexConfig;
    $this->indexName = $indexName;
  }

  /**
   * Getter for the index config array.
   *
   * @return indexConfig
   */
  public function getIndexConfig() {
    return $this->indexConfig;
  }

  /**
   * Setter for the index config array.
   *
   * @param $indexConfig
   */
  public function setIndexConfig($indexConfig) {
    $this->indexConfig = $indexConfig;
  }

  /**
   * Getter for the index name.
   *
   * @return indexName
   */
  public function getIndexName() {
    return $this->indexName;
  }
}