<?php

require_once ('lib/Autoloader.php');

class TemplateTest extends PHPUnit_Framework_TestCase {
	function test_replace_vars () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_vars ('foo'), '<?php echo Template::sanitize ($data->foo, \'UTF-8\'); ?>');
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
	}

	function test_replace_strings () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_strings ('Don\'t'), '<?php echo i18n_get (\'Don\\\'t\'); ?>');
	}

	function test_replace_blocks () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_blocks ('end'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('endif'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('endforeach'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('else'), '<?php } else { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo'), '<?php if ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo.bar'), '<?php if ($GLOBALS[\'foo\']->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('if $_POST.value'), '<?php if ($_POST[\'value\']) { ?>');
		$this->assertEquals ($t->replace_blocks ('elseif foo'), '<?php } elseif ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo'), '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>');
		$this->assertEquals ($t->replace_blocks ('inc foo'), '<?php echo $this->render (\'foo\', $data); ?>');
	}

	function test_replace_includes () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_includes ('app/handler'), '<?php echo $this->controller->run (\'app/handler\', array ()); ?>');
		$this->assertEquals ($t->replace_includes ('app/handler?foo=bar&asdf=qwerty'), '<?php echo $this->controller->run (\'app/handler\', array (\'foo\' => \'bar\', \'asdf\' => \'qwerty\')); ?>');
	}

	function test_parse_template () {
		$t = new Template ('UTF-8');

		$data = '{% foreach foo %}{% if loop_index == 1 %}{{ loop_value|none }}{% end %}{% end %}';
		$out = '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>'
			. '<?php if ($data->loop_index == 1) { ?><?php echo $data->loop_value; ?>'
			. '<?php } ?><?php } ?>';
		$this->assertEquals ($t->parse_template ($data), $out);
		$this->assertEquals ($t->parse_template ('{" Hello "}'), '<?php echo i18n_get (\'Hello\'); ?>');
	}

	function test_sanitize () {
		$t = new Template ('UTF-8');

		$this->assertEquals (
			$t->sanitize ('<script type="text/javascript">eval ("alert (typeof window)")</script>'),
			'&lt;script type=&quot;text/javascript&quot;&gt;eval (&quot;alert (typeof window)&quot;)&lt;/script&gt;'
		);
	}

	function test_escape () {
		$t = new Template ('UTF-8');

		$this->assertEquals (
			'one {{ two }} three',
			$t->parse_template ('one \\{{ two \\}} three')
		);
	}
}

?>