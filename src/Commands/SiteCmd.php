<?php

namespace RoboSiteSync\Commands;

use Robo\Tasks;
use RoboSiteSync\Entity\Datastore;
use RoboSiteSync\Entity\Site;
use RoboSiteSync\Utilities;

class SiteCmd extends Tasks {

  use SiteVerifyTrait;

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
    if ($sitename == '') {
      $siteList = $this->datastore->getSiteList();
      $maxNameLength = max( array_map( 'strlen', array_keys( $siteList ) ) );
      $output = array_reduce(
        $this->datastore->getSiteList(),
        function($carry, $item) use ( $maxNameLength ) {
          return $carry .= sprintf("   %-{$maxNameLength}s  %s\n", $item->name, $item->description);
        }, "Site List:\n"
      );
      $this->say($output);
    }
    else {
      $site = $this->datastore->getSite($sitename);
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
    $site = $this->datastore->getSite( $sitename );
    $site->verification = $this->checkSiteProperties( $site );
    $this->datastore->saveSite( $site );
    $this->say(print_r( $site->verification, 1));
  }

}
