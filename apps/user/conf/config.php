; <?php /*

[User]

; This is the page you want to redirect users to after
; they log out.

logout_redirect = "/"

; You can enable or disable alternative login methods here.
; Each name corresponds to a handler in apps/user/handlers/login.

login_methods[] = openid
login_methods[] = google
;login_methods[] = twitter
;login_methods[] = facebook
;login_methods[] = persona

; Limit the number of login attempts, and how long to stop them (in seconds)
; from trying again if they exceed the limit.

login_attempt_limit = 5
block_attempts_for = 900

; Height and width to resize photos to

photo_width = 125
photo_height = 125

; Default photo to use for users without profile photos.

default_photo = "/apps/admin/css/admin/user_profile.png"

; The default role for new users.

default_role = "member"

[Custom Handlers]

; You can override some of the built-in handlers with your own
; by changing them here. You can also disable any of them by setting
; them to Off.

user/index = user/index
user/signup = user/signup
user/update = user/update
user/login = user/login
user/login/newuser = user/login/newuser
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

twitter_id = ""
consumer_key = ""
consumer_secret = ""
access_token = ""
access_token_secret = ""

[Admin]

handler = user/admin
name = Members
install = user/upgrade
upgrade = user/upgrade
version = 1.1.3-stable

; */ ?>