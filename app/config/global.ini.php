<?php
; defined('BASEURL') || die('Direct access to this file is forbidden');
?>

; ----------------------------------------------------------------------
;
; This is the default configuration file
;
; USE global-custom.ini.php file to customize your defaults.
;
; ----------------------------------------------------------------------


; ----------------------------------------------------------------------
; Web Application Configuration
; ----------------------------------------------------------------------
[webapp]

title=RPi Home
label=RPi Home
icon-class=music
ip-hide=1


; ----------------------------------------------------------------------
; Processes to monitor
; Separated by a pipe (|) character
; ----------------------------------------------------------------------
[cron]

processes.pattern="apache"

; ----------------------------------------------------------------------
; Logs patterns
; Format: logs.pattern.{route}={logPath}
; {route} must be defined in the routes.ini.php config file
; After changing one of these values, execute ./bin/webStatus.cron.sh
; ----------------------------------------------------------------------
logs.pattern.syslog=/var/log/syslog
logs.pattern.messages=/var/log/messages
logs.pattern.web-access=/var/log/apache2/*access*.log
logs.pattern.web-error=/var/log/apache2/*error*.log


; ----------------------------------------------------------------------
; Remote configuration
; ----------------------------------------------------------------------
[remote]

;remote.server=on
;remote.client=on
;remote.url=""


; ----------------------------------------------------------------------
; Thresholds
; ----------------------------------------------------------------------
[thresholds]

; CPU usage
cpu.mid=50
cpu.high=90

; CPU Temperature
temp.mid=60
temp.high=70

; RAM usage
mem.mid=60
mem.high=90

; SWAP usage
swap.mid=5
swap.high=10

; Disk space usage
disk.mid=60
disk.high=90

; Socket number
socket.mid=20
socket.high=30
