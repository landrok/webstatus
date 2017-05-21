# WebStatus [![Build Status](https://travis-ci.org/landrok/webstatus.png)](https://travis-ci.org/landrok/webstatus) [![Code Climate](https://codeclimate.com/github/landrok/webstatus/badges/gpa.svg)](https://codeclimate.com/github/landrok/webstatus) [![Test Coverage](https://codeclimate.com/github/landrok/webstatus/badges/coverage.svg)](https://codeclimate.com/github/landrok/webstatus/coverage)

It is a simple and easy monitoring tool. It was designed for 
Raspbian OS but is compatible with Debian and Ubuntu.

There is nothing to configure. Just run the installer and use it.

Want to have a look ? See [Live Demo](http://91.121.71.25/webstatus/)

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
# Download the latest version
wget https://github.com/landrok/webstatus/archive/0.4.0.tar.gz

# Unzip
tar -xf 0.4.0.tar.gz

# Install (Must be done with root rights)
./webstatus-0.4.0/bin/install.sh

```

If the installation failed:

- Check that your system is up to date
- Create an issue and Copy/Paste installation logs

### Easy install

You have a full-automated mode with -y argument:

```shell
./webstatus-0.4.0/bin/install.sh -y
```
________________________________________________________________________

## Customize title

- Edit _app/config/global-custom.ini.php_
- In the `[webapp]` section, change 
  - `title` value, for web browser status bar title
  - `label` value, for the HTML navbar title
  - `icon` value, must be selected among 
    [glyphicons](http://getbootstrap.com/components/), delete the
    "glyphicon glyphicon-", just keep the last part of the string.
    
    _Example_: To print an asterisk, the proposed class is 
    `glyphicon glyphicon-asterisk`. Just indicate `asterisk`.

________________________________________________________________________

## Hide IP addresses

_This feature is only working for IPv4 addresses_

- Edit _app/config/global-custom.ini.php_
- In the `[webapp]` section, set `hide-ip` value to `on` or `1`

________________________________________________________________________

## Customize thresholds

- Edit _app/config/global-custom.ini.php_
- In the `[thresholds]` section, change 
  - `*.mid` or `*.high` values for each metric
  
    _Example_: To have a green flag below 50% CPU usage, an orange flag
    for CPU usage between 50 and 70%, and a red flag when CPU usage is 
    over 70%, the values should be `cpu.mid=50` and `cpu.high=70`

________________________________________________________________________

## Customize processes

- Edit _app/config/global-custom.ini.php_
- In the `[cron]` section, change 
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

Remote feature has 2 parts: a client and a server

- Install webstatus on the 2 machines
- On the **client machine**, edit _app/config/global-custom.ini.php_
  
  - In the `[remote]` section, change
    - `remote.client` value to `on`
    - `remote.url` value to `http://your-server-url/webstatus/remote.php`

- On the **server machine**, edit _app/config/global-custom.ini.php_
  - In the `[remote]` section, change 
    - `remote.server` value to `on`

That's all. Now you can follow the client status on the server.
