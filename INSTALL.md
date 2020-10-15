# Installation

Clone this repository and change into that directory.

Then run `composer` to update the PHP dependencies.

Use the etc/apache2.conf as a guide, or edit directly.
If editing directly you may add to Apache by symlink into sites-enabled or by directy Include reference.

Copy the etc/app.ini.example file to etc/app.ini and edit as necessary for your environment.

If you need to create the database, use the files in ./etc/sql/* to create the schema.
If you are tying OpenTHC into an existing environment, the schema could be a guide for creating compatible views / table aliases.

You may need to create the necessary **auth_service** and **auth_context_ticket** entries that are suitable for your environment.

Run ./test/test.sh to verify things are working OK.
