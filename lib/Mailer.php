<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * For Zend's own inclusions.
 */
ini_set ('include_path', ini_get ('include_path') . PATH_SEPARATOR . 'lib/vendor');
require_once ('Zend/Mail.php');
require_once ('Zend/Mime/Part.php');

/**
 * Mailer is a wrapper around Zend_Mail configured from Elefant's global
 * mail settings.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // Using Mailer::send() as a wrapper:
 *     $mail = Mailer::send (array (
 *         'to'          => array ('user@example.com', 'Joe User'),
 *         'from'        => array ('me@widgets.com', 'Alternate Sender'),
 *         'cc'          => array ('me@widgets.com', 'CC This Guy'),
 *         'bcc'         => array ('me@widgets.com', 'BCC This Guy'),
 *         'reply_to'    => array ('me@widgets.com', 'Reply to This Guy'),
 *         'subject'     => 'Subject line',
 *         'text'        => 'This is a plain text message.',
 *         'html'        => 'This is an <b>html</b> message.',
 *         'attachments' => array (
 *             'File data...',
 *             // or
 *             new Zend_Mime_Part ('File data...')
 *         )
 *     );
 *     
 *     // Using the Zend_Mail object directly:
 *     $mail = Mailer::getInstance ();
 *     $mail->setBodyText ('This is an email message.');
 *     $mail->setSubject ('Subject line');
 *     $mail->addTo ('user@example.com', 'Joe User');
 *     // Etc.
 *     $mail->send ();
 *     
 *     ?>
 */
class Mailer {
	/**
	 * The parsed configurations from the `[Mailer]` section of the
	 * global configuration.
	 */
	private static $config = null;

	/**
	 * `Zend_Mail` object.
	 */
	private static $mailer = null;

	/**
	 * `Zend_Mail_Transport` object.
	 */
	private static $transport = null;

	/**
	 * If `getInstance()` fails, this will contain the error message.
	 */
	public static $error = false;

	/**
	 * Configures and fetches a singleton instance of `Zend_Mail`.
	 * Also clears settings from previous use onthe `Zend_Mail` object
	 * unless you call `getInstance(false)`.
	 */
	public static function getInstance ($clear = true) {
		// Has it been requested before?
		if (self::$mailer === null) {
			// Parse/verify configuration file
			self::$config = conf ('Mailer');

			// Create the new Zend_Mail object
			self::$mailer = new Zend_Mail ('UTF-8');

			// Set the transport method
			if (! isset (self::$config['transport']) || ! isset (self::$config['transport']['type'])) {
				self::$error = 'No transport method defined.';
				return false;
			}
			switch (self::$config['transport']['type']) {
				case 'smtp':
					$config = self::$config['transport'];
					$host = $config['host'];
					unset ($config['host']);
					unset ($config['type']);
					require_once ('Zend/Mail/Transport/Smtp.php');
					self::$transport = new Zend_Mail_Transport_Smtp ($host, $config);
					break;

				case 'sendmail':
					require_once ('Zend/Mail/Transport/Sendmail.php');
					self::$transport = new Zend_Mail_Transport_Sendmail ();
					break;

				case 'file':
					if (! isset (self::$config['transport']['path'])) {
						self::$config['transport']['path'] = 'cache/mailer';
					}
					if (! file_exists (self::$config['transport']['path'])) {
						mkdir (self::$config['transport']['path']);
					}
					require_once ('Zend/Mail/Transport/File.php');
					self::$transport = new Zend_Mail_Transport_File (array (
						'path' => self::$config['transport']['path'],
						'callback' => function ($transport) {
							// Files are named DATETIME-RECIPIENT-RAND.tmp
							return $_SERVER['REQUEST_TIME'] . '-' . $transport->recipients . '-' . mt_rand () . '.tmp';
						}
					));
					break;
				default:
					self::$error = 'Unknown transport type.';
					return false;
			}
			Zend_Mail::setDefaultTransport (self::$transport);

			// Set default from info
			$email_from = (self::$config['email_from'] !== 'default') ? self::$config['email_from'] : envconf ('General', 'email_from');
			$email_name = (self::$config['email_name'] !== 'default') ? self::$config['email_name'] : envconf ('General', 'site_name');
			Zend_Mail::setDefaultFrom ($email_from, $email_name);
		}

		// Clear mailer settings
		if ($clear) {
			self::$mailer->clearRecipients ();
			self::$mailer->clearSubject ();
			self::$mailer->clearFrom ();
		}

		return self::$mailer;
	}

	/**
	 * Send a single message to a recipient. Handy for one-off messages.
	 * Returns the `Zend_Mail` object after calling `send()`, unless false
	 * is passed as the second parameter, in which case `send()` is not
	 * called and the `Zend_Mail` object is returned immediately.
	 */
	public static function send ($msg, $send = true) {
		if ($send === true && conf ('Mailer', 'use_resque')) {
			$GLOBALS['controller']->run ('resque/init');
			return Resque::enqueue ('email', 'Mailer', $msg, true);
		}

		$mailer = self::getInstance ();
		if ($mailer === false) {
			// see Mailer::$error for info
			return false;
		}

		if (isset ($msg['to'])) {
			if (is_array ($msg['to'])) {
				$mailer->addTo ($msg['to'][0], $msg['to'][1]);
			} else {
				$mailer->addTo ($msg['to']);
			}
		}

		if (isset ($msg['from'])) {
			if (is_array ($msg['from'])) {
				$mailer->setFrom ($msg['from'][0], $msg['from'][1]);
			} else {
				$mailer->setFrom ($msg['from']);
			}
		}

		if (isset ($msg['cc'])) {
			if (is_array ($msg['cc'])) {
				$mailer->addCc ($msg['cc'][0], $msg['cc'][1]);
			} else {
				$mailer->addCc ($msg['cc']);
			}
		}

		if (isset ($msg['bcc'])) {
			if (is_array ($msg['bcc'])) {
				$mailer->addBcc ($msg['bcc'][0], $msg['bcc'][1]);
			} else {
				$mailer->addBcc ($msg['bcc']);
			}
		}

		if (isset ($msg['reply_to'])) {
			if (is_array ($msg['reply_to'])) {
				$mailer->setReplyTo ($msg['reply_to'][0], $msg['reply_to'][1]);
			} else {
				$mailer->setReplyTo ($msg['reply_to']);
			}
		}

		if (isset ($msg['html'])) {
			$mailer->setBodyHtml ($msg['html']);
		}

		if (isset ($msg['text'])) {
			$mailer->setBodyText ($msg['text']);
		}

		if (isset ($msg['subject'])) {
			$mailer->setSubject ($msg['subject']);
		}

		if (isset ($msg['attachments'])) {
			foreach ($msg['attachments'] as $attachment) {
				if (is_object ($attachment)) {
					$mailer->addAttachment ($attachment);
				} else {
					$mailer->createAttachment ($attachment);
				}
			}
		}

		if ($send) {
			return $mailer->send ();
		}
		return $mailer;
	}

	/**
	 * Handles jobs from a Resque queue if `use_resque = On` is set in the
	 * `[Mailer]` configuration. Mailer will automatically queue messages
	 * when this setting is set, and this method performs the job at a
	 * later time.
	 */
	public function perform () {
		Mailer::send ($this->args, 2);
	}
}
