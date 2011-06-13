; <?php /*

[name]

not empty = 1

[email]

email = 1

[password]

not empty = 1
length = "6+"

[verify]

not empty = 1
matches = "$_POST['password']"

; */ ?>