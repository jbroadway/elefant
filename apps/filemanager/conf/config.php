; <?php /*

[General]

; Set a custom proxy handler (e.g., "aws/s3") to serve
; file requests instead of handling them directly.
proxy_handler = Off

[Admin]

handler = filemanager/index
name = Files
install = filemanager/upgrade
upgrade = filemanager/upgrade
version = 1.3.0-beta

; */ ?>