In the `collection` root folder are folders named with an ActivityPub identifier.

The identifier folders contain files of various mime types or other organizational folders.

Within an identifier folder is a `.lipupini` folder.

Each file can have a corresponding `.json` file in the `.lipupini` folder with additional metadata such as `caption`.

A file will not be served if there is no corresponding `.json` file, though the `.json` file can be blank.

See `plugin/Lipupini/WebFinger/README.md` for information about the `.webfinger.json` file.

## Vision

Linux user folders symlinked into `collection`, for example:

```shell
bob@domain $ pwd
/home/bob/Lipupini

bob@domain $ ls
cat-computer.jpg  cat-scarf.jpg  toki-ipsum.md
cat-hat.png       dup.mp4        winamp-intro.mp3

webserver@domain $ su - webserver

webserver@domain $ pwd
/var/www/lipupini

webserver@domain $ sudo ln -s /home/bob/Lipupini /var/www/lipupini/collections/bob@domain.tld

webserver@domain $ ls collections/bob@domain.tld
cat-computer.jpg  cat-scarf.jpg  toki-ipsum.md
cat-hat.png       dup.mp4        winamp-intro.mp3
```
After symlinking, user `bob` can log in using SFTP and see a `Lipupini` directory in their `/home/bob` directory. The files placed in this directory are then automatically served by Lipupini.

In this way, "Bob" can log in and upload or sync files using many standard methods for which tons of great tutorials are already written. The organization of the files stays the same as it is on Bob's local computer. "Bob" can use WinSCP, FileZilla, drag-n-drop, Windows file manager, MacOS file manager, Linux file manager, various mobile file managers, remote drive mount, a Git repository, rsync, Unison, Syncthing, inotify, etc etc. This method also does not place any limitation on what can be achieved later via the website.
