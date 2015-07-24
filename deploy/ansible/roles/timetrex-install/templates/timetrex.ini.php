;<?php die('Access denied...');?>
;
;
; TimeTrex Configuration File
; *Linux* Example
;
;

;
; System paths. NO TRAILING SLASHES!
;
[path]
;URL to TimeTrex web root directory. ie: http://your.domain.com/<*BASE_URL*>
;DO NOT INCLUDE http://your.domain.com, just the directory AFTER your domain
base_url = /interface

;
;log directory
;
;Linux
log = /var/log/timetrex

;
;Misc storage, for attachments/images
;
;Linux
storage = /var/timetrex/storage

;
;Full path and name to the PHP CLI Binary
;
;Linux
php_cli = /usr/bin/php



;
; Database connection settings. These can be set from the installer.
;
[database]
type = mysqli
;type = postgres8

host = localhost
database_name = {{ timetrex_db_name }}
user = {{ timetrex_db_user }}
password = {{ timetrex_db_password }}


;
; Email delivery settings.
;
[mail]
;Least setup, deliver email through TimeTrex's email relay via SOAP (HTTP port 80)
delivery_method = soap

;Deliver email through local sendmail command specified in php.ini
;delivery_method = mail

;Deliver email through remote SMTP server with the following settings.
;delivery_method = smtp
;smtp_host=smtp.gmail.com
;smtp_port=587
;smtp_username=timetrex@gmail.com
;smtp_password=testpass123


;
; Cache settings
;
[cache]
enable = TRUE
;Linux
dir = /tmp/timetrex



[debug]
;Set to false if you're debugging
production = TRUE

enable = FALSE
enable_display = FALSE
buffer_output = TRUE
enable_log = FALSE
verbosity = 10



[other]
force_ssl = FALSE
installer_enabled = FALSE
primary_company_id = 1
hostname = localhost
deployment_on_demand = 1

; System Administrators Email address to send critical errors to if necessary. Set to FALSE to disable completely.
;system_admin_email = "sysadmin@mydomain.com"

default_interface = html5

;WARNING: DO NOT CHANGE THIS AFTER YOU HAVE INSTALLED TIMETREX.
;If you do it will cause all your passwords to become invalid,
;and you may lose access to some encrypted data.
salt = 1825c844d2dbb89029eee426fa72d61a
