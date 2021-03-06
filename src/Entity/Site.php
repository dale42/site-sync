<?php

namespace RoboSiteSync\Entity;

class Site extends Entity {

  /**
   * The domain name of the webserver host. e.g. localhost, example.com
   *
   * @var string
   */
  public $hostDomain;

  /**
   * The ssh user name of account used to access the server
   *
   * @var string
   */
  public $hostUser;

  /**
   * The ssh port number of the domain server.
   *
   * @var string
   */
  public $hostSshPort;

  /**
   * The CMS used by the site. Options: drupal7, drupal8, wordpress
   *
   * @var string
   */
  public $cms;

  /**
   * The directory containing the web project. e.g. If this is a composer project.
   *
   * @var string
   */
  public $projectDir;

  /**
   * The directory the website is served from.
   *
   * @var string
   */
  public $websiteDir;

  /**
   * The files or uploads directory relative to the webroot.
   *
   * @var string
   */
  public $filesDir;

  /**
   * A server directory that can hold backup files.
   *
   * @var string
   */
  public $backupDir;

  /**
   * The website url. e.g.: https://www.example.com
   *
   * @var string
   */
  public $siteUrl;

  /**
   * Examples: dev, stage, local-dev, production
   *
   * @var string
   */
  public $role;

  /**
   * A very short description of the site. e.g.: Sample Project test
   *
   * @var string
   */
  public $description;

  /**
   * Optional notes about the site
   *
   * @var string
   */
  public $notes;

  /**
   * Validate name.
   *
   * Determine if the provided name followings the naming rules.
   *
   * @param string $name
   *
   * @return bool
   */
  public static function isValidName( $name ) {
    return TRUE;
  }

  /**
   * Site constructor.
   *
   * @param array|null $initialData
   */
  public function __construct( array $initialData = NULL ) {
    parent::__construct($initialData);
  }

  public function propertyChecksum( array $propertyList = [] ) {
    if ( count( $propertyList ) > 0 ) {
      $properties = $propertyList;
    }
    else {
      $properties = [
        'projectDir', 'websiteDir', 'backupDir', 'filesDir',
        'host_doman', 'host_user', 'host_ssh_port',
      ];
    }

    return $this->calculateChecksum( $properties );
  }

  public function getFullPath( $property ) {
    $firstChar = substr( $this->{$property}, 0, 1);
    $isRelative = ( $firstChar != '/' && $firstChar != '~' ) ? TRUE : FALSE;

    switch ( $property ) {
      case 'projectDir':
        $fullPath = $this->projectDir;
        break;
      case 'websiteDir':
        $fullPath = ( $isRelative ) ? $this->projectDir .'/'. $this->websiteDir : $this->websiteDir;
        break;
      case "filesDir":
        $fullPath = ( $isRelative ) ? $this->projectDir .'/'. $this->filesDir : $this->filesDir;
        break;
      case 'backupDir':
        $fullPath = ( $isRelative ) ? $this->projectDir .'/'. $this->backupDir : $this->backupDir;
        break;
      default:
        $fullPath = NULL;
    }

    return $fullPath;
  }

}
