; <?php /*

[title]

not empty = 1

[layout]

skip_if_empty = 1
callback = "admin\Layout::exists"

[post_layout]

skip_if_empty = 1
callback = "admin\Layout::exists"

[comments]

not empty = 1
regex = "/^(facebook|disqus|comments\/embed|none)$/"

[preview_chars]

skip_if_empty = 1
type = numeric

[post_format]

not empty = 1
regex = "/^(html|markdown)$/"

[disqus_shortname]

skip_if_empty = 1
regex = "/^[a-zA-Z0-9_-]+$/"

;[twitter_username]

;skip_if_empty = 1
;regex = "/^[a-zA-Z0-9_-]+$/"

; */ ?>
