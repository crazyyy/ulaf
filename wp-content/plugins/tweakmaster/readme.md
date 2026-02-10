If you just want just use the plugin its on [wordpress.org](https://wordpress.org/tweakmaster).

# Contributing is very easy

1. For a basic standalone tweak all you need to do it develop a (mu)plugin as a single file that automatically enabled/disables or otherwise tweaks something with filters or actions.
1. When you have your tiny tweak plugin working simply place the file into `tweakmaster/php/tweaks-standalone/` look at the naming of the files and name your tweak straight to the point. Look at the other files for coding standards. Use Namespace, filters/actions at the top of the file, avoid anonymous functions.
1. Then check out how settings are defined in `tweakmaster/php/fn-settings.php`. Add a setting for your tweak, the array key should be like your filename but without the `.php` ending. Most of the boolean settings simple case a tweak file to be loaded. Think of it like activating plugins.

# Advanced usage of standalone tweaks as mu-plugins

If you do it this way, you probably should not use the Plugin at the same time. It won't work for tweaks that have settings that are not just on/off switches. Each standalone tweak can be used as mu-plugin. Simple put the files you need inside your mu-plugins folder.

To do this you can simply copy `.php` files you like from [tweaks-standalone/](https://github.com/nextgenthemes/tweakmaster/tree/master/php/tweaks-standalone) to your mu-plugins folder. Github has a 'download file' icon on the top right when you open files.

If you have WP CLI you can use these command to copy the files directly from Github to your WordPress site.

```sh
# In case you do not have a mu-plugins folder.
wp eval "if ( ! file_exists(WP_CONTENT_DIR . '/mu-plugins' ) ) { mkdir(WP_CONTENT_DIR . '/mu-plugins', 0755, true); }"

# Replace the filename with any file you find in https://github.com/nextgenthemes/tweakmaster/tree/master/php/tweaks-standalone/
wp eval "\$filename = 'disable-auto-trash-emptying.php'; file_put_contents( WP_CONTENT_DIR . '/mu-plugins/' . \$filename, file_get_contents( 'https://raw.githubusercontent.com/nextgenthemes/tweakmaster/refs/heads/master/php/tweaks-standalone/' . \$filename ) );"
```
