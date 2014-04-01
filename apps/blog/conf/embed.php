; <?php /*

[blog/headlines]

label = "Blog: Headlines"
icon = rss

tag[label] = "Tag (optional)"
tag[type] = select
tag[require] = "apps/blog/lib/Functions.php"
tag[callback] = "blog_get_tags"

dates[label] = "Show dates"
dates[type] = select
dates[require] = "apps/blog/lib/Functions.php"
dates[callback] = "blog_yes_no"

[blog/bymonth]

label = "Blog: Headlines by Month"
icon = rss

tag[label] = "Tag (optional)"
tag[type] = select
tag[require] = "apps/blog/lib/Functions.php"
tag[callback] = "blog_get_tags"

[blog/tags]

label = "Blog: Tag Cloud"
icon = tags

[blog/rssviewer]

label = "Blog: RSS Viewer"
icon = rss

url[label] = RSS Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

[blog/postsfeed]

label = "Blog: Latest Posts"
icon = clock-o

number[label] = "Number of Posts"
number[type] = numeric
number[initial] = 5

; */ ?>