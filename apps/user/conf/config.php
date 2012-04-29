; <?php /*

[User]

; This is the page you want to redirect users to after
; they log out.

logout_redirect = "/"

; This is a list of user types available on your site.
; The admin and member types are required, but you can
; add new ones here.

user_types = "admin, member"

; You can enable or disable alternative login methods here.
; Each name corresponds to a handler in apps/user/handlers/login.

login_methods[] = openid
login_methods[] = google
;login_methods[] = twitter
;login_methods[] = facebook

; Limit the number of login attempts, and how long to stop them (in seconds)
; from trying again if they exceed the limit.

login_attempt_limit = 5
block_attempts_for = 900

[Access]

; You can add your own access levels here. The public, member,
; and private values should be left as defaults since they are
; used by the CMS.

; The value "all" means any user type including anonymous users.
; The value "login" means any logged in user.
; The value "admin" is short for "type:admin".

; To specify a custom access level for a custom user type, enter
; "client = type:client" or simply "client = client".

public = all
member = login
private = admin

[Custom Handlers]

; You can override some of the built-in handlers with your own
; by changing them here. You can also disable any of them by setting
; them to Off.

user/index = user/index
user/signup = user/signup
user/update = user/update
user/login = user/login
user/logout = user/logout

[Facebook]

; To enable Facebook login support, register your site at
; https://developers.facebook.com/apps to generate the following
; values for your site.

application_id = ""
application_secret = ""

[Twitter]

; To enable Twitter login support, register your site at
; https://dev.twitter.com/apps/new to generate the following
; values for your site.

consumer_key = ""
consumer_secret = ""

[Admin]

handler = user/admin
name = Users
install = user/upgrade
upgrade = user/upgrade
version = 1.1.3-stable

; */ ?>