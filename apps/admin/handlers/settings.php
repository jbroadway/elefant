<?php

/**
 * Global site settings manager.
 */

// keep unauthorized users out
$this->require_acl ('admin', 'settings');

// set the layout and page title
$page->layout = 'admin';
$page->title = __ ('Site Settings');

// create the form
$form = new Form ('post', $this);

// set the form data from the global conf() settings
$form->data = array (
    'site_name' => conf ('General', 'site_name'),
    'site_domain' => conf ('General', 'site_domain') ? conf ('General', 'site_domain') : $_SERVER['HTTP_HOST'],
    'default_thumbnail' => conf ('General', 'default_thumbnail'),
    'email_from' => conf ('General', 'email_from'),
    'timezone' => conf ('General', 'timezone'),
    'google_analytics_id' => conf ('General', 'google_analytics_id')
);

echo $form->handle (function ($form) {
    // merge the new values into the settings
    $merged = Appconf::merge ('admin', array (
        'Site Settings' => array (
        	'site_name' => $_POST['site_name'],
        	'site_domain' => $_POST['site_domain'],
        	'default_thumbnail' => $_POST['default_thumbnail'],
            'email_from' => $_POST['email_from'],
            'timezone' => $_POST['timezone'],
            'google_analytics_id' => $_POST['google_analytics_id']
        )
    ));

    // save the settings to disk
    if (! Ini::write ($merged, 'conf/app.admin.' . ELEFANT_ENV . '.php')) {
        printf (
            '<p>%s</p>',
            __ ('Unable to save changes. Check your permissions and try again.')
        );
        return;
    }

    // redirect to the main admin page with a notification
    $form->controller->add_notification (__ ('Settings saved.'));
    $form->controller->redirect ('/');
});
