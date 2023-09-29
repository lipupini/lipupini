On Linux machines, it is intended that the `rclip` binary v1.7.3 go in this folder.

To do that, `cd` to this folder and run the following commands:

```shell
wget https://github.com/yurijmikhalevich/rclip/releases/download/v1.7.3/rclip-v1.7.3-x86_64.AppImage
chmod u+x rclip-v1.7.3-x86_64.AppImage
```

On Windows and MacOS, you will need to follow the [rclip installation instructions](https://github.com/yurijmikhalevich/rclip#installation) then update the `rclipPath` in `bin/rclip-api.php`:

```php
$rclipSearch = new RclipSearch(
	collectionFolderName: $collectionFolderName,
	rclipPath: DIR_ROOT . '/package/rclip/rclip-v1.7.3-x86_64.AppImage'
);
```

The API is only tested on Linux, and when using Windows you might have to make an update to `plugin/Lipupini/Collection/RclipSearch.php` related to this: https://github.com/yurijmikhalevich/rclip/issues/36
