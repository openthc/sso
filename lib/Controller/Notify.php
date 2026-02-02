<?php
/**
 * Notify Controller
 * Show the user a notification, and remember where they were supposed to go.
 *
 * SPDX-License-Identifier: MIT
 */

namespace OpenTHC\SSO\Controller;

class Notify extends \OpenTHC\SSO\Controller\Base
{
	static $url = '/notify';

	/**
	 * Build the notification URL while remembering where they are supposed to go
	 * @arg $ret The URL the user is supposed to navigate to after the notification is shown
	 * @arg $notify_id Optional Notification ID found in etc/notify/$id.yaml
	 */
	static function make_url(string $ret, $notify_id = null)
	{
		if (empty($notify_id)) {
			return self::$url . '?' . http_build_query(['r' => $ret]);
		}
		return self::$url . '/' . $notify_id . '?' . http_build_query(['r' => $ret]);
	}

	/**
	 * HTTP GET method
	 * @arg id optional Notification ID found in etc/notify/$id.yaml
	 */
	function __invoke($REQ, $RES, $ARG)
	{
		$next_url = $_GET['r'];
		if (empty($next_url)) {
			$next_url = '/profile';
		}

		// When a specific notification is not requested, use the newest record.
		$notify_id = h($ARG['notify_id']);
		$yaml_file = sprintf('%s/etc/notify/%s.yaml', APP_ROOT, $notify_id);
		if (empty($notify_id)) {
			$file_list = glob(sprintf('%s/etc/notify/*.yaml', APP_ROOT));
			if (empty($file_list)) {
				return $this->redirect($next_url);
			}

			// This scheme shows them the latest/newest/last yaml file. If older ones exist, we ignore them
			$yaml_file = array_pop($file_list);
			preg_match('/\/(\w+)\.yaml$/', $yaml_file, $m);
			$notify_id = $m[1];
		}

		$contact_id = $_SESSION['Contact']['id'];
		$company_id = $_SESSION['Company']['id'];
		$redis = $this->_container->RDB;
		$key = sprintf('notify-%s-company-%s-contact-%s', $notify_id, $company_id, $contact_id);
		if ($redis->exists($key)) {
			return $this->redirect($next_url);
		}

		$yaml = file_get_contents($yaml_file);
		$yaml = \yaml_parse($yaml);

		$data = $this->data;
		$data['id'] = $notify_id;
		$data['Page']['title'] = $yaml['title'];
		$data['head'] = $yaml['head'];
		$data['body'] = $yaml['body'];
		$data['next_url'] = $next_url;

		return $RES->write( $this->render('notify.php', $data) );
	}

	/**
	 * HTTP POST method
	 */
	function post($REQ, $RES, $ARG)
	{
		$contact_id = $_SESSION['Contact']['id'];
		$company_id = $_SESSION['Company']['id'];
		$notify_id = $_POST['notify_id'];
		$redis = $this->_container->RDB;
		$key = sprintf('%s-company-%s-contact-%s', $notify_id, $company_id, $contact_id);
		$redis->set($key, true);

		return $this->redirect($_POST['next_url']);

	}
}
