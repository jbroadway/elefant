<?php

global $user;

echo '<script src="/js/jquery-1.6.2.min.js"></script>';

if (User::is_valid () && $user->type == 'admin') {
	echo $tpl->render ('admin/head');
}

?>