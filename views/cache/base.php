<html>
<head>
<title><?php echo htmlspecialchars ($data->title, ENT_QUOTES, 'UTF-8'); ?></title>
<?php echo $data->head; ?>
</head>
<body>
<h1><?php echo htmlspecialchars ($data->title, ENT_QUOTES, 'UTF-8'); ?></h1>
<?php echo $data->body; ?>
</body>
</html>