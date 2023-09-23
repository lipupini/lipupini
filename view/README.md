Namespaced view or theme files are stored here.

The webserver's document root ("webroot") is contained within a View Package.

Using this pattern, things like "favicon.ico" and loaded plugins can be efficiently customized within a View Package.

The `c` folder needs to be writeable as it will store cache:

```shell
cd view/Lipupini/webroot
chmod 755 c
```
