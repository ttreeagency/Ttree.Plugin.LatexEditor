TYPO3 Neos LaTeX Authoring Support
==================================

This plugin provides an editor to edit LaTeX document in Neos, with conversion to HTML. The current conversion toolkit 
used is `ht4latex`, you need to have a work LaTeX distribution on your server to use the plugin.

Note: currently this plugin use `sudo` to execute `ht4latex`. Without that the command line tools failed for a unknown 
reason, certainly related to `www-data` environement variables or shell. If you have a solution for that, 
your are welcome, just contact me or open an issue.

Note: this package is still experimental and may change heavily in the near future.

Configure sudo
--------------

Note: don't do that on your production server, it's a huge security problem.

Edit the file `/etc/sudoers` and add the following line:

```
%www-data	ALL=(ALL) NOPASSWD: ALL
```

On Mac OS, replace `www-data` by `_www`. This plugin is not tested on Windows.

A docker container to host the LaTeX toolchain
----------------------------------------------

The full LaTeX toolchain can be really big, and as a system administrator I don't like to install all those stuff on a
production system. I will work on Docker container to host the full LaTeX toolchain. The container can be executed only 
for conversion process. Let's try ... but I see a nice use case for Docker here.