<?php

// keep unauthorized users out
$this->require_admin ();

// set the layout and page title
$page->layout = 'admin';
$page->title = __ ('Files - Settings');

// create the form
$form = new Form ('post', $this);

// set the form data from the app settings
$form->data = array (
    'aviary_key' => Appconf::filemanager ('General', 'aviary_key')
);

echo $form->handle (function ($form) {
    // merge the new values into the settings
    $merged = Appconf::merge ('filemanager', array (
        'General' => array (
            'aviary_key' => $_POST['aviary_key']
        )
    ));

    // save the settings to disk
    if (! Ini::write ($merged, 'conf/app.filemanager.' . ELEFANT_ENV . '.php')) {
        printf (
            '<p>%s</p>',
            __ ('Unable to save changes. Check your permissions and try again.')
        );
        return;
    }

    // redirect to the main admin page with a notification
    $form->controller->add_notification (__ ('Settings saved.'));
    $form->controller->redirect ('/filemanager/index');
});

?>