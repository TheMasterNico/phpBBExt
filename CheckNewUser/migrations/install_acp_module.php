<?php
/**
 *
 * Check New User. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\CheckNewUser\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['acme_demo_goodbye']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			// Para que el admin sea el que active nuevas cuentas, cambiar el config require_activation de 1 a 2
			array('config.update', array('require_activation', 2)),

			array('module.add', array(
				'acp',
				'',
				'ACP_CHECK_USER'
			)),
			array('module.add', array(
				'acp',
				'ACP_CHECK_USER',
				array(
					'module_basename'	=> '\TheMasterNico\CheckNewUser\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
	public function revert_schema()
	{
		return array(
			array('config.update', array('require_activation', 1)),

		);
	}
}
