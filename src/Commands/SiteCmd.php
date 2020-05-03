<?php

namespace RoboSiteSync\Commands;

use Robo\Tasks;
use RoboSiteSync\Entity\Datastore;
use RoboSiteSync\Entity\Site;
use RoboSiteSync\Utilities;

class SiteCmd extends Tasks {

  public $validActions = [
    'list',
    'create',
    'delete',
    'verify',
  ];

  protected $datastore;

  public function __construct() {
    $this->datastore = new Datastore();
  }

  /**
   * Site management
   *
   * Manage the configuration of sites used.
   *
   * @param $action list | create | delete | verify
   * @param string $sitename
   */
  public function site( $action = '', $sitename = '', $opts = ['prompt|p' => false] ) {
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
        $this->siteList($sitename, $opts);
        break;
      case 'create':
        $this->createSite( $sitename, $opts );
        break;
      case 'delete':
        $this->deleteSite( $sitename, $opts );
        break;
      case 'verify':
        $this->verifySite( $sitename, $opts );
        break;
      default:
        $this->say("'$action' is not a valid action.\nValid actions: " . implode(' | ', $this->validActions));
    }
  }

  protected function siteList($sitename, $opts) {
    $datastore = new Datastore();
    if ($sitename == '') {
      $output = array_reduce($datastore->getSiteList(),
        function($carry, $item) {
          return $carry .= "{$item->name} ({$item->description})\n";
        }, "Site List:\n"
      );
      $this->say($output);
    }
    else {
      $site = $datastore->getSite($sitename);
      $output = (is_null($site)) ? "$site does not exist" : $site->toPrint();
      $this->say($output);
    }
  }


  protected function createSite($sitename, $opts) {
    //todo: add logic to make name safe for file system
    $initialData = [
      'name' => $sitename,
    ];
    if ($opts['prompt']) {
      $initialData = Utilities::promptForProperties(Site::class, $sitename);
    }
    $this->datastore->saveSite( new Site( $initialData ) );
    $saveFilePath = $this->datastore->getSiteConfigPath( $sitename );
    $this->say("Configuration file created in $saveFilePath" );
    if ( php_uname('s') == 'Darwin') {
      $this->taskExec("open $saveFilePath")->run();
    }
  }

  protected function deleteSite( $sitename, $opts ) {
    $this->datastore->deleteSite( $sitename );
  }

  protected function verifySite( $sitename, $opts ) {
    $status = [];
    $site = $this->datastore->getSite( $sitename );

    if ($site->hostDomain == 'localhost') {
      // Test that directories exist
      $status['projectDir'] =  Utilities::verifyDirectory( $site->projectDir );
      $status['websiteDir'] =  Utilities::verifyDirectory( $site->websiteDir );
      $status['backupDir'] =  Utilities::verifyDirectory( $site->backupDir );
    }
    else {
      $dirTask = $this->taskExec('pwd');
      $result = $this->taskSshExec($site->hostDomain, $site->hostUser)
        ->port((int) $site->hostSshPort)
        ->exec('ls -alh')->quiet()
        ->run();
      $status['hostTest']['getMessage'] = $result->getMessage();
      $status['hostTest']['getData'] = $result->getData();
      $status['hostTest']['getOutputData'] = $result->getOutputData();
      $status['hostTest']['getExitCode'] = $result->getExitCode();
      $status['hostTest']['wasSuccessful'] = $result->wasSuccessful();
      $status['hostTest']['getData'] = $result->getData();
    }

    $this->say(print_r($status, 1));
  }

}
