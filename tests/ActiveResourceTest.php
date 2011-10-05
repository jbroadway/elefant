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
		$this->assertEquals ($t->_xml_entities ('<'), '&#60;');

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
}

?>