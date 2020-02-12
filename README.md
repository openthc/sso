# Authentication Server

The SSO service provides an oAuth2 enabled single sign-on point for all of the OpenTHC services.
Account Creation, Sign-In, Profile Settings and Authentication methods are managed through this service.

Corporate or Government implementers would extend this SSO to integrate with their own environment through Middlware or custom Controllers.


## Configuration

* Database
* Communications Service
* oAuth2
* U2F / FIDO


## Database

SSO expects to connect to a database following the OpenTHC data models as described in the API.
An example schema is provided in `etc/sql/`


## Communications

The SSO system doesn't support sending emails or text messages directly.
An external service must be provided to respond to some simple POST messages, similar to web-hooks


## oAuth2

The oAuth2 interface requires all oAuth2 service requestors to have a Service Client ID.


## API

An API exists to query Company and Contact information as well the directory of Licenses. See the [OpenTHC API Documentation](https://api.openthc.org/doc/#_authentication) for more information.


## SMS-2FA

If you want to use SMS for two-factor authentication you will need to configure the necessary webhooks.


## U2F

The service is enabled for use with U2F such as RSA Secure ID and Solokeys.
