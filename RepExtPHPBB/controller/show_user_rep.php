<?php
/**
 *
 * Reputation System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\RepExtPHPBB\controller;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Reputation System show_user_rep controller.
 */
class show_user_rep
{
	/* @var \phpbb\config\config */
	//protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	//protected $user;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\template\template $template
	 * @param \phpbb\db\driver\driver $db
	 *
	 * Cargamos la clase helper que tiene la funcion render
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request)
	{
		$this->helper = $helper;
        $this->template = $template;
		$this->db = $db;
		$this->request = $request;
	}

	/**
	 * Demo controller for route /rep/{metodo}/
	 *
	 * @param string $username
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 *
	 *
	 * Muestra la pagina view_members_rep.html
	 */
	public function handle($metodo)
	{
		if($metodo == 'comments')
		{

		}
		else if($metodo == 'add' || $metodo == 'rem')
		{
			$username 	= $this->request->variable('username', 	'', true, \phpbb\request\request_interface::POST);
			$action 	= $this->request->variable('action', 	'', true, \phpbb\request\request_interface::POST);
			$poster 	= $this->request->variable('poster', 	'', true, \phpbb\request\request_interface::POST);
			$topic 		= $this->request->variable('topic', 	'', true, \phpbb\request\request_interface::POST);
			$forum 		= $this->request->variable('forum', 	'', true, \phpbb\request\request_interface::POST);
			$post 		= $this->request->variable('post', 		'', true, \phpbb\request\request_interface::POST);
			$mensaje 	= $this->request->variable('msg', 		'', true, \phpbb\request\request_interface::POST);
			$actualrep 	= $this->request->variable('ActualRep', '', true, \phpbb\request\request_interface::POST);
			if(empty($forum) || empty($poster))
			{
				trigger_error("You shall not pass");
			}

			//Obtenemos el user_id del que envia la rep
			$GetIDSQL = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_build_array('SELECT', array(
					'username'     => $username,
				)
			);
			$Result = $this->db->sql_query_limit($GetIDSQL, 1);
			$user_id = (int) $this->db->sql_fetchfield('user_id', 0, $Result); // Obtenemos el dato del user_id de la consulta
			$this->db->sql_freeresult($Result);




			//Insertamos los datos en la tabla
			$insert_data = array(
				'forum_id'	=> $forum,
				'topic_id'	=> $topic,
				'post_id'	=> $post,
				'user_id'	=> $user_id,
				'poster_id'	=> $poster,
				'rep_time'	=> time(),
				'rep_text'	=> $this->db->sql_escape($mensaje),
				'rep_power'	=> 1, // Default, si luego quieres cambiarlo
				'rep_action'=> $action, // 1: Add - 0: Rem
			);
			$InsertSQL = 'INSERT INTO phpbb_reputation ' . $this->db->sql_build_array('INSERT', $insert_data);
			$this->db->sql_query($InsertSQL);


			$addorrem = ($action)?('user_rep+1'):('user_rep-1'); // En caso de cambiar el rep_power, cambiar acá tambien
			$UpdateSQL = 'UPDATE ' . USERS_TABLE . ' SET user_rep = '.$addorrem.' WHERE user_id = ' . $poster;
			$this->db->sql_query($UpdateSQL);


			$this->template->assign_var('CAN_UPDATE_REP', $action.'-'.$metodo);
			$this->template->assign_var('NEW_REP', ($action)?($actualrep+1):($actualrep-1));
			//return $metodo;
			return $this->helper->render('view_members_rep.html', $username);
			/*
			render(string $template_file,
				string $page_title = '',
				int $status_code = 200,
				bool $display_online_list = false,
				int $item_id,
				string $item = 'forum',
				bool $send_headers = false
			);
			*/
		}
	}
}
