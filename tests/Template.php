<?php

require_once ('lib/Template.php');

class TemplateTest extends PHPUnit_Framework_TestCase {
	function test_template () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_vars ('foo'), '<?php echo htmlspecialchars ($data->foo, ENT_QUOTES, \'UTF-8\'); ?>');
		$this->assertEquals ($t->replace_vars ('foo|none'), '<?php echo $data->foo; ?>');
		$this->assertEquals ($t->replace_vars ('foo|strtoupper|strtolower'), '<?php echo strtolower (strtoupper ($data->foo)); ?>');
		$this->assertEquals ($t->replace_vars ('foo|date (\'F j\', %s)'), '<?php echo date (\'F j\', $data->foo); ?>');
		$this->assertEquals ($t->replace_vars ('User::foo|none'), '<?php echo User::foo; ?>');
		$this->assertEquals ($t->replace_vars ('User::foo ()|none'), '<?php echo User::foo (); ?>');
		$this->assertEquals ($t->replace_vars ('db_shift (\'select * from foo\')|none'), '<?php echo db_shift (\'select * from foo\'); ?>');
		$this->assertEquals ($t->replace_vars ('user.name|none'), '<?php echo $GLOBALS[\'user\']->name; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST[value]|none'), '<?php echo $_POST[value]; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST[\'value\']|none'), '<?php echo $_POST[\'value\']; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST.value|none'), '<?php echo $_POST[\'value\']; ?>');

		$this->assertEquals ($t->replace_blocks ('end'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('else'), '<?php } else { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo'), '<?php if ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo.bar'), '<?php if ($GLOBALS[\'foo\']->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('if $_POST.value'), '<?php if ($_POST[\'value\']) { ?>');
		$this->assertEquals ($t->replace_blocks ('elseif foo'), '<?php } elseif ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo'), '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>');

		$data = '{% foreach foo %}{% if loop_index == 1 %}{{ loop_value|none }}{% end %}{% end %}';
		$out = '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>'
			. '<?php if ($data->loop_index == 1) { ?><?php echo $data->loop_value; ?>'
			. '<?php } ?><?php } ?>';
		$this->assertEquals ($t->parse_template ($data), $out);
	}
}

?>