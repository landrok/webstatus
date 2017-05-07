<?php
; defined('BASEURL') || die('Direct access to this file is forbidden');
?>

; ----------------------------------------------------------------------
; Default menu
; No submenu
; ----------------------------------------------------------------------

[index]
icon=home
label=Home

; ----------------------------------------------------------------------
; Status menu
; ----------------------------------------------------------------------

[status]
icon=dashboard
label=Tools

; System
sub-header-1=System
sub-temperature=Temperature
sub-uptime=Uptime
sub-users=Users

; Usage
sub-header-2=Performances
sub-memory=Memory
sub-mpstat=CPU
sub-hdd=Disk
sub-top=Usage
sub-processes=Processes

; Network
sub-header-3=Network
sub-interfaces=Interfaces
sub-servers=Servers
sub-tcp-sockets=TCP Sockets
sub-iptables=Firewall
sub-ifstat=Ifstat

; ----------------------------------------------------------------------
; Logs menu
; ----------------------------------------------------------------------

[logs]
icon=book
label=Logs

sub-header-1=System
sub-syslog=Syslog
sub-messages=Messages
;sub-fr24feed=Flight radar feed
;sub-vrsw-mlat=VRS MLAT
sub-web-access=Apache access
sub-web-error=Apache error
;sub-lftw01=LFTW01

; ----------------------------------------------------------------------
; Monitor menu
; ----------------------------------------------------------------------

[monitor]
icon=stats
label=Monitor
