Web Status
==========

It is a simple and easy monitoring tool. It was designed for 
Raspbian OS but is compatible with Debian too.

There is nothing to configure. Just run the installer and use it!

[//]: # "[Demo](http://example.com/webstatus/)"

________________________________________________________________________

## Features

- A global summary for processor, memory, temperature, disk space, 
  network and more

- Clear status labels & trends for main metrics

- Each metric is bounded to a dump of the corresponding shell command 
  result

- Live monitoring for CPU, memory and network bandwith usage

- Easy to custom: specify processes, specific log files

- Easy to extend

________________________________________________________________________

## Supported OS

* Debian
* Raspbian

________________________________________________________________________

## Install

```shell
# Download archive
wget https://github.com/landrok/webstatus/archive/0.2.1.tar.gz

# Unzip the archive
tar -xf 0.2.1.tar.gz

# Install (Must be done with root rights)
./webstatus-0.2.1/bin/install.sh

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
