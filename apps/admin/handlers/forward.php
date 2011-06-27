<?php

if (isset ($data['to'])) {
	header ('Location: ' . $data['to']);
} elseif (isset ($_GET['to'])) {
	header ('Location: ' . $_GET['to']);
} else {
	return;
}
exit;

?>