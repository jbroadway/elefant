; <?php /*

[filemanager/slideshow]

label = "Images: Slideshow"
icon = picture-o

path[label] = Folder
path[type] = select
path[require] = "apps/filemanager/lib/Functions.php"
path[callback] = "filemanager_list_folders"

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

file[label] = MP3 Audio
file[type] = file

[filemanager/video]

label = "Embedded Video (MP4)"
icon = video-camera

file[label] = MP4 Video
file[type] = file

[filemanager/swf]

label = "Embedded Flash (SWF)"
icon = "/apps/filemanager/css/icon-swf.png"

file[label] = SWF Flash File
file[type] = file

; */ ?>