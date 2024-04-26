CLI Scripts
===========

`generate-keys.php` - Specify a collection name to generate RSA keys for signed HTTP requests, required by ActivityPub.

`process-media.php` - This is a comprehensive way to generate cache files, and clean up the cache folders for collections. You can specify a collection name, e.g. `bin/process-media.php example`, or leave the argument blank to process all collections. All on-demand media processor requests can be deleted from the request queue and this script can be used instead for generating static cache in the background.

`test-helper.php` - The E2E test suite is able to "talk" to the PHP app using this script as a bridge.
