; <?php /* Global configurations go here

[General]

; A master login for the example /admin pages.
master_username = master
master_password = "CHANGE ME"

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

[I18n]

negotiation_method = url

[Database]

; Database settings go here.
driver = sqlite
file = "conf/site.db"
;driver = mysql
;host = "host:port"
;name = dbname
;user = username
;pass = "password"

; */ ?>