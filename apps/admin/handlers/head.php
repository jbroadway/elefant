<?php

global $user;

if (User::is_valid () && $user->type == 'admin') {
	echo $tpl->render ('admin/head');
}

?>