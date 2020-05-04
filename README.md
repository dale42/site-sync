# Site Sync

A Robo based utility designed to synchronize website content for MySQL based websites such as Drupal and WordPress. It's intended use is keeping local and development websites up-to-date with the live website. 

## Overview

The utility is installed by placing the phar file in the computer path.

Sites are defined indivually with the _site_ command. The _pair_ command creates a synchronization pair. The _sync_ command is then used synchronize the destination site to source site.

The site and pair definition files are placed in a directory named .robo-site-sync in the account home directory. The site-sync utility has minimal assistance for maintaining these files. They are primarily maintained through a text editor. 

### Example

The example below assumes the site-sync.phar file has not been placed in the system path.

- `php site-sync.phar site create source-site`
- `php site-sync.phar site create destination-site`
- edit the config files
- `php site-sync.phar pair my-sync-pair source-site destination-site`
- edit the config file
- `php site-sync.phar sync my-sync-pair`

## Installation

- Download the site-sync.phar file
- Check if it works:  
  `php site-sync.phar --help`
- To be able to type `site-sync`, instead of `php site-sync.phar`, you need to make the file executable and move it to somewhere in your PATH. For example:  
  `chmod +x site-sync.phar`  
  `mv site-sync.phar ~/bin/site-sync` or `sudo mv site-sync.phar /usr/local/bin/site-sync`
- Test for successful installation:  
  `site-sync --help`

## Development

### Setup

- git clone the repository
- cd into the project root
- run composer install

### Creating a New phar File

- cd to project root
- `composer build`

### Running from the Project

This is useful for testing code changes without recompiling the phar file

- cd to project root
- `php init.php {command}`
  