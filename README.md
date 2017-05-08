Web Status
==========

It is a simple and easy to use monitoring tool. It was designed for 
Raspbian OS but is compatible with Debian too.

There is nothing to configure. Just run the installer and use it!

[//]: # ([Demo](http://example.com/webstatus/))

________________________________________________________________________

## Features

- Quick summaries & monitoring about temperature, disk space, 
  processors, memory, network and more.

- Clear status labels & trends for main metrics

- 12 status views

- Live monitoring for CPU, memory and bandwith usage

- Easy to custom: specify processes, specific log files

- Easy to extend

________________________________________________________________________

## Install

```shell
# Download archive
wget https://github.com/landrok/webstatus/archive/master.tar.gz

# Unzip the archive
tar -xf master.tar.gz

# Install (Must be done with root rights)
./webstatus-master/bin/install.sh

```

If the installation failed:

- Check that your system is up to date
- Create an issue and Copy/Paste installation logs

________________________________________________________________________

## Customize title

- Edit app/config/global.ini.php
- In the [webapp] section, change 
  - `title` value, for web browser status bar title
  - `label` value, for the HTML navbar title
  - `icon` value, must be selected among 
    [glyphicons](http://getbootstrap.com/components/), delete the
    "glyphicon glyphicon-", just keep the last part of the string.
    
    _Example_: To print an asterisk, the proposed class is 
    `glyphicon glyphicon-asterisk`. Just indicate `asterisk`.

________________________________________________________________________

## Customize processes

- Edit app/config/global.ini.php
- In the [cron] section, change 
  - `processes.pattern` value
  
    _Example_: To focus on apache and mysql processes, the value should 
    be `"apache|mysql"`

________________________________________________________________________

## Customize logs

- Edit app/config/global.ini.php
- In the [cron] section, add or change 
  - `logs.pattern.{route}={logPath}`

    _Example_: To focus on apache and mysql processes, the value should 
    be `"apache|mysql"`

________________________________________________________________________

## Customize menus

_Coming soon_

________________________________________________________________________

## Activate remote feature

_Coming soon_
