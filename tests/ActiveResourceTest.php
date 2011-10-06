<?php

require_once ('lib/ActiveResource.php');

class Test extends ActiveResource {
}

class ActiveResourceTest extends PHPUnit_Framework_TestCase {
	function test_construct () {
		$t = new Test (array ('foo' => 'bar'));

		$this->assertEquals ($t->foo, 'bar');
		$t->foo = 'asdf';
		$this->assertEquals ($t->foo, 'asdf');
		$this->assertEquals ($t->_data, array ('foo' => 'asdf'));
		$this->assertEquals ($t->element_name_plural, 'tests');
	}

	function test_build_xml () {
		$t = new Test;
		
		$this->assertEquals ($t->_build_xml (0, 'foo'), 'foo');
		$this->assertEquals ($t->_build_xml (0, array ('foo' => 'bar')), "<foo>bar</foo>\n");
		$this->assertEquals ($t->_build_xml ('foo', 'bar'), "<foo>bar</foo>\n");
		$this->assertEquals ($t->_build_xml ('foo', array ('bar' => 'asdf')), "<foo><bar>asdf</bar>\n</foo>\n");
		$this->assertEquals ($t->_build_xml ('foo', array ('@bar' => 'asdf')), "<foo bar=\"asdf\"></foo>\n");

		$xml = new SimpleXMLElement ('<foo><bar asdf="qwerty" />what</foo>');
		$this->assertEquals ($t->_build_xml (0, $xml), "<foo><bar asdf=\"qwerty\"/>what</foo>\n");
	}

	function test_pleuralize () {
		$t = new Test;
		
		$this->assertEquals ($t->pluralize ('person'), 'people');
		$this->assertEquals ($t->pluralize ('people'), 'people');
		$this->assertEquals ($t->pluralize ('man'), 'men');
		$this->assertEquals ($t->pluralize ('woman'), 'women');
		$this->assertEquals ($t->pluralize ('women'), 'women');
		$this->assertEquals ($t->pluralize ('child'), 'children');
		$this->assertEquals ($t->pluralize ('sheep'), 'sheep');
		$this->assertEquals ($t->pluralize ('octopus'), 'octopi');
		$this->assertEquals ($t->pluralize ('virus'), 'viruses');
		$this->assertEquals ($t->pluralize ('quiz'), 'quizzes');
		$this->assertEquals ($t->pluralize ('axis'), 'axes');
		$this->assertEquals ($t->pluralize ('axe'), 'axes');
		$this->assertEquals ($t->pluralize ('buffalo'), 'buffaloes');
		$this->assertEquals ($t->pluralize ('tomato'), 'tomatoes');
		$this->assertEquals ($t->pluralize ('potato'), 'potatoes');
		$this->assertEquals ($t->pluralize ('ox'), 'oxen');
		$this->assertEquals ($t->pluralize ('mouse'), 'mice');
		$this->assertEquals ($t->pluralize ('matrix'), 'matrices');
		$this->assertEquals ($t->pluralize ('vertex'), 'vertices');
		$this->assertEquals ($t->pluralize ('vortex'), 'vortexes');
		$this->assertEquals ($t->pluralize ('index'), 'indices');
		$this->assertEquals ($t->pluralize ('sandwich'), 'sandwiches');
		$this->assertEquals ($t->pluralize ('mass'), 'masses');
		$this->assertEquals ($t->pluralize ('fax'), 'faxes');
		$this->assertEquals ($t->pluralize ('pin'), 'pins');
		$this->assertEquals ($t->pluralize ('touch'), 'touches');
		$this->assertEquals ($t->pluralize ('sash'), 'sashes');
		$this->assertEquals ($t->pluralize ('bromium'), 'bromia');
		$this->assertEquals ($t->pluralize ('prophecy'), 'prophecies');
		$this->assertEquals ($t->pluralize ('crisis'), 'crises');
		$this->assertEquals ($t->pluralize ('life'), 'lives');
		$this->assertEquals ($t->pluralize ('wife'), 'wives');
		$this->assertEquals ($t->pluralize ('song'), 'songs');
		$this->assertEquals ($t->pluralize ('try'), 'tries');
		$this->assertEquals ($t->pluralize ('tree'), 'trees');
		$this->assertEquals ($t->pluralize ('tries'), 'tries');
		$this->assertEquals ($t->pluralize ('entry'), 'entries');
		$this->assertEquals ($t->pluralize ('entries'), 'entries');
	}

