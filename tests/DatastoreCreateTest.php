<?php

declare(strict_types=1);

namespace RoboSiteSync\Tests;

use org\bovigo\vfs\vfsStream;

use PHPUnit\Framework\TestCase;
use RoboSiteSync\Entity\Datastore;

class DatastoreCreateTest extends TestCase {

  private $vfsRoot;

  public function setUp(): void {
    $this->vfsRoot = vfsStream::setup();
  }


  public function testCreateDatastore() {

    $datastore = new Datastore( $this->vfsRoot->url() . '/sitesync' );
    $this->assertTrue($this->vfsRoot->hasChild('sitesync'));

  }

}