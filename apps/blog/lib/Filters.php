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
 * Formats a date in the format YYYY-MM-DD HH:MM:SS into the
 * specifed format using `gmdate()`. The default format is
 * `F j, Y - g:ia`.
 */
function blog_filter_date ($ts, $format = 'F j, Y - g:ia') {
	$t = strtotime ($ts);
	return gmdate ($format, $t);
}

function blog_filter_csv_line ($line) {
	$o = '';
	foreach ($line as $field) {
		if (strlen ($field) > 50) {
			$field = substr ($field, 47) . '...';
		}
		$o .= '<td>' . Template::sanitize ($field) . '</td>';
	}
	return $o;
}

/**
 * Filter published yes/no/que to text.
 */
function blog_filter_published ($p) {
	if ($p === 'yes') {
		return __ ('Yes');
	} elseif ($p === 'no') {
		return __ ('No');
	}
	return __ ('Scheduled');
}

/**
* Truncates text.
*
* Cuts a string to the length of $length and replaces the last characters
* with the ending if the text is longer than length.
* 
* Originally from CakePHP - http://cakephp.org/
* With modifications by Alex Prokop
*
* @param string $text String to truncate.
* @param integer $length Length of returned string, including ellipsis.
* @param string $ending Ending to be appended to the trimmed string.
* @param boolean $exact If false, $text will not be cut mid-word
* @param boolean $considerHtml If true, HTML tags would be handled correctly
* @return string Trimmed string.
*/
function blog_filter_truncate ($text, $length = 100, $ending = '…', $exact = true, $considerHtml = true) {
        if($considerHtml){
                // if the plain text is shorter than the maximum length, return the whole text
                if(strlen(preg_replace('/<.*?>/', '', $text)) <= $length){
                        return $text;
                }

                // splits all html-tags to scanable lines
                preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

                $total_length = strlen($ending);
                $open_tags = array ();
                $truncate = '';

                foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                        if( ! empty($line_matchings[1])){
                        // if it's an “empty element” with or without xhtml-conform closing slash (f.e.)
                                if(preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])){
                                // do nothing
                                        // if tag is a closing tag (f.e. )
                                } else if(preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)){
                                        // delete tag from $open_tags list
                                        $pos = array_search($tag_matchings[1], $open_tags);
                                        if($pos !== false){
                                                unset($open_tags[$pos]);
                                        }
                                // if tag is an opening tag (f.e. )
                                } else if(preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)){
                                        // add tag to the beginning of $open_tags list
                                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                                }
                                // add html-tag to $truncate'd text
                                $truncate .= $line_matchings[1];
                        }

                        // calculate the length of the plain text part of the line; handle entities as one character
                        $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                        if($total_length + $content_length > $length){
                                // the number of characters which are left
                                $left = $length - $total_length;
                                $entities_length = 0;
                                // search for html entities
                                if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)){
                                        // calculate the real length of all entities in the legal range
                                        foreach ($entities[0] as $entity) {
                                                if($entity[1] + 1 - $entities_length <= $left){
                                                        $left --;
                                                        $entities_length += strlen($entity[0]);
                                                } else{
                                                        // no more characters left
                                                        break;
                                                }
                                        }
                                }
                                $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                                // maximum lenght is reached, so get off the loop
                                break;
                        } else{
                                $truncate .= $line_matchings[2];
                                $total_length += $content_length;
                        }

                        // if the maximum length is reached, get off the loop
                        if($total_length >= $length){
                                break;
                        }
                }
        } else{
                if(strlen($text) <= $length){
                        return $text;
                } else{
                        $truncate = substr($text, 0, $length - strlen($ending));
                }
        }

        // if the words shouldn't be cut in the middle...
        if( ! $exact){
                // ...search the last occurance of a space...
                $spacepos = strrpos($truncate, ' ');
                if(isset($spacepos)){
                        // ...and cut the text in this position
                        $truncate = substr($truncate, 0, $spacepos);
                }
        }

        // add the defined ending to the text
        $truncate .= $ending;

        if($considerHtml){
                // close all unclosed html-tags
                foreach ($open_tags as $tag) {
                        $truncate .= '</' . $tag . '>';
                }
        }

        return $truncate;
}
