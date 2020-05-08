# Change Log

### This release

* App name changed to SiteSync
* Fix propagation of $directory param in Datastore::AddDatastore
* Added PHPUnit testing and composer package mikey179/vfsstream for file system tests
* Added test for datastore creation
* Added `composer test` script for running tests

### Version 0.2.0 (Sunday May 3, 2020)

* Add CHANGELOG.md file
* Small README.md update
* Set build signature algorithm to SHA512
* Remove _init_ command and automatically creat configuration directory
* Refactor _site_ to use $this->datastore property
* Improve _site list_ output formatting
* Update Datastore constructor docblock
