Caching
=======

This `/cache` directory is for ActivityPub requests, an `./ap` subfolder should be created automatically if directory permissions will allow.

*Here is the problem:*

Static media file caching is created by default in the `webroot`: `/module/Lukenview/webroot/c`

ActivityPub cache will not go in the webroot, and media file static cache can at least since it makes it easier for most webservers to load static files.

Any time a media file is modified, if there is processing involved with caching the file then the associated cache file (e.g. as creating a thumbnail image) will need to be deleted so that it can be regenerated. Where possible to save space, symlinks are created to original unprocessed files. The symlinks may be a challenge with some hosting setups.


From a DevOps perspective there are a lot of paths that this platform can go in.

The recommended setup is currently with either `apache2` configured to serve static files first, or PHP's built-in webserver.

In the case of `apache2`, you have an `.htaccess` file already in the `webroot`.
