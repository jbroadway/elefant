<?php

if (! User::require_login ()) {}

User::logout ('/account');

?>