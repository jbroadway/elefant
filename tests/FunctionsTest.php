<?php

class FunctionsTest extends PHPUnit_Framework_TestCase {
	function test_simple_auth () {
		$verifier = function ($user, $pass) {
			return true;
		};
		$method = function ($callback) {
			return $callback ('', '');
		};
		$this->assertTrue (simple_auth ($verifier, $method));
	}

	function test_sql_split () {
		$sql = "select * from foo;\nselect * from bar";
		$split = sql_split ($sql);
		$this->assertEquals (2, count ($split));
		$this->assertEquals ("select * from foo\n", $split[0]);
		$this->assertEquals ("select * from bar\n", $split[1]);
	}

	function test_format_filesize () {
		$this->assertEquals (format_filesize (-25), '-25 b');
		$this->assertEquals (format_filesize (25), '25 b');
		$this->assertEquals (format_filesize (2500), '2 KB');
		$this->assertEquals (format_filesize (2500000), '2.4 MB');
		$this->assertEquals (format_filesize (25000000), '23.8 MB');
		$this->assertEquals (format_filesize (2500000000), '2.3 GB');
	}

	function test_detect () {
		// iPhone
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; XXXXX like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/241 Safari/419.3';
		$this->assertTrue (detect ('iphone'));
		$this->assertFalse (detect ('ipad'));
		$this->assertTrue (detect ('ios'));
		$this->assertTrue (detect ('webkit'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));

		// iPad
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10';
		$this->assertTrue (detect ('ipad'));
		$this->assertTrue (detect ('ios'));
		$this->assertTrue (detect ('webkit'));
		$this->assertFalse (detect ('mobile'));
		$this->assertTrue (detect ('tablet'));

		// iPod touch
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A101a Safari/419.3';
		$this->assertTrue (detect ('ipod'));
		$this->assertTrue (detect ('ios'));
		$this->assertTrue (detect ('webkit'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));

		// Android
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; U; Android 2.3.5; en-us; HTC Vision Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
		$this->assertTrue (detect ('android'));
		$this->assertTrue (detect ('webkit'));
		$this->assertTrue (detect ('mobile'));
		$this->assertTrue (detect ('tablet'));
		$this->assertFalse (detect ('msie'));

		// Blackberry
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (BlackBerry; U; BlackBerry 9850; en-US) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.0.0.115 Mobile Safari/534.11+';
		$this->assertTrue (detect ('blackberry'));
		$this->assertTrue (detect ('webkit'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('firefox'));

		// Windows phone
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)';
		$this->assertTrue (detect ('iemobile'));
		$this->assertTrue (detect ('windows phone os'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));

		// Opera mobile
		$_SERVER['HTTP_USER_AGENT'] = 'Opera/9.80 (Android 2.3.3; Linux; Opera Mobi/ADR-1111101157; U; es-ES) Presto/2.9.201 Version/11.50';
		$this->assertTrue (detect ('opera'));
		$this->assertTrue (detect ('opera mobi'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('bot'));

		// Opera mini
		$_SERVER['HTTP_USER_AGENT'] = 'Opera/9.80 (J2ME/MIDP; Opera Mini/9.80 (S60; SymbOS; Opera Mobi/23.348; U; en) Presto/2.5.25 Version/10.54';
		$this->assertTrue (detect ('opera'));
		$this->assertTrue (detect ('opera mini'));
		$this->assertTrue (detect ('mobile'));
		$this->assertFalse (detect ('msie'));

		// Chrome
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/18.6.872.0 Safari/535.2 UNTRUSTED/1.0 3gpp-gba UNTRUSTED/1.0';
		$this->assertTrue (detect ('chrome'));
		$this->assertTrue (detect ('webkit'));
		$this->assertFalse (detect ('mobile'));

		// Firefox
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:9.0a2) Gecko/20111101 Firefox/9.0a2';
		$this->assertTrue (detect ('firefox'));
		$this->assertTrue (detect ('ff'));
		$this->assertTrue (detect ('moz'));
		$this->assertTrue (detect ('gecko'));
		$this->assertFalse (detect ('webkit'));
		$this->assertFalse (detect ('mobile'));

		// MSIE 10.6
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0';
		$this->assertTrue (detect ('msie'));
		$this->assertTrue (detect ('msie 10'));
		$this->assertFalse (detect ('msie 9'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// MSIE 10
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)';
		$this->assertTrue (detect ('msie'));
		$this->assertTrue (detect ('msie 10'));
		$this->assertFalse (detect ('msie 9'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// MSIE 9
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))';
		$this->assertTrue (detect ('msie'));
		$this->assertFalse (detect ('msie 10'));
		$this->assertTrue (detect ('msie 9'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// MSIE 8
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
		$this->assertTrue (detect ('msie'));
		$this->assertFalse (detect ('msie 9'));
		$this->assertTrue (detect ('msie 8'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// MSIE 7
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/4.0(compatible; MSIE 7.0b; Windows NT 6.0)';
		$this->assertTrue (detect ('msie'));
		$this->assertFalse (detect ('msie 8'));
		$this->assertTrue (detect ('msie 7'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// MSIE 6
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
		$this->assertTrue (detect ('msie'));
		$this->assertFalse (detect ('msie 7'));
		$this->assertTrue (detect ('msie 6'));
		$this->assertTrue (detect ('ie'));
		$this->assertFalse (detect ('mobile'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('firefox'));

		// Opera
		$_SERVER['HTTP_USER_AGENT'] = 'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00';
		$this->assertTrue (detect ('opera'));
		$this->assertFalse (detect ('opera mobi'));
		$this->assertFalse (detect ('mobile'));

		// Safari
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1';
		$this->assertTrue (detect ('safari'));
		$this->assertTrue (detect ('webkit'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('mobile'));

		// Google bot
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		$this->assertTrue (detect ('googlebot'));
		$this->assertTrue (detect ('bot'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('mobile'));

		// Bing bot
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
		$this->assertFalse (detect ('googlebot'));
		$this->assertTrue (detect ('bot'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('mobile'));

		// Duck Duck Go bot
		$_SERVER['HTTP_USER_AGENT'] = 'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)';
		$this->assertTrue (detect ('bot'));
		$this->assertFalse (detect ('tablet'));
		$this->assertFalse (detect ('mobile'));
	}

	function test_rmdir_recursive () {
		mkdir ('rmdir_recursive_test');
		mkdir ('rmdir_recursive_test/a');
		mkdir ('rmdir_recursive_test/b');
		mkdir ('rmdir_recursive_test/b/c');
		mkdir ('rmdir_recursive_test/b/c/d');
		mkdir ('rmdir_recursive_test/b/c/d/e');
		touch ('rmdir_recursive_test/foo.txt');
		touch ('rmdir_recursive_test/a/foo.txt');
		touch ('rmdir_recursive_test/a/bar.txt');
		touch ('rmdir_recursive_test/b/c/foo.txt');
		touch ('rmdir_recursive_test/b/c/d/e/.foo');

		$this->assertTrue (rmdir_recursive ('rmdir_recursive_test'));
		$this->assertFalse (file_exists ('rmdir_recursive_test'));
	}
}

?>