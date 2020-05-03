<?php


namespace RoboSiteSync\Entity;


class Pair extends Entity {

  /**
   * The source site.
   *
   * @var string
   */
  public $sourceSite;

  /**
   * The destination site.
   *
   * @var string
   */
  public $destinationSite;

  /**
   * Localization tasks.
   *
   * @var string
   */
  public $localizationTasks;

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

  public function __construct(array $initialData = NULL) {
    parent::__construct($initialData);
  }

}