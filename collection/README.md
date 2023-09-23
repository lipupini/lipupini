In the `collection` root folder are folders named with an ActivityPub identifier

The identifier folders contain files of various mime types or other organizational folders

Within an identifier folder is a `.lipupini` folder

Each file can have a corresponding `.json` file in the `.lipupini` folder with additional metadata such as `caption`

A file will not be served if there is no corresponding `.json` file, though the `.json` file can be blank.

See `plugin/Lipupini/WebFinger/README.md` for information about the `.webfinger.json` file.
