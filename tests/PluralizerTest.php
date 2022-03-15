<?php

use PHPUnit\Framework\TestCase;

class PluralizerTest extends TestCase {
	function test_pluralize () {
		$this->assertEquals ('gems', Pluralizer::pluralize ('gem'));
		$this->assertEquals ('idioms', Pluralizer::pluralize ('idiom'));
		$this->assertEquals ('crises', Pluralizer::pluralize ('crisis'));
		$this->assertEquals ('guys', Pluralizer::pluralize ('guy'));
		$this->assertEquals ('theories', Pluralizer::pluralize ('theory'));
		$this->assertEquals ('strategies', Pluralizer::pluralize ('strategy'));
		$this->assertEquals ('people', Pluralizer::pluralize ('person'));
		$this->assertEquals ('men', Pluralizer::pluralize ('man'));
		$this->assertEquals ('women', Pluralizer::pluralize ('woman'));
		$this->assertEquals ('children', Pluralizer::pluralize ('child'));
		$this->assertEquals ('sheep', Pluralizer::pluralize ('sheep'));
		$this->assertEquals ('viruses', Pluralizer::pluralize ('virus'));
		$this->assertEquals ('octopuses', Pluralizer::pluralize ('octopus'));
		$this->assertEquals ('quizzes', Pluralizer::pluralize ('quiz'));
		$this->assertEquals ('axes', Pluralizer::pluralize ('axis'));
		$this->assertEquals ('indices', Pluralizer::pluralize ('index'));
		$this->assertEquals ('tomatoes', Pluralizer::pluralize ('tomato'));
		$this->assertEquals ('oxen', Pluralizer::pluralize ('ox'));
		$this->assertEquals ('mice', Pluralizer::pluralize ('mouse'));
		$this->assertEquals ('matrices', Pluralizer::pluralize ('matrix'));
		$this->assertEquals ('beliefs', Pluralizer::pluralize ('belief'));
		$this->assertEquals ('shoes', Pluralizer::pluralize ('shoe'));
		$this->assertEquals ('analyses', Pluralizer::pluralize ('analysis'));
		$this->assertEquals ('news', Pluralizer::pluralize ('news'));
		$this->assertEquals ('heroes', Pluralizer::pluralize ('hero'));
		$this->assertEquals ('echoes', Pluralizer::pluralize ('echo'));
		$this->assertEquals ('vetoes', Pluralizer::pluralize ('veto'));
		$this->assertEquals ('busses', Pluralizer::pluralize ('bus'));
		$this->assertEquals ('leaves', Pluralizer::pluralize ('leaf'));
		$this->assertEquals ('loathes', Pluralizer::pluralize ('loath'));
		$this->assertEquals ('thieves', Pluralizer::pluralize ('thief'));
		$this->assertEquals ('sheaves', Pluralizer::pluralize ('sheaf'));
		$this->assertEquals ('consortia', Pluralizer::pluralize ('consortium'));
		$this->assertEquals ('taxes', Pluralizer::pluralize ('tax'));
	}
}