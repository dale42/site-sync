<?php


namespace RoboSiteSync\Commands;


use RoboSiteSync\Entity\Site;
use RoboSiteSync\Utilities;

trait SiteVerifyTrait {

  /**
   * Check Site Properties
   *
   * Verify site properties required for synchronizations. The verification
   * tests are different for local and remote hosts, since ssh is not required
   * for a local install.
   *
   * Localhost tests:
   *  - Project Directory Exists
   *  - Website Directory Exists
   *  - Files Directory Exists
   *  - Backup Directory Exists
   *
   * Remote host tests:
   *  - Can SSH
   *  - Project Directory Exists
   *  - Website Directory Exists
   *  - Files Directory Exists
   *  - Backup Directory Exists
   *
   * @param $site
   *
   * @return array
   */
  protected function checkSiteProperties( Site $site ) {
    $verification = [
      'date'      => date('c'),
      'checksum'  => $site->propertyChecksum(),
      'status'    => 'Incomplete',
      'tests'     => [
        'passed'  => [],
        'failed'  => [],
        'skipped' => [],
      ]
    ];

    if ($site->hostDomain == 'localhost') {
      $verification = $this->localhostSiteTests( $verification, $site );
    }
    else {
      $verification = $this->remoteHostSiteTests( $site );
    }

    if ( count( $verification['tests']['failed'] ) == 0 ) {
      $verification['status'] = 'Passed';
    }
    else {
      $verification['status'] = 'Failed';
    }

    return $verification;
  }

  private function localhostSiteTests( array $verification, Site $site ) {
    $properties = ['projectDir', 'websiteDir', 'backupDir', 'filesDir'];
    foreach ($properties as $property) {
      if ( Utilities::verifyDirectory( $site->getFullPath( $property ) ) ) {
        $verification['tests']['passed'][$property] = 'Good';
      }
      else {
        $verification['tests']['failed'][$property] = 'Bad';
      }
    }

    return $verification;
  }

  private function remoteHostSiteTests( Site $site ) {
    $dirTask = $this->taskExec('pwd');
    $result = $this->taskSshExec($site->hostDomain, $site->hostUser)
                   ->port((int) $site->hostSshPort)
                   ->exec('ls -alh')->quiet()
                   ->run();
    $status['hostTest']['getMessage'] = $result->getMessage();
    $status['hostTest']['getData'] = $result->getData();
    $status['hostTest']['getOutputData'] = $result->getOutputData();
    $status['hostTest']['getExitCode'] = $result->getExitCode();
    $status['hostTest']['wasSuccessful'] = $result->wasSuccessful();
    $status['hostTest']['getData'] = $result->getData();

    return $status;
  }

}
