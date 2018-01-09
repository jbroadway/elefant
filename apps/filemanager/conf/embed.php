; <?php /*

[filemanager/photo]

label = "Images: Editable Photo"
icon = picture-o
acl = filemanager

key[type] = hidden
key[filter] = "Image::generate_key"

width[label] = "Width (px)"
width[type] = text
width[initial] = 300

height[label] = "Height (px)"
height[type] = text
height[initial] = 200

link[label] = "Link (optional)"
link[type] = text

[filemanager/slideshow]

label = "Images: Slideshow"
icon = picture-o

path[label] = Folder
path[type] = select
path[require] = "apps/filemanager/lib/Functions.php"
path[callback] = "filemanager_list_folders"

autoplay[label] = Auto-play
autoplay[type] = select
autoplay[require] = "apps/filemanager/lib/Functions.php"
autoplay[callback] = "filemanager_yes_no"

speed[label] = "Transition Speed (milliseconds)"
speed[type] = text

ratio[label] = "Ratio (format: 16:9)"
ratio[type] = text
ratio[initial] = "16:9"

effect[label] = "Transition effect"
effect[type] = select
effect[require] = "apps/filemanager/lib/Functions.php"
effect[callback] = "filemanager_effect_list"
effect[initial] = fade

[filemanager/gallery]

label = "Images: Gallery"
icon = picture-o

path[label] = Folder
path[type] = select
path[require] = "apps/filemanager/lib/Functions.php"
path[callback] = "filemanager_list_folders"

order[label] = Sorting order
order[type] = select
order[require] = "apps/filemanager/lib/Functions.php"
order[callback] = "filemanager_gallery_order"

desc[label] = Show descriptions
desc[type] = select
desc[require] = "apps/filemanager/lib/Functions.php"
desc[callback] = "filemanager_yes_no"

style[label] = Display style
style[type] = select
style[require] = "apps/filemanager/lib/Functions.php"
style[callback] = "filemanager_style_list"

[filemanager/audio]

label = "Audio Player (MP3)"
icon = headphones
acl = filemanager

file[label] = MP3 Audio
file[type] = file

[filemanager/video]

label = "Video Player (MP4)"
icon = video-camera
acl = filemanager

file[label] = MP4 Video
file[type] = file

[filemanager/videogif]

label = "Video GIF (MP4)"
icon = video-camera
acl = filemanager

file[label] = MP4 Video
file[type] = file

gif[label] = "GIF Fallback (optional)"
gif[type] = file

[filemanager/swf]

label = "Flash Player (SWF)"
icon = flash
acl = filemanager

file[label] = SWF Flash File
file[type] = file

; */ ?>
