# Authentication Server

The SSO service provides an oAuth2 enabled single sign-on point for all of the OpenTHC services.
Account Creation, Sign-In, Profile Settings and Authentication methods are managed through this service.

Corporate or Government implementers would extend this SSO to integrate with their own environment through Middlware or custom Controllers.

## Configuration

* Hostname
* SMTP
* SMS (Carrier & Tokens)
* oAuth2
* U2F / FIDO


## oAuth2

The oAuth2 interface requires all oAuth2 service requestors to have a Client ID.


## API

An API exists to query Company and Contact information as well the the


## Profile

A Company profile provides information about the billing plan too

A Contact profile is a name, email and phone number.


## SMS-2FA

If you want to use SMS for two-factor authentication you will need to configure.


## U2F / FIDO2

The service is enabled for use with U2F such as RSA Secure ID and Solokeys.
