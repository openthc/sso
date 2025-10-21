#!/usr/bin/env php
<?php
/**
 * OpenTHC Docker SSO Service Init
 */

_init_config();

// Bootstrap OpenTHC Service
$d = dirname(__DIR__);
require_once("$d/boot.php");
// require_once("$d/vendor/openthc/common/lib/docker.php");

// Wait for Database
$dsn = getenv('OPENTHC_DSN_MAIN');
$dbc_main = _spin_wait_for_sql($dsn);
echo "SQL Connection: MAIN\n";

$dsn = getenv('OPENTHC_DSN_AUTH');
$dbc_auth = _spin_wait_for_sql($dsn);
echo "SQL Connection: AUTH\n";

_upsert_this_service($dbc_main, $dbc_auth);

_upsert_demo_contact($dbc_main, $dbc_auth);
_upsert_demo_company($dbc_main, $dbc_auth);

exit(0);

/**
 * Create Service Config File
 */
function _init_config()
{
	$cfg = [];

	$cfg['database'] = [
		'auth' => [
			'dsn' => getenv('OPENTHC_DSN_AUTH'),
			'hostname' => 'sql',
			'username' => 'openthc_auth',
			'password' => 'openthc_auth',
			'database' => 'openthc_auth',
		],
		'main' => [
			'dsn' => getenv('OPENTHC_DSN_MAIN'),
			'hostname' => 'sql',
			'username' => 'openthc_main',
			'password' => 'openthc_main',
			'database' => 'openthc_main',
		],
	];

	// Redis
	$cfg['redis'] = [
		'hostname' => 'rdb',
	];

	// OpenTHC Services
	$cfg['openthc'] = [
		'app' => [
			// 'id' => '',
			'origin' => getenv('OPENTHC_APP_ORIGIN'),
			'public' => getenv('OPENTHC_APP_PUBLIC'),
		],
		'b2b' => [
			'origin' => getenv('OPENTHC_B2B_ORIGIN'),
			'public' => getenv('OPENTHC_B2B_PUBLIC'),
		],
		'cre' => [
			'origin' => getenv('OPENTHC_CRE_ORIGIN'),
			'public' => getenv('OPENTHC_CRE_PUBLIC'),
		],
		'dir' => [
			'id' => getenv('OPENTHC_DIR_ID'),
			'origin' => getenv('OPENTHC_DIR_ORIGIN'),
			'public' => getenv('OPENTHC_DIR_PUBLIC'),
		],
		'pos' => [
			// 'id' => '',
			'origin' => getenv('OPENTHC_POS_ORIGIN'),
			'public' => getenv('OPENTHC_POS_PUBLIC'),
		],
		'pub' => [
			'origin' => getenv('OPENTHC_PUB_ORIGIN'),
			'public' => getenv('OPENTHC_PUB_PUBLIC'),
		],
		'sso' => [
			'id' => getenv('OPENTHC_SSO_ID'),
			'origin' => getenv('OPENTHC_SSO_ORIGIN'),
			'public' => getenv('OPENTHC_SSO_PUBLIC'),
			'secret' => getenv('OPENTHC_SSO_SECRET'),
			// 'client-id' => '010DEM0XXX0000SVC000000SS0',
			// 'client-sk' => 'UJWRp58-YYy4nUEFfjYdpKlikgMUHNu6GOZ5lh0jK0k',
			// 'client-pk' => 'JEWoMtX-FpXaTI4MJLNoV7CWWWH5zdFeXETZiHpmhCo',
			// 'test' => [
			// 	'sk' => 'M178xe6J4dPxvdxhWXJLfC8HQsBqEL_QioQT4F3L5GI',
			// ]
		]
	];

	$cfg_data = var_export($cfg, true);
	$cfg_text = sprintf("<?php\n// Generated File\n\nreturn %s;\n", $cfg_data);
	$cfg_file = sprintf('%s/etc/config.php', dirname(__DIR__));

	file_put_contents($cfg_file, $cfg_text);

}


/**
 *
 */
function _upsert_demo_company($dbc_main, $dbc_auth)
{
	$arg = [];
	$arg[':c1'] = getenv('OPENTHC_DEMO_COMPANY_ID');
	$arg[':n1'] = getenv('OPENTHC_DEMO_COMPANY_NAME');
	$sql = <<<SQL
	INSERT INTO public.auth_company (id, name, cre)
	VALUES (:c1, :n1, 'openthc')
	ON CONFLICT (id) DO UPDATE SET
		name = EXCLUDED.name
	SQL;

	$dbc_auth->query($sql, $arg);
}

