; <?php /*

[id]

not empty = 1
regex = "/^[a-zA0-9_-]+$/"
not exists = "apps"

[layout]

skip_if_empty = 1
callback = "admin\Layout::exists"

; */ ?>
