; <?php /*

[General]

; Set a custom proxy handler (e.g., "aws/s3") to serve
; file requests instead of handling them directly.
proxy_handler = Off

; To enable image editing in the file manager, register
; for an API key with Aviary.com and enter it here.
aviary_key = Off

[Admin]

handler = filemanager/index
name = Files
install = filemanager/upgrade
upgrade = filemanager/upgrade
version = 1.3.2-beta
platforms = desktop, tablet

; */ ?>
