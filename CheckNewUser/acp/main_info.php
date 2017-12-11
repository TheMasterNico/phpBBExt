<?php
/**
 *
 * Check New User. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\CheckNewUser\acp;

/**
 * Check New User ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\TheMasterNico\CheckNewUser\acp\main_module',
			'title'		=> 'ACP_CHECK_USER',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'SETTINGS',
					'auth'	=> 'ext_TheMasterNico/CheckNewUser && acl_a_board',
					'cat'	=> array('ACP_CHECK_USER')
				),
			),
		);
	}
}
