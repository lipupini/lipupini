This is the `v2.x` branch of [Lipupini](https://github.com/instalution/lipupini), a Work in Progress

TODO:

- Validate HTTP signatures, this could help improve outgoing signature flow too
- i18n
- Removing a folder "Type" (without file extension) from `.files.json` results in unexpected behavior in the rendering
- All plugin output gets added to a buffer. This way any headers can be modified before output.
    - Currently in the `shutdown()` method of `Lipupini.php` the timing and `X-Powered-By` header is commented out, but it should be possible to send those before output.
- New window from frontend, might not need to use the `Parsedown.php` extension
- Optionally specify separate URL in config for cache files (`c` folder)
- Look into X-Frame-Options "SAMEORIGIN" header
- Check out Laravel middleware for additional security ideas
