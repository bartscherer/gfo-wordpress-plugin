# gfo-wordpress-plugin
This is the WordPress plugin for the [Google Fonts On-premise project](https://github.com/bartscherer/gfo). To install the plugin, just move it into the `wp-content/plugins` directory of your WordPress instance and enable it. Have a look at [this tutorial](https://www.wonderplugin.com/wordpress-tutorials/how-to-manually-install-a-wordpress-plugin-via-ftp/) if you are not sure how to accomplish that.

## Settings

This plugin integrates itself into the `Settings` submenu of your WordPress Admin panel. Its name is `Google Fonts On-Premise`. Open the menu and you should see two options. The first one is `Enable GFO` which tells WordPress to use your `GFO instance URL` (*that you can configure below the `Enable GFO` checkbox*) as the provider for Google Fonts.

## How it works

The plugin replaces every occurence of `fonts.googleapis.com` with `[your gfo domain]`. It also rewrites the legacy endpoint `https://fonts.googleapis.com/icon` to `...[your gfo domain]/css` in order to prevent issues with the old Material Icons. It is intelligent enough to ignore http[s]:// and trailing slashes in your configured GFO domain. It will only work if the GFO instance is accessible via HTTPS.

## Disclaimer

The plugin was only tested within an up-to-date and some older dockerized WordPress instances but should be compatible with yours too.