; <?php /*

[admin/forward]

label = "Pages: Redirect Link"
icon = external-link

to[label] = Link
to[type] = text
to[initial] = "http://"
to[not empty] = 1
to[url] = 1
to[message] = Please enter a valid URL.

code[label] = Status
code[type] = select
code[initial] = 302
code[require] = "apps/admin/lib/Functions.php"
code[callback] = "admin_status_codes"

[admin/conditionalforward]

label ="Pages: Conditional Redirect"
icon = external-link

to[label] = Link
to[type] = text
to[initial] = "http://"
to[url] = 1
to[message] = Please enter a valid URL.

user_type[label] = User role
user_type[type] = select
user_type[require] = "apps/admin/lib/Functions.php"
user_type[callback] = "admin_user_groups"

[admin/html]

label = "Embed HTML Code"
icon = code

id[label] = HTML
id[type] = textarea
id[not empty] = 1
id[message] = Please enter some HTML.
id[require] = "apps/admin/lib/Functions.php"
id[filter] = admin_embed_filter

[admin/empty]

label = "Empty Contents"
icon = code

; */ ?>
