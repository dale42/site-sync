<?php


namespace RoboSiteSync\Entity;


class Entity {

  /**
   * The name used to reference this.
   *
   * @var string;
   */
  public $name;

  /**
   * Used to track the verification status of important object properties.
   *
   * @var string;
   */
  public $verification;

  /**
   * Site constructor.
   *
   * @param array|null $initialData
   */
  public function __construct( array $initialData = NULL ) {
    if (!is_null($initialData)) {
      foreach ($initialData as $propName => $value) {
        if (property_exists($this, $propName)) {
          $this->{$propName} = $value;
        }
      }
    }
  }

  public function toArray() {
    $properties = [];
    $class = new \ReflectionClass($this);
    foreach ($class->getProperties() as $property) {
      $properties[$property->getName()] = $this->{$property->getName()};
    }
    // Make sure 'name' is always first.
    $name = ['name' => $properties['name']];
    unset($properties['name']);
    $properties = array_merge($name, $properties);
    return $properties;
  }

  public function toPrint() {
    return print_r($this->toArray(), 1);
  }

  protected function calculateChecksum( array $propertyList ) {
    asort( $propertyList );
    $propertyString = '';
    foreach ( $propertyList as $property ) {
      $propertyString .= $this->{$property} ?? '';
    }

    return hash( 'sha256', $propertyString );
  }

}
