; <?php /*

[id]

not empty = 1
regex = "/^[a-zA0-9_-]+$/"
unique = "webpage.id"
not exists = "apps"

[title]

not empty = 1

[layout]

skip_if_empty = 1
exists = "layouts/%s.html"

[body]

not empty = 1

; */ ?>