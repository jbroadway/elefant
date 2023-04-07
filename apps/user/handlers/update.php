<?php

use PragmaRX\Google2FAQRCode\Google2FA;

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

// 2fa
$global_2fa = Appconf::user ('User', '2fa');
$form->data->global_2fa = $global_2fa;
$form->data->_2fa = isset ($u->userdata['2fa']) ? $u->userdata['2fa'] : 'on';

$form->data->photo_url = $form->data->photo;
if ($form->data->photo_url != '' && strpos ($form->data->photo_url, '/') != 0) {
	$form->data->photo_url = '/' . $form->data->photo_url;
}

$page->title = __ ('Update Profile');

echo $form->handle (function ($form) use ($u, $page) {
	if (user\Rules::email_in_use ($_POST['email'], User::val ('id'))) {
		$form->failed[] = 'email-in-use';
		return false;
	}

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
		// some browsers may urlencode the file name
		$_FILES['photo']['name'] = urldecode ($_FILES['photo']['name']);
		
		if (! preg_match ('/\.(png|jpe?g)$/i', $_FILES['photo']['name'])) {
			$form->failed[] = 'photo';
			return false;
		}
		
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

	// 2-factor authentication
	$was_2fa_enabled = isset ($u->userdata['2fa']) ? ($u->userdata['2fa'] == 'on') : false;
	$is_2fa_enabled = false;

	if ($global_2fa == 'all' || ($global_2fa == 'admin' && User::require_admin ())) {
		$_POST['_2fa'] = 'on';
	}

	if ($_POST['_2fa'] == 'on') {
		$is_2fa_enabled = true;
		$data = $u->userdata;
		if (! isset ($data['2fa_secret'])) {
			$g2fa = new Google2FA ();
			$data['2fa_secret'] = $g2fa->generateSecretKey (32);
		}
		$data['2fa'] = 'on';
		$u->userdata = $data;
	} else {
		$is_2fa_enabled = false;
		$data = $u->userdata;
		$data['2fa'] = 'off';
		$u->userdata = $data;
	}

	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		if (!$was_2fa_enabled && $is_2fa_enabled) {
			$this->redirect ('/user/update2fa');
		}

		$page->title = __ ('Profile Updated');
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
		return;
	}
	@error_log ('Error updating profile (#' . $u->id . '): ' . $u->error);
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('Please try again later.') . '</p>';
	echo '<p><a href="/user">' . __ ('Back') . '</a></p>';
});
