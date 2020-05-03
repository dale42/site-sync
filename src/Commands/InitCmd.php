<?php


namespace RoboSiteSync\Commands;


use RoboSiteSync\Defaults;
use RoboSiteSync\Entity\Datastore;
use RoboSiteSync\Utilities;

class InitCmd extends \Robo\Tasks {

  /**
   * Initialize configuration storage
   */
  public function init() {
    if (Datastore::exists()) {
      $this->say("A configuration directory exists. No action taken.");
      return;
    }

    $configDir = Defaults::getInstance()->getConfigDir();
    $this->say("The directory {$configDir} will be created.");
    $answer = $this->ask("Create [y/n]?");
    if (strtolower($answer) == 'y') {
      $status = mkdir(Defaults::getInstance()->getConfigDir());
      $message = ($status) ? 'Directory created' : 'Could not create directory';
      $this->say($message);
    }
    else {
      $this->say('Directory not created');
    }
  }

}