<?php

use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase {
	function test_replace_vars () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_vars ('foo'), '<?php echo Template::sanitize ($data->foo, \'UTF-8\'); ?>');
		$this->assertEquals ($t->replace_vars ('foo|none'), '<?php echo $data->foo; ?>');
		$this->assertEquals ($t->replace_vars ('foo|strtoupper|strtolower'), '<?php echo strtolower (strtoupper ($data->foo)); ?>');
		$this->assertEquals ($t->replace_vars ('foo|date (\'F j\', %s)'), '<?php echo date (\'F j\', $data->foo); ?>');
		$this->assertEquals ($t->replace_vars ('User::foo|none'), '<?php echo User::foo; ?>');
		$this->assertEquals ($t->replace_vars ('User::foo ()|none'), '<?php echo User::foo (); ?>');
		$this->assertEquals ($t->replace_vars ('DB::shift (\'select * from foo\')|none'), '<?php echo DB::shift (\'select * from foo\'); ?>');
		$this->assertEquals ($t->replace_vars ('user.name|none'), '<?php echo $GLOBALS[\'user\']->name; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST[value]|none'), '<?php echo $_POST[\'value\']; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST[\'value\']|none'), '<?php echo $_POST[\'value\']; ?>');
		$this->assertEquals ($t->replace_vars ('$_POST.value|none'), '<?php echo $_POST[\'value\']; ?>');
		$this->assertEquals ($t->replace_vars ('foo = true'), '<?php $data->foo = true; ?>');
		$this->assertEquals ($t->replace_vars ('foo = "bar"'), '<?php $data->foo = "bar"; ?>');
	}

	function test_replace_strings () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_strings ('Don\'t'), '<?php echo __ (\'Don\\\'t\'); ?>');
	}

	function test_replace_blocks () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_blocks ('end'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('endif'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('endfor'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('endforeach'), '<?php } ?>');
		$this->assertEquals ($t->replace_blocks ('else'), '<?php } else { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo'), '<?php if ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('if !foo'), '<?php if (! $data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('if ! foo'), '<?php if (! $data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('if foo.bar'), '<?php if ($GLOBALS[\'foo\']->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('if $_POST.value'), '<?php if ($_POST[\'value\']) { ?>');
		$this->assertEquals ($t->replace_blocks ('elseif foo'), '<?php } elseif ($data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('elseif !foo'), '<?php } elseif (! $data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('elseif ! foo'), '<?php } elseif (! $data->foo) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo'), '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo as bar'), '<?php foreach ($data->foo as $data->loop_index => $data->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo as _k, _v'), '<?php foreach ($data->foo as $data->_k => $data->_v) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach bar in foo'), '<?php foreach ($data->foo as $data->loop_index => $data->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('foreach foo as _k, _v'), '<?php foreach ($data->foo as $data->_k => $data->_v) { ?>');
		$this->assertEquals ($t->replace_blocks ('for foo'), '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>');
		$this->assertEquals ($t->replace_blocks ('for foo as bar'), '<?php foreach ($data->foo as $data->loop_index => $data->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('for foo as _k, _v'), '<?php foreach ($data->foo as $data->_k => $data->_v) { ?>');
		$this->assertEquals ($t->replace_blocks ('for bar in foo'), '<?php foreach ($data->foo as $data->loop_index => $data->bar) { ?>');
		$this->assertEquals ($t->replace_blocks ('for _k, _v in foo'), '<?php foreach ($data->foo as $data->_k => $data->_v) { ?>');
		$this->assertEquals ($t->replace_blocks ('inc foo'), '<?php echo $this->render (\'foo\', self::add_parent_id ($data)); ?>');
	}

	function test_replace_includes () {
		$t = new Template ('UTF-8');

		$this->assertEquals ($t->replace_includes ('app/handler'), '<?php echo $this->controller->run (\'app/handler\', array ()); ?>');
		$this->assertEquals ($t->replace_includes ('app/handler?foo=bar&asdf=qwerty'), '<?php echo $this->controller->run (\'app/handler\', array (\'foo\' => \'bar\', \'asdf\' => \'qwerty\')); ?>');

		// Test sub-expressions
		$this->assertEquals ($t->replace_includes ('app/handler?foo=[bar]'), '<?php echo $this->controller->run (\'app/handler\', array (\'foo\' => Template::sanitize ($data->bar, \'UTF-8\'))); ?>');
		$this->assertEquals ($t->replace_includes ('app/handler?foo=[bar]&bar=a[sd]f'), '<?php echo $this->controller->run (\'app/handler\', array (\'foo\' => Template::sanitize ($data->bar, \'UTF-8\'), \'bar\' => \'a\' . Template::sanitize ($data->sd, \'UTF-8\') . \'f\')); ?>');
	}

	function test_parse_template () {
		$t = new Template ('UTF-8');

		$data = '{% foreach foo %}{% if loop_index == 1 %}{{ loop_value|none }}{% end %}{% end %}';
		$out = '<?php foreach ($data->foo as $data->loop_index => $data->loop_value) { ?>'
			. '<?php if ($data->loop_index == 1) { ?><?php echo $data->loop_value; ?>'
			. '<?php } ?><?php } ?>';
		$this->assertEquals ($t->parse_template ($data), $out);
		$this->assertEquals ($t->parse_template ('{" Hello "}'), '<?php echo __ (\'Hello\'); ?>');
	}

	function test_sanitize () {
		$this->assertEquals (
			Template::sanitize ('<script type="text/javascript">eval ("alert (typeof window)")</script>'),
			'&lt;script type=&quot;text/javascript&quot;&gt;eval (&quot;alert (typeof window)&quot;)&lt;/script&gt;'
		);
	}

	function test_quotes () {
		$this->assertEquals (
			Template::quotes ('Escape "double" quotes'),
			'Escape &quot;double&quot; quotes'
		);
	}

	function test_autolink () {
		$this->assertEquals (
			Template::autolink ('http://www.example.com/'),
			'<a rel="nofollow" href="http://www.example.com/">example.com</a>'
		);

		$this->assertEquals (
			Template::autolink ('http://www.example.com/'),
			'<a rel="nofollow" href="http://www.example.com/">example.com</a>'
		);

$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah'),
			'<a rel="nofollow" href="http://foo.com/blah_blah">foo.com/blah_blah</a>'
		);

$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah/'),
			'<a rel="nofollow" href="http://foo.com/blah_blah/">foo.com/blah_blah</a>'
		);

$this->assertEquals (
			Template::autolink ('(Something like http://foo.com/blah_blah)'),
			'(Something like <a rel="nofollow" href="http://foo.com/blah_blah">foo.com/blah_blah</a>)'
		);

$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah_(wikipedia)'),
			'<a rel="nofollow" href="http://foo.com/blah_blah_(wikipedia)">foo.com/blah_blah_(wikipedia)</a>'
		);

$this->assertEquals (
			Template::autolink ('(Something like http://foo.com/blah_blah_(wikipedia))'),
			'(Something like <a rel="nofollow" href="http://foo.com/blah_blah_(wikipedia)">foo.com/blah_blah_(wikipedia)</a>)'
		);

$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah.'),
			'<a rel="nofollow" href="http://foo.com/blah_blah">foo.com/blah_blah</a>.'
		);

		$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah/.'),
			'<a rel="nofollow" href="http://foo.com/blah_blah/">foo.com/blah_blah</a>.'
		);

		$this->assertEquals (
			Template::autolink ('<http://foo.com/blah_blah>'),
			'<<a rel="nofollow" href="http://foo.com/blah_blah">foo.com/blah_blah</a>>'
		);

		$this->assertEquals (
			Template::autolink ('<http://foo.com/blah_blah/>'),
			'<<a rel="nofollow" href="http://foo.com/blah_blah/">foo.com/blah_blah</a>>'
		);

		$this->assertEquals (
			Template::autolink ('http://foo.com/blah_blah,'),
			'<a rel="nofollow" href="http://foo.com/blah_blah">foo.com/blah_blah</a>,'
		);

		$this->assertEquals (
			Template::autolink ('http://www.example.com/wpstyle/?p=364.'),
			'<a rel="nofollow" href="http://www.example.com/wpstyle/?p=364">example.com/wpstyle</a>.'
		);

		$this->assertEquals (
			Template::autolink ('rdar://1234'),
			'<a rel="nofollow" href="rdar://1234">1234</a>'
		);

		$this->assertEquals (
			Template::autolink ('http://userid:password@example.com:8080'),
			'<a rel="nofollow" href="http://userid:password@example.com:8080">example.com</a>'
		);

		$this->assertEquals (
			Template::autolink ('http://example.com:8080 x-yojimbo-item://6303E4C1-xxxx-45A6-AB9D-3A908F59AE0E'),
			'<a rel="nofollow" href="http://example.com:8080">example.com</a> <a rel="nofollow" href="x-yojimbo-item://6303E4C1-xxxx-45A6-AB9D-3A908F59AE0E">6303E4C1-xxxx-45A6-AB9D-3A908F59AE0E</a>'
		);

		$this->assertEquals (
			Template::autolink ('message://%3c330e7f8409726r6a4ba78dkf1fd71420c1bf6ff@mail.gmail.com%3e'),
			'<a rel="nofollow" href="message://%3c330e7f8409726r6a4ba78dkf1fd71420c1bf6ff@mail.gmail.com%3e">mail.gmail.com%3e</a>'
		);

		$this->assertEquals (
			Template::autolink ('<tag>http://example.com</tag>'),
			'<tag><a rel="nofollow" href="http://example.com">example.com</a></tag>'
		);


	}

	function test_escape () {
		$t = new Template ('UTF-8');

		$this->assertEquals (
			'one {{ two }} three',
			$t->parse_template ('one \\{{ two \\}} three')
		);

		$this->assertEquals (
			'one {" two "} three',
			$t->parse_template ('one \\{" two \\"} three')
		);

		$this->assertEquals (
			'one {" two "} three',
			$t->parse_template ('one \\{\' two \\\'} three')
		);

		$this->assertEquals (
			'one {% two %} three',
			$t->parse_template ('one \\{% two \\%} three')
		);

		$this->assertEquals (
			'one {! two !} three',
			$t->parse_template ('one \\{! two \\!} three')
		);

		$this->assertEquals (
			'one {# two #} three',
			$t->parse_template ('one \\{# two \\#} three')
		);
	}
}
