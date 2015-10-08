; <?php /*

[social/facebook/like]

label = Facebook: Like Button
icon = thumbs-up

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

[social/facebook/like-box]

label = Facebook: Like-Box
icon = facebook-square
columns = 2

url[label] = Facebook Page URL
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

width[label] = Width
width[type] = text
width[not empty] = 1
width[initial] = 300
width[message] = Please enter a valid width.

height[label] = Height
height[type] = text
height[not empty] = 1
height[initial] = 500
height[message] = Please enter a valid height.

colorscheme[label] = Color Scheme
colorscheme[type] = select
colorscheme[require] = "apps/social/lib/Functions.php"
colorscheme[callback] = "facebook_light_dark"

bordercolor[label] = Border Color
bordercolor[type] = text
bordercolor[message] = Please enter a valid Border Color.

faces[label] = Show Faces
faces[type] = select
faces[require] = "apps/social/lib/Functions.php"
faces[callback] = "social_yes_no"

header[label] = Show Header
header[type] = select
header[require] = "apps/social/lib/Functions.php"
header[callback] = "social_yes_no"

stream[label] = Show Stream
stream[type] = select
stream[require] = "apps/social/lib/Functions.php"
stream[callback] = "social_yes_no"

[social/facebook/comments]

label = Facebook: Comments
icon = facebook

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

[social/facebook/commentcount]

label = Facebook: Comment Count
icon = facebook

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

[social/twitter/follow]

label = Twitter: Follow
icon = twitter

twitter_id[label] = Twitter ID
twitter_id[type] = text
twitter_id[not empty] = 1
twitter_id[message] = Please enter your Twitter ID.

[social/twitter/feed]

label = Twitter: Feed
icon = twitter-square
columns = 2

twitter_id[label] = Twitter ID
twitter_id[type] = text
twitter_id[not empty] = 1
twitter_id[message] = Please enter your Twitter ID.

num_of_tweets[label] = Number of Tweets
num_of_tweets[type] = text
num_of_tweets[not empty] = 1
num_of_tweets[initial] = 5
num_of_tweets[message] = Between 1 - 29.
num_of_tweets[regex] = "/^([1-9]|[1-2][0-9])$/"

show_dates[label] = Show Dates
show_dates[type] = select
show_dates[require] = "apps/social/lib/Functions.php"
show_dates[callback] = "social_yes_no"

parse_links[label] = Include Links
parse_links[type] = select
parse_links[require] = "apps/social/lib/Functions.php"
parse_links[callback] = "social_yes_no"

[social/twitter/tweet]

label = Twitter: Share
icon = twitter

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

via[label] = Twitter ID
via[type] = text

[social/google/plusone]

label = "Google: +1 Button"
icon = google-plus-square

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

[social/video/youtube]

label = "Video: YouTube"
icon = video-camera

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

width[label] = Player width
width[type] = text
width[initial] = 640
width[not empty] = 1
width[message] = Please enter a player width.

height[label] = Player height
height[type] = text
height[initial] = 360
height[not empty] = 1
height[message] = Please enter a player height.

[social/video/vine]

label = "Video: Vine"
icon = video-camera

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "/^https?:\/\/vine\.co\/v\/[a-zA-Z0-9_-]+$/"
url[message] = Please enter a valid vine.co URL.

size[label] = Player width/height
size[type] = select
size[require] = "apps/social/lib/Functions.php"
size[callback] = vine_size
size[initial] = "600"

audio[label] = Mute video by default?
audio[type] = select
audio[require] = "apps/social/lib/Functions.php"
audio[callback] = social_yes_no
audio[initial] = "yes"

embed[label] = Embed Type
embed[type] = select
embed[require] = "apps/social/lib/Functions.php"
embed[callback] = vine_embed
embed[initial] = "simple"

[social/google/maps]

label = "Google: Map"
icon = map-marker

address[label] = Address
address[type] = text
address[not empty] = 1
address[message] = Please enter an address.

city[label] = City
city[type] = text
city[not empty] = 1
city[message] = Please enter a city.

state[label] = State/Province
state[type] = text

country[label] = Country
country[type] = text
country[not empty] = 1
country[message] = Please enter a country.

zip[label] = Zip/Postal Code
zip[type] = text

width[label] = Map width
width[type] = text
width[initial] = "100%"
width[not empty] = 1
width[message] = Please enter a width.

height[label] = Map height
height[type] = text
height[initial] = "400px"
height[not empty] = 1
height[message] = Please enter a height.

[social/video/vimeo]

label = "Video: Vimeo"
icon = video-camera

url[label] = Link
url[type] = text
url[not empty] = 1
url[url] = 1
url[message] = Please enter a valid URL.

width[label] = Player width
width[type] = text
width[initial] = 640
width[not empty] = 1
width[message] = Please enter a player width.

height[label] = Player height
height[type] = text
height[initial] = 360
height[not empty] = 1
height[message] = Please enter a player height.

; */ ?>
