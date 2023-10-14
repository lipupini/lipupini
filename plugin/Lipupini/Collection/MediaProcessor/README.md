# Media Processors

Media processor requests represent routes that the browser requests for a media file

Example:

`/c/file/example/image/large/cat-hat.png`

On a fresh install, this file does not exist in the webroot, nor does the `c` folder, yet.

These media processor request classes can take the route and file extension and determine which processor to use.

They will take the corresponding media file from a collection, process it in the relevant way for the media type, and store the cache file for future static loading.

NOTE: Once the static file is created, the Media Processor class is no longer needed in the request for as long as the cache file exists! So, if you need to debug a media processor, the cache will need to be cleared.
