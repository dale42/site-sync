<?php


namespace RoboSiteSync;

/**
 * Class Defaults
 *
 * A single source of authority for default values.
 *
 * @package RoboSiteSync
 */
class Defaults {

  private static $instance = null;

  protected $configDirName = '.robo-site-sync';

  private function __construct() {
  }

  public static function getInstance() {
    if (self::$instance == null) {
      self::$instance = new Defaults();
    }

    return self::$instance;
  }

  public function getConfigParentDir() {
    return Utilities::homeDirectory();
  }

  public function getConfigDirName() {
    return $this->configDirName;
  }

  public function getConfigDir() {
    return $this->getConfigParentDir() . '/' . $this->getConfigDirName();
  }

}
