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

dimensions[label] = "Dimensions (format: WIDTHxHEIGHT)"
dimensions[type] = text

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

label = "Embedded Audio (MP3)"
icon = headphones
acl = filemanager

file[label] = MP3 Audio
file[type] = file

[filemanager/video]

label = "Embedded Video (MP4)"
icon = video-camera
acl = filemanager

file[label] = MP4 Video
file[type] = file

[filemanager/swf]

label = "Embedded Flash (SWF)"
icon = flash
acl = filemanager

file[label] = SWF Flash File
file[type] = file

; */ ?>
