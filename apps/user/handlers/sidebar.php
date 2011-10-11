<?php

/**
 * User login/registration sidebar handler.
 */

echo $tpl->render ('user/sidebar', array ('login_methods' => $appconf['User']['login_methods']));

?>