<?php

// This file is load in event/main_listener.php


/**
 *
 * Reputation System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'REPUTATION'			=> 'Reputación',
	'ADD_REP'   			=> 'Aumentar reputación',
	'REM_REP'   			=> 'Disminuir reputación',
	'ADD_OR_DEL_REP'		=> '<strong>%s</strong> la reputación del usuario en 1'
));
