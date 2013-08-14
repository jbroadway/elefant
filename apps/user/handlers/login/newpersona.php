<?php

User::init_session ();

$_POST['email'] = $_SESSION['persona/email'];
$_POST['redirect'] = $_SESSION['persona/redirect'];
$_POST['token'] = $_SESSION['persona/token'];

echo $this->run ('user/login/newuser');

?>