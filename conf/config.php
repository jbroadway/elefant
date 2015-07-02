; <?php /* Global configurations go here

[General]

; The name of your website.
site_name = "Your Site Name"

; Default outbound email address.
email_from = "you@example.com"

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

; Set this to 'flat' to have all pages appear at the top-level,
; for example /about-us, /products. Set this to 'nested' to have
; page URLs appear as they are in your navigation tree, for
; example /products/flowbee/benefits. Default is flat.

page_url_style = flat

; The domain to set the session cookie for. If set to 'full'
; (the default) it will set it to the full domain of the current
; request, including subdomain. If set to 'top', it will set
; the session cookie to '.domain.com' so that it will work
; across subdomains of the site.

session_domain = full

; Session duration in seconds. Default is 2592000, or 30 days.
; Set this to 0 for the session to expire when the user closes
; their browser.

session_duration = 2592000

; The name of the session cookie.

session_name = elefant_user

; This can be set to a session save handler such as memcached
; or redis, or the name of a class that implements SessionHandlerInterface.
; The default is Off, which uses PHP's internal session handler.
; Here are 3 example options:
;
; session_save_handler = "memcached:192.0.2.25:11211,192.0.2.26:11211"
; session_save_handler = "redis:tcp://192.0.2.5:6379?timeout=0.5"
; session_save_handler = SessionHandlerCookie
;
; The first two specify a session handler followed by a string that
; session.save_path will be set to. The third uses the SessionHandlerCookie
; class to store session data in an HMAC-verified cookie.

session_save_handler = Off

; For development, turn debugging on and Elefant will output
; helpful information on errors.

debug = Off

; For development, turn display_errors on and Elefant will
; output fatal error messages in addition to the debugger.

display_errors = Off

; Check for updates and display an update notice in the top bar
; when new updates are available for the CMS.

check_for_updates = On

; Unique secret site key
site_key = Elefant_Secret_Key

[I18n]

; Turn this on if your site is multilingual. This will change
; the behaviour of the site navigation so that the appropriate
; language's section of the site tree is shown, depending on
; the user's language choice.

multilingual = Off

; This is the method for determining which language to show the
; current visitor. Options are: url (e.g., /fr/), subdomain
; (e.g., fr.example.com), http (uses Accept-Language header),
; or cookie.

negotiation_method = http

[Paths]

; The path to your navigation structure file.
; Note: Leave the leading slash out of the path.

navigation_json = conf/navigation.json

; The path to your file uploads. Note: Leave
; the leading and trailing slashes out of the path.

filemanager_path = files

; The path to your role definitions and their access
; rules. Note: Leave the leading slash out of the
; path.

access_control_list = conf/acl.php

; The path to your custom tools list. Note: Leave
; the leading slashes out of the path.

toolbar = conf/tools.php

[Database]

; Database settings go here. Driver must be a valid PDO driver.
; For examples of various database configurations, visit:
; http://www.elefantcms.com/wiki/Database-configuration-examples

master[driver] = sqlite
master[file] = "conf/site.db"

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

; Enable external cache control for the content.
; If On, it will allow to inform frontend proxy to cache this response
; and use it next "expires" seconds. Currently nginx as frontend proxy is
; supported.
control = Off

; Sets when to expire the content in the external cache, if one is used.
expires = 86400

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

; If you are using the Resque app, set this to On and the mailer will
; automatically queue outgoing messages for increased scalability and
; responsiveness to end users.
use_resque = Off

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
