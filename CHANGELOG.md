# Change Log

### Version 0.3.0 (Sunday May 10, 2020)

* App name changed to SiteSync in init.php
* Fix propagation of $directory param in Datastore::AddDatastore
* Added PHPUnit testing and composer package mikey179/vfsstream for file system tests
* Added test for datastore creation
* Added `composer test` script for running tests
* Refactor $datastore handling in _pair_ command
* On Mac, automatically open pair yml file on `sitesync pair create {pair-name}`
* Added a working `post_sync_tasks` functionality. Tasks specified in the pair file `post_sync_tasks` option are ran after the site is sync'ed.
* `sitesync site verify {site}` now has a basic implimentation, not a placeholder
* `Datestore->siteSave()` preserves comment changes in site yml file

### Version 0.2.0 (Sunday May 3, 2020)

* Add CHANGELOG.md file
* Small README.md update
* Set build signature algorithm to SHA512
* Remove _init_ command and automatically creat configuration directory
* Refactor _site_ to use $this->datastore property
* Improve _site list_ output formatting
* Update Datastore constructor docblock
