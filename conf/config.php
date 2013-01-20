; <?php /* Global configurations go here

[General]

; The name of your website.
site_name = "die symbionten"

; Default outbound email address.
email_from = "jens@die-symbionten.de"

; Default character set for output.

charset = UTF-8

; Default timezone for date functions.

timezone = GMT

; Default handler for requests to / and /(.*) that don't match
; any other handler.

default_handler = "admin/page"

; Default layout template (aka theme) to use for page rendering.

default_layout = "default"

; Handler for errors, to be called by other handlers via
; $controller->error() with 'code', 'title' and an optional
; 'message' parameters.

error_handler = "admin/error"

; Whether to gzip output for browsers that support it.
; This usually gives a noticeable performance boost.

compress_output = On

; For development, turn debugging on and Elefant will output
; helpful information on errors.

debug = On

; For development, turn display_errors on and Elefant will
; output fatal error messages in addition to the debugger.

display_errors = On

[I18n]

; This is the method for determining which language to show the
; current visitor. Options are: url (e.g., /fr/), subdomain
; (e.g., fr.example.com), http (uses Accept-Language header),
; or cookie.

negotiation_method = http

[Database]

; Database settings go here. Driver must be a valid PDO driver.

master[driver] = mysql
master[host] = "localhost:3306"
master[name] = d0161ff6
master[user] = d0161ff6
master[pass] = "bm7bK34HTVnf8u7S"

; The database table name prefix.

prefix = "elefant_"

[Mongo]

; Settings to connect to MongoDB. Must have PHP Mongo extension
; installed via `pecl install mongo`.

;host = localhost:27017
;user = username
;pass = password
;set_name = my_replica_set

[Hooks]

; This is a list of hooks in the system and associated handlers
; to trigger when they occur. It's a good idea to name the hooks
; you define after the handler they occur in, to make it easier
; to look up the parameters they will receive.

admin/edit[] = navigation/hook/edit
admin/delete[] = navigation/hook/delete
;admin/add[] = search/add
;admin/edit[] = search/add
;admin/delete[] = search/delete
;blog/add[] = search/add
;blog/edit[] = search/add
;blog/delete[] = search/delete

[Cache]

; Configure your cache server list. This will be available in
; your handlers via the $cache object. If blank, or cache
; is unavailable, it will emulate Memcache via the lib/Cache
; object, storing the cache in the `conf/datastore` folder.
; Alternately add `backend = redis` to use Redis as the cache
; backend, `backend = apc` to use APC as the cache backend, or
; `backend = xcache` to use XCache as the cache backend.
; Each of these cache options uses an identical API so you can
; implement caching without worrying about the backend in
; development versus production.
;
; Note: To use Redis auth, add the password after the port,
; separated by a comma, e.g. "localhost:6379,PASSWORD"

;backend = memcache
;server[] = localhost:11211

[Mailer]

; This is where you configure your settings for sending emails.
; Email sending is powered by the Zend Framework's Mail package.
; For more info on the configuration options for the various
; transport methods here, see:
; http://framework.zend.com/manual/en/zend.mail.html

; To override the default email from info in the
; global config file, edit these:
email_from = default
email_name = default

; To send using PHP's mail() function, use this:
transport[type] = sendmail

; To send using an SMTP server, use this:
;transport[type] = smtp
;transport[host] = 127.0.0.1
;transport[name] = localhost

; To add TLS-based encryption to the SMTP connection:
;transport[ssl]  = tls
;transport[port] = 25

; To add SSL-based encryption to the SMTP connection:
;transport[ssl]  = ssl
;transport[port] = 465

; To add authentication to the SMTP connection:
;transport[auth] = plain
;transport[auth] = login
;transport[auth] = crammd5
;transport[username] = username
;transport[password] = password

; To send to a file in conf/mailer, use this:
;transport[type] = file
;transport[folder] = cache/mailer

; */ ?>
