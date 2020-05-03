<?php


namespace RoboSiteSync\Commands;

use RoboSiteSync\Entity\Datastore;

class SyncCmd extends \Robo\Tasks {

  public function sync( $pairname = '' ) {
    $datastore = new Datastore();
    $syncPair = $datastore->getPair( $pairname );

    $source = $datastore->getSite( $syncPair->sourceSite );
    $dest   = $datastore->getSite( $syncPair->destinationSite );

    $backupFilename = $source->name .'-'. $source->role .'-'. date('Y-m-d') .'.sql.gz';
    $rmtBackupPath  = $source->backupDir .'/'. $backupFilename;

    if (is_null($source)) {
      $this->say("Invalid source: {$syncPair->sourceSite}");
    }
    if (is_null($dest)) {
      $this->say("Invalid destination: {$dest->sourceSite}");
    }

    if ( $source->cms != $dest->cms ) {
      $this->say("Can not sync between different CMSs:\n{$source->name} CMS: {$source->cms} | {$dest->name} CMS: {$dest->cms}");
      return;
    }

    if ($source->cms == 'drupal7') {
      // todo: starting point assuming drupal sites have settings in default dir
      $userToVarCmd = "export USERNAME=`grep \"^[[:space:]]*'username'\" sites/default/settings.php | awk -F \"'\" '{ print $4 }'`";
      $passToVarCmd = "export MYSQL_PWD=`grep \"^[[:space:]]*'password'\" sites/default/settings.php | awk -F \"'\" '{ print $4 }'`";
      $dbToVarCmd = "export DATABASE=`grep \"^[[:space:]]*'database'\" sites/default/settings.php | awk -F \"'\" '{ print $4 }'`";
      $hostToVarCmd = "export HOST=`grep \"^[[:space:]]*'host'\" sites/default/settings.php | awk -F \"'\" '{ print $4 }'`";
    }
    elseif ($source->cms == 'wordpress' || $source->cms == 'wp') {
      $userToVarCmd = "export USERNAME=`grep \"^[[:space:]]*define.*'DB_USER'\" wp-config.php | awk -F \"'\" '{ print $4 }'`";
      $passToVarCmd = "export MYSQL_PWD=`grep \"^[[:space:]]*define.*'DB_PASSWORD'\" wp-config.php | awk -F \"'\" '{ print $4 }'`";
      $dbToVarCmd = "export DATABASE=`grep \"^[[:space:]]*define.*'DB_NAME'\" wp-config.php | awk -F \"'\" '{ print $4 }'`";
      $hostToVarCmd = "export HOST=`grep \"^[[:space:]]*define.*'DB_HOST'\" wp-config.php | awk -F \"'\" '{ print $4 }'`";
    }
    else {
      $this->say("The {$source->cms} is not supported.");
      return;
    }

    $this->taskSshExec($source->hostDomain, $source->hostUser)
         ->port((int) $source->hostSshPort)
         ->exec("cd {$source->websiteDir}")
         ->exec(str_replace("'", "'\\''", $userToVarCmd))
         ->exec(str_replace("'", "'\\''", $passToVarCmd))
         ->exec(str_replace("'", "'\\''", $dbToVarCmd))
         ->exec(str_replace("'", "'\\''", $hostToVarCmd))
         ->exec("mysqldump -u\$USERNAME -h\$HOST \$DATABASE | gzip > $rmtBackupPath")
         ->run();

    $this->taskExec("scp -P{$source->hostSshPort} {$source->hostUser}@{$source->hostDomain}:$rmtBackupPath $dest->backupDir/.")->run();

    $this->taskSshExec($source->hostDomain, $source->hostUser)
         ->port((int) $source->hostSshPort)
         ->exec("rm -v $rmtBackupPath")
         ->run();

    // todo: add dropping database tables
    $this->taskExecStack()
      ->exec("cd {$dest->websiteDir}")
      ->exec($userToVarCmd)
      ->exec($passToVarCmd)
      ->exec($dbToVarCmd)
      ->exec($hostToVarCmd)
      ->exec("gunzip -c {$dest->backupDir}/$backupFilename | mysql -u\$USERNAME -h\$HOSTNAME \$DATABASE")
      ->run();

    if ($source->cms == 'wordpress') {
      $replaceUrl = $this->taskExecStack()
        ->exec("cd {$dest->websiteDir}");
      if (parse_url($source->siteUrl, PHP_URL_SCHEME) != parse_url($dest->siteUrl, PHP_URL_SCHEME)) {
        $replaceUrl->exec("wp search-replace --all-tables {$source->siteUrl} {$dest->siteUrl}");
      }
      $sourceSiteHost = parse_url($source->siteUrl, PHP_URL_HOST);
      $destSiteHost   = parse_url($dest->siteUrl, PHP_URL_HOST);
      $replaceUrl->exec("wp search-replace --all-tables {$sourceSiteHost} {$destSiteHost}");
      $replaceUrl->run();
    }

    $this->taskRsync()
         ->remoteShell("ssh -p {$source->hostSshPort}")
         ->fromPath("{$source->hostUser}@{$source->hostDomain}:{$source->websiteDir}/{$source->filesDir}/")
         ->toPath("{$dest->websiteDir}/{$dest->filesDir}/")
         ->recursive()
         ->progress()
         ->stats()
         ->run();
  }

}
