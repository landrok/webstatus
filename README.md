# WebStatus [![Build Status](https://travis-ci.org/landrok/webstatus.png)](https://travis-ci.org/landrok/webstatus) [![Code Climate](https://codeclimate.com/github/landrok/webstatus/badges/gpa.svg)](https://codeclimate.com/github/landrok/webstatus) [![Test Coverage](https://codeclimate.com/github/landrok/webstatus/badges/coverage.svg)](https://codeclimate.com/github/landrok/webstatus/coverage)

It is a simple and easy monitoring tool. It was designed for 
Raspbian OS but is compatible with Debian and Ubuntu.

There is nothing to configure. Just run the installer and use it!

[//]: # "[Demo](http://example.com/webstatus/)"

________________________________________________________________________

## Features

- Easy to install

- A global summary for processor, memory, temperature, disk space, 
  network and more

- Clear status labels & trends for main metrics

- Each metric is bounded to a dump of the corresponding shell command 
  result

- Live monitoring for CPU, memory and network bandwith usage

- Easy to customize: specific processes, log files

________________________________________________________________________

## Supported OS

* Raspbian  >=8
* Debian    >=8
* Ubuntu    >=14.04

________________________________________________________________________

## Install

```shell
# Download archive
wget https://github.com/landrok/webstatus/archive/0.3.1.tar.gz

# Unzip the archive
tar -xf 0.3.1.tar.gz

# Install (Must be done with root rights)
./webstatus-0.3.1/bin/install.sh

```

If the installation failed:

- Check that your system is up to date
- Create an issue and Copy/Paste installation logs

You have a fully automated mode with -y argument:

```shell
# Available since release 0.4
./webstatus-0.3.1/bin/install.sh -y
```
________________________________________________________________________

## Customize title

- Edit app/config/global-custom.ini.php
- In the [webapp] section, change 
  - `title` value, for web browser status bar title
  - `label` value, for the HTML navbar title
  - `icon` value, must be selected among 
    [glyphicons](http://getbootstrap.com/components/), delete the
    "glyphicon glyphicon-", just keep the last part of the string.
    
    _Example_: To print an asterisk, the proposed class is 
    `glyphicon glyphicon-asterisk`. Just indicate `asterisk`.

________________________________________________________________________

## Customize thresholds

- Edit app/config/global-custom.ini.php
- In the [thresholds] section, change 
  - `*.mid` or `*.high` values for each metric
  
    _Example_: To have a green flag below 50% CPU usage, an orange flag
    for CPU usage between 50 and 70%, and a red flag when CPU usage is 
    over 70%, the values should be `cpu.mid=50` and `cpu.high=70`

________________________________________________________________________

## Customize processes

- Edit app/config/global-custom.ini.php
- In the [cron] section, change 
  - `processes.pattern` value
  
    _Example_: To focus on apache and mysql processes, the value should 
    be `"apache|mysql"`

________________________________________________________________________

## Customize logs

_Coming soon_

________________________________________________________________________

## Customize menus

_Coming soon_

________________________________________________________________________

## Activate remote feature

_Coming soon_
