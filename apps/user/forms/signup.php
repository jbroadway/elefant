; <?php /*

[name]

not empty = 1

[email]

email = 1
unique = "#prefix#user.email"

[password]

length = "6+"

[verify_pass]

matches = "$_POST['password']"

; */ ?>
