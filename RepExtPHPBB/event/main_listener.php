<?php
/**
 *
 * Reputation System. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\RepExtPHPBB\event;


/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reputation System Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	protected $TheUserID;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\user $user							User object
	* @param \phpbb\template\template $template			Template object
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template)
	{
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'load_language_file',
			'core.memberlist_prepare_profile_data'			=> 'get_user_rep_data', // Solo en el memberlist
			'core.viewtopic_cache_user_data'				=> 'get_rep_data_from_db',
			'core.viewtopic_modify_post_row'				=> 'post_row_reputation', // En los mensajes del tema
			'core.modify_user_rank'							=>	'change_user_rank',
			'core.index_modify_page_title'					=> 'show_top_list',
		);
	}

	public function show_top_list($event)
	{
		//echo $this->MaxTopList;
		$sql = 'SELECT user_id, username, user_colour, user_rep, user_avatar, user_avatar_width, user_avatar_height, user_avatar_type FROM '.USERS_TABLE.' ORDER BY user_rep DESC';
		$result = $this->db->sql_query_limit($sql, $this->MaxTopList, 0);
		$count = 1;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('toplist', array(
				'USERNAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'REP'			=> $row['user_rep'],
				'POS'			=> $count,
			));
			$count++;
		}

		$this->db->sql_freeresult($result);
	}

	public function change_user_rank($event)
	{
		/*
		*
		* Modificamos los "mensajes" totales del usuario. LE asignamos la rep que tiene actualmente. posts = rep
		* Así entonces el mismo sistema del foro le pondrá el rango adecuado
		*
		*/
		$event['user_posts'] = $event['user_data']['user_rep'];
		//echo "<pre>".print_r($event['user_posts'], true)."</pre>";
	}

	public function load_language_file($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'TheMasterNico/RepExtPHPBB',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;

		$this->TheUserID = $event['user_data']['user_id'];
		$this->UserRepEnable = $event['user_data']['user_rep_enable'];
		$this->MaxTopList = $event['user_data']['user_rep_toplist_count'];


		$this->template->assign_vars(array(
			'CAN_REP_OTHERS'   => $this->UserRepEnable,
			)
		);


		//echo "<pre>".print_r($event['user_data'], true)."</pre>";
	}

	public function get_user_rep_data($event)
	{
		$data = $event['data'];
		$template_data = $event['template_data'];

		$user_id = $data['user_id'];
		$GetCommentSQL = 'SELECT rep.*, users.username, users.user_colour, users.user_avatar, users.user_avatar_width, users.user_avatar_height, users.user_avatar_type
		FROM phpbb_reputation rep
		LEFT JOIN '.USERS_TABLE.' users
		ON rep.user_id = users.user_id
		WHERE rep.poster_id = '.$user_id.'
		ORDER BY rep_time DESC';
		$result = $this->db->sql_query_limit($GetCommentSQL, 15, 0); // El 15 es el limite de comentarios y empieza de 0

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation', array(
				'USERNAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'FORUM'			=> $row['forum_id'],
				'TOPIC'			=> $row['topic_id'],
				'POST'			=> $row['post_id'],
				'TIME'			=> $this->user->format_date($row['rep_time']),
				'COMENTARIO'	=> $row['rep_text'],
				'ACTION'		=> $row['rep_action'],
				'POWER'			=> $row['rep_power'],
			));
		}

		$this->db->sql_freeresult($result);
		// Obtener toda la data y hacer el pagination

		$template_data = array_merge($template_data, array(
			'MEMBER_USER_REP' 	=> $data['user_rep'], // Obtenemos la cantidad de reputación de la db
		));
		$event['template_data'] = $template_data;
		//echo "<pre>".print_r($event['template_data'], true)."</pre>";
	}

	public function get_rep_data_from_db($event)
	{
		/*
			Obtenemos el valor de "user_rep" desde la base de datos.
			El $event['row'] de aquí tiene los datos de la base de datos del usuario y del mensaje que enviá
		*/
		$user_cache_data = $event['user_cache_data'];
		$user_cache_data['cache_rep'] = $event['row']['user_rep']; // Obtenemos el valor de rep
		$user_cache_data['cache_rep_enable'] = $event['row']['user_rep_enable']; // Obtenemos si puede usar rep

		$event['user_cache_data'] = $user_cache_data; // Guardamos los cambios
		//echo "<pre>".print_r($event['row'], true)."</pre>";
	}

	public function post_row_reputation($event)
	{
		$user_poster_data = $event['user_poster_data']; // Aquí esta almacenado el cache_rep del $user_cache_data
		$post_row = $event['post_row']; // Aquí están todos los postrow.KEY. ej: postrow.POST_AUTHOR_FULL

		$sql = 'SELECT count(post_id) as reputacionado
		FROM phpbb_reputation
		WHERE post_id = '.$post_row['POST_ID'].'
		AND poster_id = ' . $post_row['POSTER_ID'] . '
		AND user_id = '. $this->TheUserID;
		$Result = $this->db->sql_query_limit($sql, 1);
		$reputacionado = (int) $this->db->sql_fetchfield('reputacionado', 0, $Result); // Obtenemos el dato del user_id de la consulta
		$this->db->sql_freeresult($Result);
		//echo $sql.'<br />'.$reputacionado.'<br />';

		$post_row = array_merge($post_row, array(
			'POST_USER_REP'		=> $user_poster_data['cache_rep'], // Creamos el postrow.POST_USER_REP
			'REPUTATIONED'		=> $reputacionado,
			'REP_ENABLE'		=> $user_poster_data['cache_rep_enable'],
		));

		/*
			POST_USER_REP tendrá el valor de "user_rep" de la base de datos
		*/
		$event['post_row'] = $post_row;
		//echo "<pre>".print_r($event['post_row'], true)."</pre>";
	}
}
