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
      $verification = $this->remoteHostSiteTests( $verification, $site );
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

  private function remoteHostSiteTests( array $verification, Site $site ) {
    $result = $this->taskSshExec($site->hostDomain, $site->hostUser)
                   ->port((int) $site->hostSshPort)
                   ->exec('cd ~')->quiet()
                   ->run();
    if ( $result->wasSuccessful() ) {
      $verification['tests']['passed']['sshCredentials'] = 'Good';
    }
    else {
      $verification['tests']['failed']['sshCredentials'] = 'One of the following is incorrect: host_domain, host_user, hostSshPort';
    }

    $properties = ['projectDir', 'websiteDir', 'backupDir', 'filesDir'];

    if ( isset( $verification['tests']['passed']['sshCredentials'] ) ) {
      foreach ($properties as $property) {
        $directory = $site->getFullPath( $property );
        $result = $this->taskSshExec($site->hostDomain, $site->hostUser)
                       ->port((int) $site->hostSshPort)
                       ->exec("cd $directory")->quiet()
                       ->run();
        if ( $result->wasSuccessful() )  {
          $verification['tests']['passed'][$property] = 'Good';
        }
        else {
          $verification['tests']['failed'][$property] = 'Bad';
        }
      }
    }
    else {
      foreach ( $properties as $property ) {
        $verification['tests']['skipped'][$property] = 'Requires working SSH credentials';
      }
    }

    return $verification;
  }

}