/**
 *
 */
function _upsert_demo_contact($dbc_main, $dbc_auth)
{
	// Upsert Main Database
	$arg = [];
	$arg[':c1'] = getenv('OPENTHC_DEMO_CONTACT_ID');
	$arg[':u1'] = getenv('OPENTHC_DEMO_CONTACT_USERNAME');
	$sql = <<<SQL
	INSERT INTO contact (id, name)
	VALUES (:c1, :u1)
	ON CONFLICT (id) DO UPDATE SET
		name = EXCLUDED.name
	SQL;

	$dbc_main->query($sql, $arg);


	// Upsert Auth Database
	$arg = [];
	$arg[':c1'] = getenv('OPENTHC_DEMO_CONTACT_ID');
	$arg[':u1'] = getenv('OPENTHC_DEMO_CONTACT_USERNAME');
	$arg[':p1'] = password_hash('passweed', PASSWORD_DEFAULT);

	$p = getenv('OPENTHC_DEMO_CONTACT_PASSWORD');
	if ( ! empty($p)) {
		if (preg_match('/^crypt:\/\/(.+)$/', $p, $m)) {
			$arg[':p1'] = $p;
		} elseif (preg_match('/^plain:\/\/(.+)$/', $p, $m)) {
			$arg[':p1'] = password_hash($m[1], PASSWORD_DEFAULT);
		} else {
			$arg[':p1'] = password_hash($p, PASSWORD_DEFAULT);
		}
	}

	$sql = <<<SQL
	INSERT INTO public.auth_contact (id, username, password, stat, flag)
	VALUES (:c1, :u1, :p1, 200, 3)
	ON CONFLICT (id) DO UPDATE SET
		username = EXCLUDED.username
		, password = EXCLUDED.password
		, stat = EXCLUDED.stat
		, flag = EXCLUDED.flag
	SQL;

	$dbc_auth->query($sql, $arg);

}


/**
 *
 */
function _upsert_this_service($dbc_main, $dbc_auth)
{
	$arg = [];
	$arg[':s1'] = getenv('OPENTHC_SSO_ID');
	$arg[':c1'] = getenv('OPENTHC_ROOT_COMPANY_ID');
	$arg[':pk1'] = getenv('OPENTHC_SSO_PUBLIC');
	$arg[':sk1'] = getenv('OPENTHC_SSO_SECRET');

	$sql = <<<SQL
	INSERT INTO public.auth_service (id, company_id, created_at, stat, flag, name, code, hash, context_list)
	VALUES (:s1, :c1, '2014-04-20', 0, 0, 'OpenTHC/Demo/SSO', :pk1, :sk1, 'profile')
	ON CONFLICT (id) DO UPDATE SET
		company_id = EXCLUDED.company_id
		, code = EXCLUDED.code
		, hash = EXCLUDED.hash
	SQL;
	$dbc_auth->query($sql, $arg);

	// My Service
	// $sql = <<<SQL
	// INSERT INTO auth_service (id, company_id, name, code, hash, context_list)
	// VALUES (:s0, :company_id, 'OpenTHC SSO Services', :pk, :sk, 'contact company license profile')
	// ON CONFLICT DO NOTHING
	// SQL;

	// $cfg = $openthc_global_config['service']['sso'];
	// $dbc_auth->query($sql, [
	// 	':s0' => $cfg['id'],
	// 	':company_id' => $openthc_global_config['root']['company'],
	// 	':pk' => $cfg['public-key'],
	// 	':sk' => $cfg['secret-key'],
	// ]);

}


/**
 * Waits about 1 minute for the sql server to start
 */
function _spin_wait_for_sql(string $dsn)
{

	$try = 0;

	do {

		$try++;

		try {
			// echo "SQL Connection: Checking\n";

			$ret = new \Edoceo\Radix\DB\SQL($dsn);

			return $ret;

		} catch (Exception $e) {
			// Ignore
			// echo "SQL Connection: ";
			// echo $e->getMessage();
			// echo "\n";
			// var_dump($e);
		}

		sleep(4);

	} while ($try < 16);

	throw new \Exception('Failed to connect to database');

	exit(1);
}
