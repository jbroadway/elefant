<?php

/**
 * Provides a modal dialog to browse for users.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('user/util/userchooser');
 *
 * Or anywhere in your view:
 *
 *     {! user/util/userchooser !}
 *
 * 2. User the $.userchooser() function to open the dialog window:
 *
 *     $.userchooser ({
 *         set_id_value: '#field-id',
 *         set_name_value: '#display-name',
 *         callback: function (id, name, email) {
 *             console.log (id);
 *             console.log (name);
 *             console.log (email);
 *         }
 *     });
 *
 * Options:
 *
 * - callback:        A function to call with the user id, name, and email.
 * - chosen:          A list of users that shouldn't be selectable.
 * - chosen_visible:  Whether to display the disabled chosen users or hide them.
 * - set_id_value:    The selector of an input or element to update with the user id.
 * - set_name_value:  The selector of an input or element to update with the user name.
 * - set_email_value: The selector of an input or element to update with the user email.
 * - set_mailto:      The selector of a link to set the mailto: value for.
 */

$this->run ('admin/util/fontawesome');
$this->run ('admin/util/modal');

$page->add_style ('/apps/user/css/userchooser.css');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_script ('/js/jquery.verify_values.js');
$page->add_script ('/apps/user/js/jquery.adduser.js');
$page->add_script ('/apps/user/js/jquery.userchooser.js');
$page->add_script (
	I18n::export (
		'Add Member',
		'Choose a Member',
		'Search',
		'Unable to load the member list. Please try again in a few seconds.'
	)
);
