<?php
/**
 *
 * Check New User. An extension for the phpBB Forum Software package.
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

$lang = array_merge($lang, array(
	'ACTIVAR_EXPLAIN' 	=> 'Aquí­ podrás activar los usuarios inactivos o recién registrados. Revisa muy bien que el correo sea legitimo y no tenga mas de una cuenta asociada a la misma IP.',
	'START_ACTIVAR' 	=> 'Selecciona la opción que consideres adecuada para cada usuario.',
	'ACT_NOMBRE' 		=> 'Nombre',
	'ACT_MAIL' 			=> 'Correo',
	'ACT_REGISTER' 		=> 'Registrado',
	'ACT_SAME_IP' 		=> 'Misma IP',
	'ACT_OPTIONS'		=>	'Opciones',
));
