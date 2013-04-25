<?php

@session_set_cookie_params (time () + conf ('General', 'session_duration'), '/', $domain);
@session_start ();

$_POST['email'] = $_SESSION['persona/email'];
$_POST['redirect'] = $_SESSION['persona/redirect'];
$_POST['token'] = $_SESSION['persona/token'];

echo $this->run ('user/login/newuser');

?>