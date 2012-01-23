<?php

namespace blog;

require_once ('apps/blog/lib/CsvParser.php');

class CsvParserTest extends \PHPUnit_Framework_TestCase {
	function test_determine_delimiter () {
		$string = "One\tTwo\tThree";
		$this->assertEquals ("\t", CsvParser::determine_delimiter ($string));

		$string = "One,Two,Three";
		$this->assertEquals (',', CsvParser::determine_delimiter ($string));
	}

	function test_parse_line () {
		$string = "One\tTwo\tThree";
		CsvParser::determine_delimiter ($string);
		$this->assertEquals (
			array ('One', 'Two', 'Three'),
			CsvParser::parse_line ($string)
		);

		$string = "Test,\"<p>Testing \"\"one\"\" two 'three'<br />\nfour five.</p>\",\"one, two\"";
		CsvParser::determine_delimiter ($string);
		$this->assertEquals (
			array ('Test', "<p>Testing \"one\" two 'three'<br />\nfour five.</p>", 'one, two'),
			CsvParser::parse_line ($string)
		);
	}

	function test_parse () {
		$string = "One,Two,Three\nFour,Five,Six";
		$parsed = CsvParser::parse ($string);

		$this->assertEquals (2, count ($parsed));
		$this->assertEquals ('Two', $parsed[0][1]);
		$this->assertEquals ('Six', $parsed[1][2]);

		$string = "Title,Date,Author,Published,Content,Tags
This is a test,2012-01-16 3:23 AM,jbroadway,Yes,\"<p>Testing \"\"one\"\" two 'three'<br />\\nfour five.</p>\",\"one, two\"
This is also a test,2012-01-20 12:23 PM,jbroadway,No,\"<p>Testing \"\"one\"\" two 'three'<br />\\nfour five.</p>\",\"three, four\"";
		$parsed = CsvParser::parse ($string);

		$this->assertEquals (3, count ($parsed));
		$this->assertEquals ('Content', $parsed[0][4]);
		$this->assertEquals ('2012-01-16 3:23 AM', $parsed[1][1]);
		$this->assertEquals ("<p>Testing \"one\" two 'three'<br />\\nfour five.</p>", $parsed[1][4]);
		$this->assertEquals ("one, two", $parsed[1][5]);
		$this->assertEquals ('This is also a test', $parsed[2][0]);
		$this->assertEquals ("three, four", $parsed[2][5]);
	}
}

?>