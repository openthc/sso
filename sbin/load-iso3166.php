#!/usr/bin/php
<?php
/**
 * Load the ISO-3166 Data
 */

require_once(__DIR__ . '/../boot.php');

// $dbc = _dbc();
$cfg = \OpenTHC\Config::get('database/auth');
$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);
$dbc = new \Edoceo\Radix\DB\SQL($dsn, $cfg['username'], $cfg['password']);


$x = file_get_contents('/usr/share/iso-codes/json/iso_3166-1.json');
$x = json_decode($x, true);
$iso3166_1_list = $x['3166-1'];
foreach ($iso3166_1_list as $c) {
	$dbc->insert('iso3166', [
		'id' => $c['alpha_2'],
		'code2' => $c['alpha_2'],
		'code3' => $c['alpha_3'],
		'type' => 'Country',
		'name' => $c['name'],
		'meta' => json_encode($c)
	]);
}

$x = file_get_contents('/usr/share/iso-codes/json/iso_3166-2.json');
$x = json_decode($x, true);
$iso3166_2_list = $x['3166-2'];
foreach ($iso3166_2_list as $x) {
	$code2 = strtok($x['code'], '-');
	$dbc->insert('iso3166', [
		'id' => $x['code'],
		'code2' => $code2,
		'type' => $x['type'],
		'name' => $x['name'],
		'meta' => json_encode($x),
	]);
}
