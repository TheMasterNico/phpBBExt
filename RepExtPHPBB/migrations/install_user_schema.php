<?php
/**
 *
 * Reputation System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\RepExtPHPBB\migrations;

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_rep');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v321');
	}
	/*
		Modificamos la tabla de usuario y creamos nuevas tablas
	 */
	public function update_schema()
	{
		return array(
			//Agregamos nueva tabla
			'add_tables'		=> array(
				$this->table_prefix . 'reputation'	=> array(
					'COLUMNS'		=> array(
						'rep_id'			=> array('UINT', null, 'auto_increment'),
						'forum_id'			=> array('UINT', 0),
						'topic_id'			=> array('UINT', 0),
						'post_id'			=> array('UINT', 0), // ID del post
						'user_id'			=> array('UINT', 0), // El que envía la rep
						'poster_id'			=> array('UINT', 0), // ID del user que realizo el post
						'rep_time'			=> array('TIMESTAMP', 0), //fecha de la rep
						'rep_text'			=> array('MTEXT', ''), // Comentario de la rep
						'rep_power'			=> array('UINT', 1), // Cantidad de reputación
						'rep_action'		=> array('BOOL', 1), // 1 add rep :: 0 remove rep
					),
					'PRIMARY_KEY'	=> 'rep_id',
					/*'KEYS'			=> array(
						'id_forum'			=> array('UNIQUE', 'forum_id'),
						'id_topic'			=> array('UNIQUE', 'topic_id'),
						'id_post'			=> array('UNIQUE', 'post_id'),
						'id_user'			=> array('UNIQUE', 'user_id'),
						'id_poster'			=> array('UNIQUE', 'poster_id'),
					),*/
				),
			),
			'add_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_rep'					=> array('INT:10', 0), // Cantidad de reputación
					'user_rep_enable'			=> array('BOOL', 1), // Habilitada la reputación
					'user_rep_email'			=> array('BOOL', 0), // Notificación por email
					'user_rep_notification'		=> array('BOOL', 1), // Notificación por el sistema de notificación
					'user_rep_toplist'			=> array('BOOL', 1), // Quiere ver el top list
					'user_rep_toplist_count'	=> array('TINT:2', 5), // Cantidad de usuarios en el top list
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_rep',
					'user_rep_enable',
					'user_rep_email',
					'user_rep_notification',
					'user_rep_toplist',
					'user_rep_toplist_count',
				),
			),
			'drop_tables'		=> array(
				$this->table_prefix . 'reputation',
			),
		);
	}
}
