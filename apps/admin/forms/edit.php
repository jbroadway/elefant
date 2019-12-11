; <?php /*

[id]

not empty = 1
regex = "/^[a-z0-9_-]+$/"
not exists = "apps"

[title]

not empty = 1

[layout]

skip_if_empty = 1
callback = "admin\Layout::exists"

[body]

not empty = 1

; */ ?>
