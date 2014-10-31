; <?php /*

[name]

not empty = 1

[email]

email = 1
unique = "#prefix#user.email"

[password]

length = "6+"

[verify]

matches = "$_POST['password']"

; */ ?>
