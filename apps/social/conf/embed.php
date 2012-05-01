; <?php /*

[social/facebook/like]

label = Facebook: Like Button

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

[social/facebook/like-box]

label = Facebook: Like-Box
columns = 2

url[label] = Facebook Page URL
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
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
faces[callback] = "social_true_false"

header[label] = Show Header
header[type] = select
header[require] = "apps/social/lib/Functions.php"
header[callback] = "social_true_false"

stream[label] = Show Stream
stream[type] = select
stream[require] = "apps/social/lib/Functions.php"
stream[callback] = "social_true_false"

[social/facebook/comments]

label = Facebook: Comments

[social/facebook/commentcount]

label = Facebook: Comment Count

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

[social/twitter/follow]

label = Twitter: Follow

twitter_id[label] = Twitter ID
twitter_id[type] = text
twitter_id[not empty] = 1
twitter_id[message] = Please enter your Twitter ID.

[social/twitter/feed]
label = Twitter: Feed
columns = 2

twitter_id[label] = Twitter ID
twitter_id[type] = text
twitter_id[not empty] = 1
twitter_id[message] = Please enter your Twitter ID.

num_of_tweets[label] = Number of Tweets
num_of_tweets[type] = text
num_of_tweets[not empty] = 1
num_of_tweets[initial] = 4
num_of_tweets[message] = Between 1 - 29.
num_of_tweets[regex] = "/^([1-9]|[1-2][0-9])$/"

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

shellbackground[label] = Shell Background Color
shellbackground[type] = text
shellbackground[not empty] = 1
shellbackground[initial] = "#009ac9"
shellbackground[message] = Please enter a valid color.

shelltext[label] = Shell Text Color
shelltext[type] = text
shelltext[not empty] = 1
shelltext[initial] = ffffff
shelltext[message] = Please enter a valid color.

tweettext[label] = Tweet Text Color
tweettext[type] = text
tweettext[not empty] = 1
tweettext[initial] = 000000
tweettext[message] = Please enter a valid color.

tweetbackground[label] = Tweet Background Color
tweetbackground[type] = text
tweetbackground[not empty] = 1
tweetbackground[initial] = ffffff
tweetbackground[message] = Please enter a valid color.

link[label] = Link Color
link[type] = text
link[not empty] = 1
link[initial] = 009ac9
link[message] = Please enter a valid color.

[social/twitter/tweet]

label = Twitter: Share

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

via[label] = Twitter ID
via[type] = text

[social/google/plusone]

label = "Google: +1 Button"

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

[social/video/youtube]

label = "Video: YouTube"

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

[social/google/maps]

label = "Google: Map"

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

[social/video/vimeo]

label = "Video: Vimeo"

url[label] = Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

; */ ?>
