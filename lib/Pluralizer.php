<?php

/**
 * Simple class for converting singular words to plural in English.
 *
 * Usage:
 *
 *     <?php
 *     
 *     echo Pluralizer::pluralize ('person'); // people
 */
class Pluralizer {
	/**
	 * Corrections to improper pluralizations.
	 */
	public static $plural_corrections = [
		'persons' => 'people',
		'peoples' => 'people',
		'mans' => 'men',
		'mens' => 'men',
		'womans' => 'women',
		'womens' => 'women',
		'childs' => 'children',
		'childrens' => 'children',
		'sheeps' => 'sheep',
		'quizs' => 'quizzes',
		'axises' => 'axes',
		'buffalos' => 'buffaloes',
		'tomatos' => 'tomatoes',
		'potatos' => 'potatoes',
		'echos' => 'echoes',
		'heros' => 'heroes',
		'vetos' => 'vetoes',
		'oxes' => 'oxen',
		'mouses' => 'mice',
		'louses' => 'lice',
		'matrixes' => 'matrices',
		'vertexes' => 'vertices',
		'indexes' => 'indices',
		'gemses' => 'gems',
		'newses' => 'news',
		'buses' => 'busses',
		'leafs' => 'leaves',
		'loaths' => 'loathes',
		'thiefs' => 'thieves',
		'sheafs' => 'sheaves'
	];

	/**
	 * Pluralize an English word.
	 */
	public static function pluralize ($word) {
		$word .= 's';
		$word = preg_replace ('/(x|ch|sh|ss])s$/', '\1es', $word);
		$word = preg_replace ('/ss$/', 'ses', $word);
		$word = preg_replace ('/([ti])ums$/', '\1a', $word);
		$word = preg_replace ('/sises$/', 'ses', $word);
		$word = preg_replace ('/([^aeiouy]|qu)ys$/', '\1ies', $word);
		$word = preg_replace ('/(?:([^f])fe|([lr])f)s$/', '\1\2ves', $word);
		$word = preg_replace ('/ieses$/', 'ies', $word);
		if (isset (self::$plural_corrections[$word])) {
			return self::$plural_corrections[$word];
		}
		return $word;
	}
}