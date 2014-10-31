; <?php /*

[id]

not empty = 1
regex = "/^[a-zA0-9_-]+$/"
unique = "#prefix#webpage.id"
not exists = "apps"

[title]

not empty = 1

[layout]

skip_if_empty = 1
callback = admin_layout_exists

[body]

not empty = 1

; */ ?>
