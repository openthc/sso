<?php
/**
 * A Thin Company Model
 * We have copy of the Prime model here, in SSO to prevent any accidents
 * This one has less features
 */

namespace App;

class Contact
{
	const FLAG_EMAIL_GOOD = 0x00000001;
	const FLAG_PHONE_GOOD = 0x00000002;

	const FLAG_EMAIL_WANT = 0x00000004;
	const FLAG_PHONE_WANT = 0x00000008;

	const FLAG_PRIMARY  = 0x00000100; // Primary Contact
	const FLAG_BILLING  = 0x00000010; // Billing Contact, move to 0x0200
	const FLAG_ROOT     = 0x00000F00; // Right?

	const FLAG_DISABLED = 0x01000000; // @called LOCK too?
	const FLAG_DEAD     = 0x08000000;
	// const FLAG_DELETED  = 0x08000000; // @deprecated

	private function __construct($dbc) { /* no */ }
}
