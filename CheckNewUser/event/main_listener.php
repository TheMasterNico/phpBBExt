<?php
/**
 *
 * Check New User. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Nicolas Castillo, pawnscript.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace TheMasterNico\CheckNewUser\event;


/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Check New User Event listener.
 */
class main_listener implements EventSubscriberInterface
{

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver    $db         Database object
	* @param \phpbb\controller\helper   $helper     Controller helper object
	* @return \TheMasterNico\CheckNewUser\event\main_listener
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request $request)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.ucp_activate_after'				=> 	'UpdateLatestUser',
			'core.ucp_register_requests_after'		=>	'CheckMultipleIPBefRegister',
		);
	}

	public function UpdateLatestUser($event)
	{
		$row = $event['user_row'];
		set_config('newest_user_id', $row['user_id'], true);
		set_config('newest_username', $row['username'], true);
		set_config('newest_user_colour', $row['user_colour'], true);
	}

	public function CheckMultipleIPBefRegister($event)
	{
		$agreed = $event['agreed'];
		/*$change_lang = $event['change_lang'];
		$coppa = $event['coppa'];
		$submit = $event['submit'];
		$user_lang = $event['user_lang'];
		echo "<br /><br /><br /><br /><pre>$agreed</pre>";
		echo "<pre>$change_lang</pre>";
		echo "<pre>$coppa</pre>";
		echo "<pre>$submit</pre>";
		echo "<pre>$user_lang</pre>";*/

		if($agreed) // Acepto las reglas
		{
			$ipaddress = '';
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ipaddress = $this->request->variable('HTTP_X_FORWARDED_FOR', '', true, \phpbb\request\request_interface::SERVER);
				//echo "Tu IP es HTTP_X_FORWARDED_FOR : $ipaddress";
			} else {
				$ipaddress = $this->request->variable('REMOTE_ADDR', '', true, \phpbb\request\request_interface::SERVER);
				//echo "Tu IP es: $ipaddress";
			}
			$sql = 'SELECT COUNT(username) as misma_ip
			FROM ' . USERS_TABLE . '
			WHERE user_ip = "'.$ipaddress.'"';
			//echo $sql;
			$resultado = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($resultado);
			$this->db->sql_freeresult($resultado);
			if($row["misma_ip"] >= 1)
			{
				//redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
				//meta_refresh(20, append_sid("{$phpbb_root_path}index.$phpEx"));
				$message = 'Tienes '.$row["misma_ip"].' cuenta asociada a tu dirección IP ('.$ipaddress.').
				<br/>No puedes registrar mas cuentas.<br><br>
				Si tienes problemas contacta por un <a href="mailto:admin@pawnoscript.com">correo</a> o por
				<a href="https://www.facebook.com/PawnoScripting/">facebook</a>.';
				//echo $message;
				//throw new \phpbb\exception\runtime_exception($message);
				//return $this->helper->error($message);
				trigger_error($message);
			}
		}
	}
}
