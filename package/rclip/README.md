On Linux machines, this folder is intended to hold `rclip` binary v1.7.3.

To add it, `cd` to this `rclip` folder and run the following commands:

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

The AI search is kind of a "hidden feature" at the moment because:

1. Setup may a bit complicated or excessively error-prone to include in a quick start.
2. Searching can take a while and use resources, so it probably does not make sense to have a button to allow arbitrary public searches.
3. The feature is mainly included as POC for some way to incorporate keyword search results from image recognition.
4. The feasibility of actually including it as a frontend search box seems rather slim. However, `rclip` creates a SQLite database where it caches the vectors after building the index. Testing may show that exposing the search is feasible when the index is not being rebuilt, which is the default behavior of the API when searching. Perhaps it can be limited to logged-in accounts at some point, or incorporate a faster alternative or hybrid search mechanism.
5. The saved search concept can have other uses and in its current state can be revisited.

## Using AI search

From the project root directory, see usage examples by typing:

```shell
bin/rclip-api.php
```

Build the search index for the `example` collection:

```shell
bin/rclip-api.php example
```

Search for cat pictures in the `example` collection and get the top 10 results:

```shell
bin/rclip-api.php example 'Cat' 10
```

The search will take a few moments. When it finishes, you are prompted `Save search for @example? [Y/n]`

If you choose to save it, `collections/example/.liputini/.savedSearches.json` will be created.

After that, you should be able to view the search results at:

http://localhost/@example?search=Cat

You can edit the `.savedSearches.json` file to rename or reorganize saved searches.

If your console (e.g. Konsole) supports iTerm2 Inline Images Protocol, you can try showing images previews in the CLI search without saving to a collection:

```shell
bin/rclip-api.php example 'Cat' 10 preview
```

On the demo site you can see examples of curated `rclip` search results:

https://lipupini-demo.dup.bz/@example?search=Cat

https://lipupini-demo.dup.bz/@example?search=Pink
