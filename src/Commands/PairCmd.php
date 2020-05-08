<?php

namespace RoboSiteSync\Commands;

use Robo\Tasks;
use RoboSiteSync\Entity\Datastore;
use RoboSiteSync\Entity\Pair;
use RoboSiteSync\Utilities;

class PairCmd extends Tasks {

  public $validActions = [
    'list',
    'create',
    'delete',
  ];

  protected $datastore;

  public function __construct() {
    $this->datastore = new Datastore();
  }

  /**
   * Pair management
   *
   * Manage the configuration of sites used.
   *
   * @param $action list | create | delete
   * @param string $pairname
   */
  public function pair( $action = '', $pairname = '', $opts = ['prompt|p' => false] ) {
    /*
     * Validation
     */
    if (!Datastore::exists()) {
      $this->say("No configuration directory. Please use init to create.");
      return;
    }
    if ( empty($action) ) {
      $this->say('Please specific an action: ' . implode(' | ', $this->validActions));
      return;
    }

    /*
     * Action Dispatch
     */
    switch ( $action ) {
      case 'list':
        $this->listPair( $pairname, $opts );
        break;
      case 'create':
        $this->createPair( $pairname, $opts );
        break;
      case 'delete':
        $this->deletePair( $pairname, $opts );
        break;
      default:
        $this->say("'$action' is not a valid action.\nValid actions: " . implode(' | ', $this->validActions));
    }
  }

  protected function listPair( $pairName, $opts ) {
    if ($pairName == '') {
      $output = array_reduce($this->datastore->getPairList(),
        function($carry, $item) {
          return $carry .= "{$item->name} ({$item->description})\n";
        }, "Pair List:\n"
      );
      $this->say($output);
    }
    else {
      $pair = $this->datastore->getPair($pairName);
      $output = (is_null($pair)) ? "{$pairName} does not exist" : $pair->toPrint();
      $this->say($output);
    }
  }

  protected function createPair($pairName, $opts) {
    //todo: add logic to validate name safe for file system
    $initialData = [
      'name' => $pairName,
    ];
    if ($opts['prompt']) {
      $initialData = Utilities::promptForProperties(Pair::class, $pairName);
    }
    $this->datastore->savePair(new Pair($initialData));
    $saveFilePath = $this->datastore->getPairConfigPath( $pairName );
    $this->say("Configuration file created in $saveFilePath" );
    if ( php_uname('s') == 'Darwin') {
      $this->taskExec("open $saveFilePath")->run();
    }
  }

  protected function deletePair($pairName, $options) {
    $this->datastore->deletePair( $pairName );
  }

}
