; <?php /* Global configurations go here

[General]

; A master login for the example /admin pages.
; Username should be an email address.

master_username = "you@example.com"
master_password = "CHANGE ME"

; Default character set for output.

charset = UTF-8

; Default timezone for date functions.

timezone = GMT

; Default handler for requests to / and /(.*) that don't match
; any other handler.

default_handler = "admin/page"

; Handler for errors, to be called by other handlers via
; $controller->error() with 'code', 'title' and an optional
; 'message' parameters.

error_handler = "admin/error"

; Whether to gzip output for browsers that support it.
; This usually gives a noticeable performance boost.

compress_output = On

[I18n]

; This is the method for determining which language to show the
; current visitor. Options are: url (e.g., /fr/), subdomain
; (e.g., fr.example.com), http (uses Accept-Language header),
; or cookie.

negotiation_method = url

[Database]

; Database settings go here. Driver must be a valid PDO driver.

driver = sqlite
file = "conf/site.db"

;driver = mysql
;host = "host:port"
;name = dbname
;user = username
;pass = "password"

[Hooks]

; This is a list of hooks in the system and associated handlers
; to trigger when they occur. It's a good idea to name the hooks
; you define after the handler they occur in, to make it easier
; to look up the parameters they will receive.

;admin/add[] = search/add
;admin/edit[] = search/add
;admin/delete[] = search/delete

[Memcache]

; Configure your memcache server list. If set, there will be a
; global $memcache object available to your apps. If the list is
; blank, or memcache is not available, it will create a fake
; global $memcache object that caches to memory within the PHP
; request, so you can hard-code cache functions even if caching
; is disabled on some systems.

;server[] = localhost:11211

; */ ?>