; <?php /*

[admin/forward]

label = "Pages: Redirect Link"

to[label] = Link
to[type] = text
to[initial] = "http://"
to[not empty] = 1
to[regex] = "|^(http:/)?/.+$|"
to[message] = Please enter a valid URL.

[admin/html]

label = "Embed HTML Code"

id[label] = HTML
id[type] = textarea
id[not empty] = 1
id[message] = Please enter some HTML.
id[require] = "apps/admin/lib/Functions.php"
id[filter] = admin_embed_filter

; */ ?>