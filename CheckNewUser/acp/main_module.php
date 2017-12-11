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
 * Check New User ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $db;

		$user->add_lang_ext('TheMasterNico/CheckNewUser', 'common');
		$this->tpl_name = 'acp_check_user_body';
		$this->page_title = $user->lang('ACP_CHECK_USER');
		add_form_key('TheMasterNico/CheckNewUser');

		if ($request->is_set_post('submit'))//se envio los datos
		{
			if (!check_form_key('TheMasterNico/CheckNewUser'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			echo "<pre>".print_r($request, true)."</pre>";
			//$config->set('acme_demo_goodbye', $request->variable('acme_demo_goodbye', 0));

			trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}
		// A partir de acá haré las cosas para mostrar a los usuarios
		$lista_tabla = "";
		$id_in_table = 0;
		$sql = 'SELECT username, user_email, user_regdate, user_ip, user_id, user_actkey
		FROM phpbb_users
		WHERE user_type = 1';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$resultado = $db->sql_query('SELECT username FROM phpbb_users WHERE user_ip = "'.$row['user_ip'].'"');

			$tooltop = "";
			$MismaIP = 0;
			while($SameIP = $db->sql_fetchrow($resultado))
			{
				$tooltop .= $SameIP["username"]."&#010;";
				$MismaIP ++;
			}
			($color == 255) ? ($color = 201) : ($color = 255);
			$maild = substr($row['user_email'], strrpos($row['user_email'], "@", -1), strlen($row['user_email']));

			$lista_tabla .=
			"<tr id='".$row['user_id']."' style='color:black; background:rgb(".$color.", ".$color.", ".$color.")'>
			<td>". $row['username'] ."</td>
			<td>". $row['user_email'] ."</td>
			<td>". $user->format_date($row['user_regdate']) ."</td>
			<td title='".$tooltop."'>".$MismaIP ."</td>
			<td id='Act-".$row['user_id']."'>
				<input type='checkbox' name='Activar[".$row['user_id']."]'		id='ActivarID' 	value='".$row['user_id']."' 	id_user='".$row['user_id']."' actkey='".$row['user_actkey']."'>
			</td>
			<td id='ExpN-".$row['user_id']."'>
				<input type='checkbox' name='ExpNombre[".$row['user_id']."]' 	id='Nombre' 	value='".$row['username']."' 	id_user='".$row['user_id']."' actkey='".$row['user_actkey']."'>
			</td>
			<td id='ExpM-".$row['user_id']."'>
				<input type='checkbox' name='ExpMail[".$row['user_id']."]' 		id='Correo' 	value='*".$maild."' 				id_user='".$row['user_id']."' actkey='".$row['user_actkey']."'>
			</td>
			<td id='ExpIP-".$row['user_id']."'>
				<input type='checkbox' name='ExpIP[".$row['user_id']."]' 		id='IP' 		value='".$row['user_ip']."' 	id_user='".$row['user_id']."' actkey='".$row['user_actkey']."'>
			</td>
			</tr>";
			$id_in_table++;
		}
		$db->sql_freeresult($resultado);
		$db->sql_freeresult($result);

		$template->assign_vars(array( // Aquí estarán los datos obtenidos. Las keys
			'U_ACTION'				=> $this->u_action,
			'LISTA_USUARIOS_PENDIENTES' => $lista_tabla,
		));
	}
}
