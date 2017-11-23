<?php

/**
 * Install a Facebook Pixel on your website.
 *
 * Usage:
 *
 * 1. Configure Elefant with your Facebook Pixel ID by entering the
 * following command:
 *
 *     ./elefant appconf social.Facebook.pixel_id 1234567890
 *
 * 2. In the <head></head> your templates:
 *
 *     {! social/facebook/pixel !}
 *
 * 3. Edit pages considered to be conversion result pages, such as
 * thank you pages, then open the Dynamic Objects menu, and choose
 * "Facebook Pixel Conversion Event". Select the conversion event
 * and enter any additional info to track along with the conversion
 * event.
 *
 * 4. If you're writing an Elefant app, you can also track manually
 * in JavaScript via:
 *
 *     <script>
 *     fbq('track', 'Purchase', {'value':'9.99','currency':'USD'});
 *     </script>
 */

$pixel_id = Appconf::social ('Facebook', 'pixel_id');

if ($pixel_id == null || $pixel_id == false || $pixel_id == '') {
	echo '<!-- Error: Facebook Pixel ID has not been set -->';
	return;
}

echo $tpl->render ('social/facebook/pixel', ['pixel_id' => $pixel_id]);
