<?php

namespace RoboSiteSync\Entity;

use Robo\Exception\TaskException;
use Robo\ResultData;
use RoboSiteSync\Defaults;
use RoboSiteSync\Utilities;
use Symfony\Component\Yaml\Yaml;

class Datastore {

  /**
   * DataStore Directory
   * @var string
   */
  private $directory;

  public static function filenameToSitename( $filename ) {
    return substr( pathinfo($filename, PATHINFO_FILENAME), 5 );
  }

  public static function filenameToPairName( $filename ) {
    return substr( pathinfo($filename, PATHINFO_FILENAME), 5 );
  }

  public static function sitenameToFilename( $sitename ) {
    return 'site-' . $sitename . '.yml';
  }

  public static function pairNameToFilename( $pairName ) {
    return 'pair-' . $pairName . '.yml';
  }

  public static function exists( $directory = NULL ) {
    if ( is_null( $directory ) ) {
      $directory = Defaults::getInstance()->getConfigDir();
    }
    return (
      file_exists($directory)
      && is_dir($directory)
      && is_readable($directory)
      && is_writeable($directory)
    );
  }

  public static function addDatastore( $directory = NULL ) {
    if ( Datastore::exists() ) {
      return new ResultData( ResultData::EXITCODE_OK, "Configuration directory exists" );
    }

    if ( is_null( $directory ) ) {
      $directory = Defaults::getInstance()->getConfigDir();
    }

    // Suppress the error handler with our own to grab the error message.
    $errorMessage = '';
    set_error_handler(
      function ($errno, $errstr, $errfile, $errline) use (&$errorMessage) {
        $errorMessage = $errstr;
        return TRUE;
      },
      E_ALL | E_STRICT
    );

    // Create the configuration directory.
    if ( @mkdir( $directory ) ) {
      $result = new ResultData( ResultData::EXITCODE_OK, "Configuration directory: {$directory}, created" );
    }
    else {
      $result = new ResultData( ResultData::EXITCODE_ERROR, "Could not create configuration directory: $errorMessage" );
    }

    // Restore the previous error handler.
    restore_error_handler();

    return $result;
  }

  /**
   * Datastore constructor.
   *
   * @param null $directory
   *
   * @throws \Robo\Exception\TaskException
   */
  public function __construct( $directory = NULL ) {
    if ( is_null( $directory ) ) {
      $this->directory = Defaults::getInstance()->getConfigDir();
    }
    else {
      $this->directory = $directory;
    }

    if ( ! self::exists( $directory ) ) {
      $result = self::addDatastore( $directory );
      if ( ! $result->wasSuccessful() ) {
        throw new TaskException( $this, $result->getMessage() );
      }
    }
  }

  /**
   * @param $sitename
   *
   * @return \RoboSiteSync\Entity\Site|null
   */
  public function getSite( $sitename ) {
    $yamlFilename = $this->directory . '/' . self::sitenameToFilename($sitename);
    if (file_exists($yamlFilename)) {
      return new Site($this->loadData($yamlFilename));
    }
    else {
      return NULL;
    }
  }

  public function getSiteConfigPath( $sitename ) {
    return $yamlFilename = $this->directory . '/' . self::sitenameToFilename($sitename);
  }

  public function saveSite( Site $site ) {
    // The dumper can not add yaml comments. To work around this, add a token
    // value that can be replaced with the comment.
    $propertiesPlusTokens = [];
    foreach ($site->toArray() as $key => $value) {
      $propertiesPlusTokens["{$key}_description"] = '';
      $propertiesPlusTokens[$key] = $value;
    }

    $storageString = Yaml::dump( $this->keysToSnakeCase( $propertiesPlusTokens ) );

    // Replace tokens with comments
    $descriptions = $this->keysToSnakeCase( Utilities::fetchPropertyDescriptions( Site::class ) );
    foreach ( $descriptions as $name => $description ) {
      $storageString = str_replace("{$name}_description: ''", "\n# $description", $storageString);
    }
    $storageString = trim( $storageString );

    $filepath = $this->directory . '/' . self::sitenameToFilename($site->name);
    file_put_contents( $filepath, $storageString );
  }

  public function deleteSite( $sitename ) {
    $filepath = $this->directory . '/' . self::sitenameToFilename($sitename);
    unlink( $filepath );
  }

  /**
   * Get Site List
   *
   * @return array
   */
  public function getSiteList() {
    $siteList = [];
    foreach (glob($this->directory . '/site-*.yml') as $filename) {
      $siteList[self::filenameToSitename($filename)] = new Site($this->loadData($filename));
    }

    return $siteList;
  }

  /**
   * Save sync pair object to the datastore.
   *
   * @param \RoboSiteSync\Entity\Pair $pair
   */
  public function savePair( Pair $pair ) {
    $filepath = $this->directory . '/' . self::pairNameToFilename( $pair->name );
    $storageString = Yaml::dump( $this->keysToSnakeCase( $pair->toArray() ) );
    file_put_contents( $filepath, $storageString );
  }

  public function getPair( $pairName ) {
    $yamlFilename = $this->directory . '/' . self::pairNameToFilename($pairName);
    if (file_exists($yamlFilename)) {
      return new Pair($this->loadData($yamlFilename));
    }
    else {
      return NULL;
    }
  }
  public function deletePair( $pairName ) {
    $filepath = $this->directory . '/' . self::pairNameToFilename($pairName);
    unlink( $filepath );
  }

  /**
   * Get Pair List
   *
   * @return array
   */
  public function getPairList() {
    $pairList = [];
    foreach (glob($this->directory . '/pair-*.yml') as $yamlFilename) {
      $pairList[self::filenameToPairName($yamlFilename)] = new Pair($this->loadData($yamlFilename));
    }

    return $pairList;
  }

  private function loadData( $yamlFilename ) {
    $yamlString = file_get_contents($yamlFilename);
    $dataArray = Yaml::parse( $yamlString );
    return $this->keysToCamelCase( $dataArray );
  }

  private function keysToSnakeCase( array $propertyArray ) {
    $snakeCase = [];
    foreach ($propertyArray as $name => $value) {
      $snakeCase[$this->camelToSnakeCase($name)] = $value;
    }
    return $snakeCase;
  }

  private function keysToCamelCase( array $propertyArray ) {
    $camelCase = [];
    foreach ($propertyArray as $name => $value) {
      $camelCase[$this->snakeToCamelCase($name)] = $value;
    }
    return $camelCase;
  }

  private function snakeToCamelCase( $snakeCase ) {
    $camelCase = '';
    $parts = explode('_', $snakeCase);
    foreach ($parts as $index => $part) {
      if ($index > 0) {
        $part = ucfirst($part);
      }
      $camelCase .= $part;
    }
    return $camelCase;
  }

  private function camelToSnakeCase( $camelCase ) {
    return strtolower(implode('_', preg_split('/(?=[A-Z])/', $camelCase)));
  }
}
