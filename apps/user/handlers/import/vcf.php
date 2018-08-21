<?php

/**
 * Implements a VCF importer.
 */

$this->require_acl ('admin', 'user');

$page->layout = 'admin';
$page->title = __ ('VCF importer');

$f = new Form ('post');

if ($f->submit ()) {
	// some browsers may urlencode the file name
	$_FILES['import_file']['name'] = urldecode ($_FILES['import_file']['name']);
	
	if (preg_match ('/\.(vcf|vcard)$/i', $_FILES['import_file']['name'])) {
		if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/user_import.vcf')) {
			$file = 'cache/user_import.vcf';
		
			$imported = 0;

			$cards = new vCard ($file);
		
			if (count ($cards) === 1) {
				$cards = array ($cards);
			}

			foreach ($cards as $card) {
				$u = new User (array (
					'name' => isset ($card->fn[0]) ? $card->fn[0] : '',
					'email' => isset ($card->email[0]) ? $card->email[0]['Value'] : '',
					'company' => isset ($card->org[0]) ? $card->org[0]['Name'] : '',
					'title' => isset ($card->title[0]) ? $card->title[0] : '',
					'website' => isset ($card->url[0]) ? $card->url[0]['Value'] : '',
					'photo' => (isset ($card->photo[0]) && $card->photo[0]['Encoding'] === 'uri') ? $card->photo[0]['Value'] : '',
					'about' => '',
					'phone' => isset ($card->tel[0]) ? $card->tel[0]['Value'] : '',
					'address' => isset ($card->adr[0]) ? $card->adr[0]['StreetAddress'] : '',
					'address2' => '',
					'city' => isset ($card->adr[0]) ? $card->adr[0]['Locality'] : '',
					'state' => isset ($card->adr[0]) ? $card->adr[0]['Region'] : '',
					'country' => isset ($card->adr[0]) ? $card->adr[0]['Country'] : '',
					'zip' => isset ($card->adr[0]) ? $card->adr[0]['PostalCode'] : '',
					'password' => '',
					'type' => 'member',
					'expires' => gmdate ('Y-m-d H:i:s'),
					'signed_up' => gmdate ('Y-m-d H:i:s'),
					'updated' => gmdate ('Y-m-d H:i:s')
				));
	
				if ($u->email === '' || ! Validator::validate ($u->email, 'unique', '#prefix#user.email')) {
					continue;
				}

				if ($u->put ()) {
					Versions::add ($u);
					$imported++;
				}
			}
			
			echo '<p>' . __ ('Imported %d members.', $imported) . '</p>';
			echo '<p><a href="/user/admin">' . __ ('Continue') . '</a></p>';
			return;
		} else {
			echo '<p><strong>' . __ ('Error uploading file.') . '</strong></p>';
		}
	} else {
		echo '<p><strong>' . __ ('Please upload a VCF or VCARD file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('user/import/vcf', $o);
