<!--
EXTENSION_NAME = remoteProctoring
REPOSITORY_NAME = extension-remote-proctoring
-->

# TAO _Remote Proctoring_ extension

![TAO Logo](https://github.com/oat-sa/taohub-developer-guide/raw/master/resources/tao-logo.png)

## Table of contents
 - [Installation](#installation-instructions)
 - [Configuration](##Configuration)

> This extension provide functionality to connect Tao instance to Proctorio tool

Using this extension you can use the remote proctoring provider [Proctorio](https://proctorio.com) tool with TAO

## Installation instructions

These instructions assume that you have already a TAO installation on your system. If you don't, go to
[package/tao](https://github.com/oat-sa/package-tao) and follow the installation instructions.

_Note, that you have to be logged in as System Administrator to do this._

Add the extension to your TAO composer and to the autoloader:
```bash
composer require oat-sa/extension-remote-proctoring
```

Install the extension on the CLI from the project root:

**Linux:**
```bash
sudo php tao/scripts/installExtension.php remoteProctoring
```

**Windows:**
```bash
php tao\scripts\installExtension remote-proctoring
```

As a system administrator you can also install it through the TAO Extension Manager:
- Settings (the gears on the right hand side of the menu) -> Extension manager
- Select _remoteProctoring_ on the right hand side, check the box and hit _install_



<!-- Not all of the blocks below are applicable for any repository, please remove those that aren't -->

## Configuration
