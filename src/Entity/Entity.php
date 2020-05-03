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

}