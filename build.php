<?php

$dir = dirname(__FILE__);

$exclude = [
    'bin',
    'build',
    'data',
    'docs',
    'docs-api',
    'tests',
    '.gitattributes',
    '.gitignore',
    '.travis.yml',
    'composer.json',
    'composer.json.dev',
    'composer.lock',
    'composer.phar',
    'phpcs.xml',
    'phpdoc.xml',
    'phpunit.xml.dist',
    'README.md',
    'CHANGELOG.md',
    '.git',
    '.idea',
    'build.php',
    'site-sync.phar'
];

$filter = function ($file, $key, $iterator) use ($exclude) {
    if ($iterator->hasChildren() && !in_array($file->getFilename(), $exclude)) {
        return true;
    }
    return $file->isFile() && !in_array($file->getFilename(), $exclude);
};

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        $filter
    )
);

$phar = new Phar("sitesync.phar");
$phar->setSignatureAlgorithm(\Phar::SHA512);
$phar->startBuffering();
$phar->buildFromIterator($iterator, $dir);
//default executable
$phar->setStub(
    "#!/usr/bin/php \n" . $phar->createDefaultStub('init.php')
);
$phar->stopBuffering();