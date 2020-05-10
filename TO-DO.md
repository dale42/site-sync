# To-Do List

- `Datastore->siteSave`: Preserve order of yaml properties

- `Datastore->pairSave`:
  - Preserve comment changes on pair yml file
  - Preserve order of yaml properties in pair yml file

- Support multiple file directory entries for Site Class for fileDir. i.e. to support Drupal public and private files systems

- Improve _site verify_ command:
  - check that project path is not relative
  - check cms bits are where they're expected

- Implement _pair verify_ command  
  - source and dest sites exist
  - source and dest sites are validated
  - check post_sync_tasks
  - check that destination is local

- Add check to `sync {pair}` preventing a run if destination not local

- `sync {pair}`: - Run verification if yml files changed since last verification (compare saved checksum to current checksome)

- Add better annotation parsing
  - Proper annotation syntax in docblock
  - Saved Property designation

- Add schema attribute to yaml file

- Add yaml file data checksum for determining if data has changed
  - Use annotation to designate which parameters should be validated
  - On _sync_ re-run verify when a data change is detected

- Figure out licensing

- Add confirmation to `site delete`

- Add confirmation to `pair delete`

- Schema version to site and pair yml files

- `sync {pair}`:
  - uncommitted file check warning

- add confirmation to site delete

- add confirmation to pair delete

- add logic to verify site name is valid

- add logic to verify pair name is valid