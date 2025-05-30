<?php
/**
 *
 */

namespace OpenTHC\SSO\UI;

class Icon
{
	static $icon_list = [
		'arrow-right' => '<i class="fa-solid fa-arrow-right"></i>',
		'checkbox' => '<i class="fa-regular fa-square-check"></i>',
		'link-out' => '<i class="fa-solid fa-arrow-up-right-from-square"></i>',
		'next' => '<i class="fa-solid fa-arrow-right"></i>',
		'save' => '<i class="fa-regular fa-floppy-disk"></i>',
	];

	/**
	 *
	 */
	static function icon($icon)
	{
		$icon = self::$icon_list[$icon];
		return $icon;
	}

}
