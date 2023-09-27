This is a "Lipupini" namespace for various Lipupini plugins.

They can be extended, overridden, or swapped. See `plugin/README.md`

The webserver's document root ("webroot") is contained within a Plugin.

Using this pattern, things like "favicon.ico" and loaded plugins can be efficiently customized.

The `c` folder in `webroot` needs to be writeable as it will store cache:

```shell
cd plugin/Lipupini/webroot
chmod 755 c
