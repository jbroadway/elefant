<?php

/**
 * Enables a user to update their profile information.
 */

// Check for a custom handler override
$res = $this->override ('user/update');
if ($res) { echo $res; return; }

if (! User::require_login ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$u = User::$user;

$form = new Form ('post', $this);

$form->data = $u->orig ();
$form->data->password = '';
$form->data = $form->merge_values ($form->data);
$form->data->failed = $form->failed;
$form->data->_states = user\Data::states ();
$form->data->_countries = user\Data::countries ();
$page->title = __ ('Update Profile');

echo $form->handle (function ($form) use ($u, $page) {
	$u->name = $_POST['name'];
	$u->email = $_POST['email'];
	if (! empty ($_POST['password'])) {
		$u->password = User::encrypt_pass ($_POST['password']);
	}
	$u->about = $_POST['about'];
	$u->phone = $_POST['phone'];
	$u->address = $_POST['address'];
	$u->address2 = $_POST['address2'];
	$u->city = $_POST['city'];
	$u->state = $_POST['state'];
	$u->country = $_POST['country'];
	$u->zip = $_POST['zip'];
	$u->title = $_POST['title'];
	$u->company = $_POST['company'];
	$u->website = $_POST['website'];

	if (isset ($_FILES['photo']) && is_uploaded_file ($_FILES['photo']['tmp_name'])) {
		$tmp_file = 'cache/.' . basename ($_FILES['photo']['name']);
		$old_file = $u->photo;
		if (move_uploaded_file ($_FILES['photo']['tmp_name'], $tmp_file)) {
			if (preg_match ('/\.jpe?g$/i', $tmp_file)) {
				Image::reorient ($tmp_file);
			}
			
			$u->photo = Image::resize (
				$tmp_file,
				Appconf::user ('User', 'photo_width'),
				Appconf::user ('User', 'photo_height')
			);
			if (strpos ($u->photo, '#') !== false) {
				error_log ('Error processing photo: ' . $u->photo);
				$u->photo = $old_file;
			} elseif (! empty ($old_file) && $old_file !== $u->photo && file_exists ($old_file)) {
				unlink ($old_file);
			}
			unlink ($tmp_file);
		}
	}

	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$page->title = __ ('Profile Updated');
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
		return;
	}
	@error_log ('Error updating profile (#' . $u->id . '): ' . $u->error);
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('Please try again later.') . '</p>';
	echo '<p><a href="/user">' . __ ('Back') . '</a></p>';
});
