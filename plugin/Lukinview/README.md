`Lukinview` is a decoupled frontend plugin for Lupupini that ships by default.

The webserver's document root ("webroot") is contained here. To make a new frontend, copy this plugin and change the webserver's document root.

Using this pattern, things like "favicon.ico" and loaded plugins can be efficiently customized.

The `c` folder in `webroot` needs to be writeable as it will store cache:

```shell
cd plugin/Lukinview/webroot
chmod 755 c
```