	function test_xml_entities () {
		$t = new Test;
		
		$this->assertEquals ($t->_xml_entities ('asdf'), 'asdf');
		$this->assertEquals ($t->_xml_entities ('<>'), '&lt;&gt;');
		$this->assertEquals ($t->_xml_entities ('"'), '&quot;');
		$this->assertEquals ($t->_xml_entities ('\''), '&apos;');
		$this->assertEquals ($t->_xml_entities ('ä'), '&#xE4;');
		$this->assertEquals ($t->_xml_entities ('£'), '&#xA3;');
		$this->assertEquals ($t->_xml_entities ('•'), '&#x2022;');
		$this->assertEquals ($t->_xml_entities ('À'), '&#xC0;');
		$this->assertEquals ($t->_xml_entities ('à'), '&#xE0;');
		$this->assertEquals ($t->_xml_entities ('Á'), '&#xC1;');
		$this->assertEquals ($t->_xml_entities ('á'), '&#xE1;');
		$this->assertEquals ($t->_xml_entities ('Â'), '&#xC2;');
		$this->assertEquals ($t->_xml_entities ('â'), '&#xE2;');
		$this->assertEquals ($t->_xml_entities ('Ã'), '&#xC3;');
		$this->assertEquals ($t->_xml_entities ('ã'), '&#xE3;');
		$this->assertEquals ($t->_xml_entities ('Ä'), '&#xC4;');
		$this->assertEquals ($t->_xml_entities ('ä'), '&#xE4;');
		$this->assertEquals ($t->_xml_entities ('Å'), '&#xC5;');
		$this->assertEquals ($t->_xml_entities ('å'), '&#xE5;');
		$this->assertEquals ($t->_xml_entities ('Æ'), '&#xC6;');
		$this->assertEquals ($t->_xml_entities ('æ'), '&#xE6;');
		$this->assertEquals ($t->_xml_entities ('Ç'), '&#xC7;');
		$this->assertEquals ($t->_xml_entities ('ç'), '&#xE7;');
		$this->assertEquals ($t->_xml_entities ('Ð'), '&#xD0;');
		$this->assertEquals ($t->_xml_entities ('ð'), '&#xF0;');
		$this->assertEquals ($t->_xml_entities ('È'), '&#xC8;');
		$this->assertEquals ($t->_xml_entities ('è'), '&#xE8;');
		$this->assertEquals ($t->_xml_entities ('É'), '&#xC9;');
		$this->assertEquals ($t->_xml_entities ('é'), '&#xE9;');
		$this->assertEquals ($t->_xml_entities ('Ê'), '&#xCA;');
		$this->assertEquals ($t->_xml_entities ('ê'), '&#xEA;');
		$this->assertEquals ($t->_xml_entities ('Ë'), '&#xCB;');
		$this->assertEquals ($t->_xml_entities ('ë'), '&#xEB;');
		$this->assertEquals ($t->_xml_entities ('Ì'), '&#xCC;');
		$this->assertEquals ($t->_xml_entities ('ì'), '&#xEC;');
		$this->assertEquals ($t->_xml_entities ('Í'), '&#xCD;');
		$this->assertEquals ($t->_xml_entities ('í'), '&#xED;');
		$this->assertEquals ($t->_xml_entities ('Î'), '&#xCE;');
		$this->assertEquals ($t->_xml_entities ('î'), '&#xEE;');
		$this->assertEquals ($t->_xml_entities ('Ï'), '&#xCF;');
		$this->assertEquals ($t->_xml_entities ('ï'), '&#xEF;');
		$this->assertEquals ($t->_xml_entities ('Ñ'), '&#xD1;');
		$this->assertEquals ($t->_xml_entities ('ñ'), '&#xF1;');
		$this->assertEquals ($t->_xml_entities ('Ò'), '&#xD2;');
		$this->assertEquals ($t->_xml_entities ('ò'), '&#xF2;');
		$this->assertEquals ($t->_xml_entities ('Ó'), '&#xD3;');
		$this->assertEquals ($t->_xml_entities ('ó'), '&#xF3;');
		$this->assertEquals ($t->_xml_entities ('Ô'), '&#xD4;');
		$this->assertEquals ($t->_xml_entities ('ô'), '&#xF4;');
		$this->assertEquals ($t->_xml_entities ('Õ'), '&#xD5;');
		$this->assertEquals ($t->_xml_entities ('õ'), '&#xF5;');
		$this->assertEquals ($t->_xml_entities ('Ö'), '&#xD6;');
		$this->assertEquals ($t->_xml_entities ('ö'), '&#xF6;');
		$this->assertEquals ($t->_xml_entities ('Ø'), '&#xD8;');
		$this->assertEquals ($t->_xml_entities ('ø'), '&#xF8;');
		$this->assertEquals ($t->_xml_entities ('Œ'), '&#x152;');
		$this->assertEquals ($t->_xml_entities ('œ'), '&#x153;');
		$this->assertEquals ($t->_xml_entities ('ß'), '&#xDF;');
		$this->assertEquals ($t->_xml_entities ('Þ'), '&#xDE;');
		$this->assertEquals ($t->_xml_entities ('þ'), '&#xFE;');
		$this->assertEquals ($t->_xml_entities ('Ù'), '&#xD9;');
		$this->assertEquals ($t->_xml_entities ('ù'), '&#xF9;');
		$this->assertEquals ($t->_xml_entities ('Ú'), '&#xDA;');
		$this->assertEquals ($t->_xml_entities ('ú'), '&#xFA;');
		$this->assertEquals ($t->_xml_entities ('Û'), '&#xDB;');
		$this->assertEquals ($t->_xml_entities ('û'), '&#xFB;');
		$this->assertEquals ($t->_xml_entities ('Ü'), '&#xDC;');
		$this->assertEquals ($t->_xml_entities ('ü'), '&#xFC;');
		$this->assertEquals ($t->_xml_entities ('Ý'), '&#xDD;');
		$this->assertEquals ($t->_xml_entities ('ý'), '&#xFD;');
		$this->assertEquals ($t->_xml_entities ('Ÿ'), '&#x178;');
		$this->assertEquals ($t->_xml_entities ('ÿ'), '&#xFF;');
		$this->assertEquals ($t->_xml_entities ('Ć'), '&#x106;');
		$this->assertEquals ($t->_xml_entities ('ć'), '&#x107;');
		$this->assertEquals ($t->_xml_entities ('Č'), '&#x10C;');
		$this->assertEquals ($t->_xml_entities ('č'), '&#x10D;');
		$this->assertEquals ($t->_xml_entities ('Đ'), '&#x110;');
		$this->assertEquals ($t->_xml_entities ('đ'), '&#x111;');
		$this->assertEquals ($t->_xml_entities ('Š'), '&#x160;');
		$this->assertEquals ($t->_xml_entities ('š'), '&#x161;');
		$this->assertEquals ($t->_xml_entities ('Ž'), '&#x17D;');
		$this->assertEquals ($t->_xml_entities ('ž'), '&#x17E;');
	}
}

?>