; <?php /*

[name]

not empty = 1

[email]

email = 1
unique = "user.email"

[password]

not empty = 1

[verify]

matches = "$_POST[password]"

; */ ?>