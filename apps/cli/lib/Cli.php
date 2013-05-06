<?php

/**
 * Command line utility methods.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // default output
 *     Cli::out ('Some info here...');
 *     
 *     // print an error message
 *     Cli::out ('Error: Something bad happened.', 'error');
 *     
 *     // print a success message
 *     Cli::out ('All is well, captain.', 'success');
 *     
 *     // print a block of text
 *     Cli::block ("Options: <info>one, two, three</info>\n");
 *     
 *     ?>
 */
class Cli {
	/**
	 * Colors for output.
	 */
	public static $colors = array (
		'success' => "\033[0;32m%s\033[0m",
        'error' => "\033[31;31m%s\033[0m",
        'info' => "\033[33;33m%s\033[0m",
        'default' => "%s"
    );

	/**
	 * Print a line of output, with optional color highlighting.
	 */
	public static function out ($text, $type = 'default', $newline = "\n") {
		if (! isset (self::$colors[$type])) {
			$type = 'default';
		}

		printf (self::$colors[$type] . $newline, $text);
	}

	/**
	 * Print a block of text, replacing tags with color highlighting,
	 * for example:
	 *
	 *     Options: <info>one, two, three</info>
	 */
	public static function block ($text) {
		echo strtr (
			$text,
			array (
				'<success>'  => "\033[0;32m",
				'</success>' => "\033[0m",
				'<error>'    => "\033[31;31m",
				'</error>'   => "\033[0m",
				'<info>'     => "\033[33;33m",
				'</info>'    => "\033[0m"
			)
		);
	}
}

?>