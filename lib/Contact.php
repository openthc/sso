<?php
/**
	A Contact Model
	@deprecated use Auth_Contact
*/

namespace App;

use Edoceo\Radix\DB\SQL;

class Contact //  extends \OpenTHC\Contact // \OpenTHC\SQL\Record
{
	const FLAG_MAILGOOD = 0x00000001;

	const FLAG_EMAIL_GOOD = 0x00000001;
	const FLAG_PHONE_GOOD = 0x00000002;
	const FLAG_EMAIL_WANT = 0x00000004;
	const FLAG_PHONE_WANT = 0x00000008;

	const FLAG_PRIMARY  = 0x00000100; // Primary Contact
	const FLAG_BILLING  = 0x00000010; // Billing Contact, move to 0x0200

	const FLAG_DISABLED = 0x01000000;
	const FLAG_DEAD     = 0x08000000;
	// const FLAG_DELETED  = 0x08000000; // @deprecated

	protected $_dbc;

	protected $_table = 'auth_contact';

	function __construct($dbc)
	{
		$this->_dbc = $dbc;
	}

	function findBy($a)
	{
		$sql_select = sprintf('SELECT * FROM %s WHERE ', $this->_table);

		$sql_filter = [];
		foreach ($a as $c => $v) {
			$k = sprintf(':%08x', crc32($v));
			$sql_filter[$k] = sprintf('%s = %s', $c, $k);
			$sql_params[$k] = $v;
		}


		$sql = $sql_select . implode(' AND ', $sql_filter);
		// var_dump($sql);
		// var_dump($sql_params);
		$rec = $this->_dbc->fetchRow($sql, $sql_params);
		// var_dump($rec);

		// exit;

	}

	function findByUsername($x)
	{
		$x = trim(strtolower($x));
		$x = iconv('UTF-8', 'US-ASCII//IGNORE', $x);
		$res = $this->_dbc->fetchRow('SELECT * FROM auth_contact WHERE username = ?', array($x));
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
