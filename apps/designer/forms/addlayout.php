; <?php /*

[name]

not empty = 1
regex = '/^[a-z0-9_-]+(\/[a-z0-9_-]+)?(\/[a-z0-9_-]+)?$/'
not exists = "layouts/%s.html"

[body]

not empty = 1

[body:invalid-php-functions]

not callback = invalid_php_functions

; */ ?>
