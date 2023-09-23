Namespaced view or theme files are stored here.

The webserver's document root ("webroot") is contained within a View Package.

Using this pattern, things like "favicon.ico" and loaded plugins can be efficiently customized within a View Package.

The root `collection` folder needs to be symlinked within the `webroot` folder as `c` if it is not already linked:

```shell
cd view/Lipupini/webroot
ln -s ../../../collection c
```
