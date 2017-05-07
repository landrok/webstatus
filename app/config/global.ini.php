<?php
; defined('BASEURL') || die('Direct access to this file is forbidden');
?>

; ----------------------------------------------------------------------
; Web Application Configuration
; ----------------------------------------------------------------------
[webapp]

title=RPI Home
label=RPI Home
icon-class=glyphicon glyphicon-music


; ----------------------------------------------------------------------
; Processes to monitor
; Separated by a pipe (|) character
; ----------------------------------------------------------------------
[cron]

processes.pattern="apache"

; ----------------------------------------------------------------------
; Logs patterns
; Each var must have his route
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
