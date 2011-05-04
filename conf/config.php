; <?php /* Global configurations go here

[General]

; A master login for the example /admin pages.
master_username = elefantman
master_password = "Mr$nuff13up@gu$"

; Default character set for output.
charset = UTF-8

; Default timezone for date functions.
timezone = GMT

; Default handler for requests to / and /(.*) that don't match
; any other handler.
default_handler = "admin/page"

; Whether to gzip output for browsers that support it.
; This usually gives a noticeable performance boost.
compress_output = On

[Database]

; Database settings go here. SQLite and MySQL are supported.
;driver = sqlite
;file = "conf/site.db"
driver = mysql
host = "mysql.pink.dotcloud.com:3185"
name = pink
user = pink
pass = "unf0rG3tt@bl3"

; */ ?>