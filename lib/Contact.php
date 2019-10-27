<?php
/**
	A Contact Model
	@deprecated use Auth_Contact
*/

namespace App;

use Edoceo\Radix\DB\SQL;

class Contact extends \OpenTHC\Contact // \OpenTHC\SQL\Record
{
	const FLAG_MAILGOOD = 0x00000001;

	const FLAG_BILLING  = 0x00000010; // Billing Contact

	const FLAG_DISABLED = 0x01000000;
	const FLAG_DELETED  = 0x08000000;

	protected static $_dbc;

	protected $_table = 'auth_contact';

	static function setDB($dbc)
	{
		self::$_dbc = $dbc;
	}

	static function findByUsername($x)
	{
		$x = strtolower($x);
		$res = static::$_dbc->fetchRow('SELECT * FROM auth_contact WHERE username = ?', array($x));
		if (!empty($res)) {
			return new self($res);
		}

		return false;
	}

	/**
		Get or Set User Options
	*/
	function opt($key, $val=null)
	{
		$uid = intval($this->_data['id']);

		// Setter
		if (null != $val) {

			//Cache::del(sprintf('user-%d-%s', $uid, $key));

			static::$_dbc->query('BEGIN');

			$sql = 'SELECT id FROM auth_contact_option WHERE uid = ? AND key = ? FOR UPDATE';
			$arg = array($uid, $key);
			$chk = static::$_dbc->fetch_one($sql, $arg);

			if (empty($chk)) {
				$sql = 'INSERT INTO auth_contact_option (uid, key, val) VALUES (?, ?, ?)';
				$arg = array($uid, $key, $val);
			} else {
				$sql = 'UPDATE auth_contact_option SET val = ? WHERE id = ?';
				$arg = array($val, $chk);
			}

			static::$_dbc->query($sql, $arg);
			static::$_dbc->query('COMMIT');

			$ret = $val;

		} else {
			// Getter
			//$ret = Cache::get(sprintf('user-%d-%s', $uid, $key));
			if (empty($ret)) {
				$sql = 'SELECT val FROM auth_contact_option WHERE uid = ? AND key = ?';
				$arg = array($uid, $key);
				$ret = static::$_dbc->fetch_one($sql, $arg);
			}
		}

		return $ret;
	}

	/**
		Reset the Password on the Model
	*/
	function resetPassword()
	{
		$pass = md5(openssl_random_pseudo_bytes(512));
		$hash = password_hash($pass, PASSWORD_DEFAULT);

		$sql = 'UPDATE auth_contact SET password = ? WHERE id = ?';
		$arg = array($hash, $this->_data['id']);
		$res = static::$_dbc->query($sql, $arg);

		$this->_data['password'] = null;

		return $res;
	}

}
