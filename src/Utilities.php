<?php

namespace RoboSiteSync;

class Utilities {

  public static function homeDirectory() {
    $home = getenv('HOME');
    if (empty($home)) {
      if (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
        // home on windows
        $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
      }
    }
    return empty($home) ? NULL : $home;
  }

  public static function promptForProperties( $entityClassName, $itemName = '' ) {
    $properties = [
      'name' => $itemName,
    ];

    // Iterate over all the property classes.
    $classProperties = (new \ReflectionClass( $entityClassName ))->getProperties();
    foreach ($classProperties as $property) {
      // Skip asking the schemaVersion and name if it is already set.
      if (
        ($property->getName() == 'name' && $properties['name'] != '')
        || $property->getName() == 'schemaVersion'
      ) {
        continue;
      }

      // Grab and format the description.
      $description = explode("\n", $property->getDocComment())[1];
      $description = trim(substr($description, stripos($description, '*') + 1));
      $displayProperty = ucfirst(strtolower(implode(' ', preg_split('/(?=[A-Z])/', $property->getName()))));

      // Ask the question.
      print "\n$description\n";
      $answer = readline($displayProperty . ': ');
      $properties[$property->getName()] = $answer;
    }

    return $properties;
  }

  public static function verifyDirectory( $filepath ) {
    $status = ( file_exists($filepath) && is_dir($filepath) && is_readable($filepath) && is_writeable($filepath) );
    return $status;
  }

  public static function fetchPropertyDescriptions( $entityClassName ) {
    $descriptions = [];
    $classProperties = (new \ReflectionClass( $entityClassName ))->getProperties();
    foreach ( $classProperties as $property ) {
      $name = $property->getName();
      $docComment = explode("\n", $property->getDocComment())[1] ?? '';
      $description = trim(substr($docComment, stripos($docComment, '*') + 1));
      $descriptions[$name] = $description;
    }
    return $descriptions;
  }

}
