; <?php /*

[admin/forward]

label = "Pages: Redirect Link"

to[label] = Link
to[type] = text
to[initial] = "http://"
to[not empty] = 1
to[regex] = "|^(http:/)?/.+$|"
to[message] = Please enter a valid URL.

code[label] = Status
code[type] = select
code[initial] = 302
code[require] = "apps/admin/lib/Functions.php"
code[callback] = "admin_status_codes"

[admin/conditionalforward]
label ="Pages: Conditional Redirect Link"

to[label] = link
to[type] = text
to[initial] = "http://"
to[regex] = "|^(http:/)?/.+$|"
to[message] = Please enter a valid URL.

user_type[label] = Group
user_type[type] = select
user_type[require] = "apps/admin/lib/Functions.php"
user_type[callback] = "admin_user_groups"

[admin/html]

label = "Embed HTML Code"

id[label] = HTML
id[type] = textarea
id[not empty] = 1
id[message] = Please enter some HTML.
id[require] = "apps/admin/lib/Functions.php"
id[filter] = admin_embed_filter

; */ ?>
