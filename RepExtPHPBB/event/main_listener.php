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

	/** @var \phpbb\request\request */
	protected $request;

	protected $TheUserID;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\user $user							User object
	* @param \phpbb\template\template $template			Template object
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template, \phpbb\request\request $request)
	{
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->request = $request;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'								=> 'load_language_file',
			'core.memberlist_prepare_profile_data'			=> 'get_user_rep_data', // Solo en el memberlist
			'core.viewtopic_cache_user_data'				=> 'get_rep_data_from_db',
			'core.viewtopic_modify_post_row'				=> 'post_row_reputation', // En los mensajes del tema
			'core.modify_user_rank'							=> 'change_user_rank',
			'core.index_modify_page_title'					=> 'show_top_list',
			'core.acp_users_overview_before'				=> 'acp_user_can_rep',
			'core.acp_users_overview_modify_data'			=> 'modificar_data_sql'
		);
	}

	public function modificar_data_sql($event)
	{
		$enableorno = $this->request->variable('can_rep', '', true, \phpbb\request\request_interface::POST);
		$enableorno_antes = $event['user_row']['user_rep_enable'];

		$count_rep_despues = $this->request->variable('count_rep', '', true, \phpbb\request\request_interface::POST);
		$count_rep_antes = $event['user_row']['user_rep'];

		$TotalComments = $this->request->variable('cantidad', '', false, \phpbb\request\request_interface::POST);
		$EliminarComentarios = $CantidadARestarSumar = 0;


		$SQLDelComment = "DELETE FROM phpbb_reputation WHERE rep_id IN (";
		for($i = 0; $i < $TotalComments; $i++)//Obtener todos los comentarios seleccionados para eliminar
		{
			$value = $this->request->variable('Comentario-'.$i, '', false, \phpbb\request\request_interface::POST);
			if(!empty($value)) // Si fue seleccionado
			{
				$EliminarComentarios++;
				//$Accion 1 : Restar || $Accion 0 : Sumar
				$Accion = $this->request->variable('SumarORestar-'.$i, '', false, \phpbb\request\request_interface::POST);
				$Power = $this->request->variable('CantidadSOR-'.$i, '', false, \phpbb\request\request_interface::POST);
				$SQLDelComment .= $value.",";
				if($Accion == 1) // Fue un +1. Así que vamos a Restar
				{
					$CantidadARestarSumar -= $Power;
				}
				else if($Accion == 0) // Fue un -1. Así que vamos a  Sumar
				{
					$CantidadARestarSumar += $Power;
				}
			}
		}
		$SQLDelComment .= ")";
		$SQLDelComment = str_replace(",)", ")", $SQLDelComment); // Quitar la ultima ","

		if($EliminarComentarios > 0)
		{
			//echo $SQLDelComment;
			$this->db->sql_freeresult($this->db->sql_query($SQLDelComment));
		//	echo "Cantidad a sumar: $CantidadARestarSumar<br />";
			//Modificamos la variable de cantidad de rep para que abajo se modifique
			//Se pone a sumar por que si $CantidadARestarSumar tiene negativo, se resta
			$count_rep_despues = $count_rep_antes+$CantidadARestarSumar;
		}

		if($enableorno != $enableorno_antes) // Si modifico esta opcion
		{
			$userid= $event['user_row']['user_id'];
			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_rep_enable = '.$enableorno.' WHERE user_id = ' . $userid;
			$this->db->sql_freeresult($this->db->sql_query($sql));
			//echo "Opcion (Des)activada<br />";
		}

		if($count_rep_antes != $count_rep_despues) // Hay que modificar la cantidad de rep
		{
			$userid= $event['user_row']['user_id'];
			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_rep = '.$count_rep_despues.' WHERE user_id = ' . $userid;
			$this->db->sql_freeresult($this->db->sql_query($sql));
			//echo "Cantidad Modificada<br />";
		}
		//echo "<pre>".print_r($event['user_row'], true)."</pre>";
	}

	public function acp_user_can_rep($event)
	{

		$user_id = $event['user_row']['user_id'];
		$GetCommentSQL = 'SELECT rep.*, users.username, users.user_colour, users.user_avatar, users.user_avatar_width, users.user_avatar_height, users.user_avatar_type
		FROM phpbb_reputation rep
		LEFT JOIN '.USERS_TABLE.' users
		ON rep.user_id = users.user_id
		WHERE rep.poster_id = '.$user_id.'
		ORDER BY rep_time DESC';
		$result = $this->db->sql_query($GetCommentSQL);
		$count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation_acp', array(
				'USERNAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'FORUM'			=> $row['forum_id'],
				'TOPIC'			=> $row['topic_id'],
				'POST'			=> $row['post_id'],
				'TIME'			=> $this->user->format_date($row['rep_time']),
				'COMENTARIO'	=> $row['rep_text'],
				'ACTION'		=> $row['rep_action'],
				'POWER'			=> $row['rep_power'],
				'REP_ID'		=> $row['rep_id'],
				'COUNT'			=> $count,
			));
			$count++;
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'USER_REP_ENABLE'   => $event['user_row']['user_rep_enable'],
			'USER_REP'   		=> $event['user_row']['user_rep'],
			'COUNT_COMM'		=> $count,
			)
		);
		//echo "<pre>".print_r($event['submit'], true)."</pre>";
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
		$this->UserRepLastUsed = $event['user_data']['user_rep_last_used'];
		$this->MaxTopList = $event['user_data']['user_rep_toplist_count'];


		$this->template->assign_vars(array(
			'CAN_REP_OTHERS'	=> $this->UserRepEnable,
			'REP_LAST_TIME'		=> $this->UserRepLastUsed,
			'MY_USER_ID'		=> $this->TheUserID,
			'ACTUAL_TIME'		=> time(),
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
		$result = $this->db->sql_query($GetCommentSQL);
		$count = 0;
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
			$count++;
		}

		$this->db->sql_freeresult($result);
		// Obtener toda la data y hacer el pagination

		$template_data = array_merge($template_data, array(
			'MEMBER_USER_REP' 	=> $data['user_rep'], // Obtenemos la cantidad de reputación de la db
			'COUNT_COMM'		=> $count,
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
