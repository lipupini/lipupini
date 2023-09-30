In the `collection` root folder are subfolders that become the names of collections.

Liputini will attempt to serve any non-hidden files in these directories. If you drop a new video file into a collection folder, Liputini will show it in the collection _if it knows how_.

Within an identifier folder is a `.lipupini` folder. This folder should have all the necessary data to configure an entire account just by dropping it into the filesystem.

Within the `.lipupini` folder is a JSON file `.files.json` containing information about each file and the order in which they should be displayed. If a filename matches an entry, the data from that entry will be used when loading the file.

For example, the JSON might look like this:

```json
{
	"cat-scarf.jpg": {
		"caption": "Scarf Cat"
	},
	"cat-hat.jpg": {
		"caption": "Hat Cat"
	},
	"Memes": {
		"caption": "Memes Folder"
	}
}
```

The root `.lipupini` folder also contains public and private RSA keys for the collection to make signed requests e.g. for ActivityPub.

A helper script is included to initialize a new collection (the collection directory `NameOfCollection` should already exist with a few files in it):

```shell
bin/create-basic-lipupini-folder-in-collection.php NameOfCollection
```

This script will generate RSA keys, generate `.files.json` based on the files in the folder, and remind you to store an avatar PNG at `.lipupini/.avatar.png`

## Vision

Linux user folders symlinked into `collection`, for example:

```shell
bob@domain $ pwd
/home/bob/Lipupini

bob@domain $ ls -A
cat-hat.png  cat-scarf.jpg  dup.mp4  .lipupini  memes  poetry  winamp-intro.mp3

webserver@domain $ su - webserver

webserver@domain $ pwd
/var/www/lipupini

webserver@domain $ sudo ln -s /home/bob/Lipupini /var/www/lipupini/collection/bob@domain.tld

webserver@domain $ ls -A collection/bob@domain.tld
cat-hat.png  cat-scarf.jpg  dup.mp4  .lipupini  memes  poetry  winamp-intro.mp3
```
After symlinking, user `bob` can log in using SFTP and see a `Lipupini` directory in their `/home/bob` directory. The files placed in this directory are then automatically served by Lipupini.

In this way, "Bob" can log in and upload or sync files using many standard methods for which tons of great tutorials are already written. The organization of the files stays the same as it is on Bob's local computer. "Bob" can use WinSCP, FileZilla, drag-n-drop, Windows file manager, MacOS file manager, Linux file manager, various mobile file managers, remote drive mount, a Git repository, rsync, Unison, Syncthing, inotify, etc etc. This method also does not place any limitation on what can be achieved later via the website.
