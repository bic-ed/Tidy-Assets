# Tidy Assets
##  A plugin to rearrange [Zenphoto](https://www.zenphoto.org/) resources

### What it does
Tidy Assets shifts all Zenphoto JavaScript elements, including inline scripts and optionally CSS resources, to the bottom of the _body_ element. This could improve the user experience on their first visit to your site, by delaying render-blocking resources.

It also offers the option to replace the outdated version of jQuery included in Zenphoto with the latest upgrade available at the time of this plugin release date. The latter feature, however, is not suitable for every existing Zenphoto theme or plugin, as some of them still use functions that have been dropped in recent updates of jQuery. This is also the reason why Zenphoto doesn’t provide a newer version of the library yet.

Both of the above features could have a positive SEO impact, since the loading speed of a page is one of the parameters involved in determining its ranking. Moreover, the upgraded version of jQuery has a smaller size also, as some support for very old browsers has been removed.

Attributes other than _rel=“stylesheet”_ of moved elements are not reflected in the output. In most cases, these are attributes that are not actually needed, such as _type=“text/javascript”_ and _type=“text/css”_, unnecessary in HTML5, or _async_ and _defer_, which lose their importance after the resources have been moved to the bottom of the _body_ element.

In addition to the CSS and JavaScript elements, the remaining HTML is also cleaned up slightly, by removing blanks from the edges of lines and replacing multiple instances of white spaces or tabs with a single white space. Empty class attributes of HTML elements are dropped too.

### How it works
Tidy Assets collects the most part of the Zenphoto PHP output, starting from the first line printed by the `theme_head` registered filters and stopping at the position where the `theme_body_close` register is placed in the theme scripts, which is just before the closing body tag. To do so, the filters of this plugin are registered with a priority of 99999. The collected output is then parsed into JS, CSS and Other Items. CSS and JS file references with duplicate base names, if any, are reduced to just the first instance (paths are ignored). The inline JS is consolidated into one script tag only, and can be optionally minified “on the fly”, with [JShrink](https://github.com/tedious/JShrink) or [JSqueeze](https://github.com/tchwork/jsqueeze). At this point, Other Items are printed first, followed by CSS file references, JS file references and finally the consolidated inline script.

That said, if any plugin sets a priority greater than 99999 for its `theme_head` filter, its output will of course not get processed. The reverse happens for the `theme_body_close` filter, so the output of filters with a priority lower than 99999 (all of them, I hope) will be excluded from processing, leaving the order of its items unchanged. This allows themes and other plugins to pass variables from PHP to JavaScript if they use `theme_body_close` for their JS resources.

Elements belonging to groups to be moved, which must instead remain in their original positions for important reasons (i.e. some CSS needed at the top of head section or some analytical JS), can be kept apart from processing by using a filter option filled with suitable identification strings.

### What it takes
All of the above comes at a very low cost in terms of processing time, which with default options is **less than 1 millisecond** on both my local server and my live server. Enabling the inline JS minification, processing time rises to ∼ 2 - 15ms, depending on the number of plugins enabled, the server CPU speed/load and the algorithm chosen for minification. By removing unnecessary characters, JShrink reduces JS size by approximately 20%. It’s a bit faster than JSqueeze, which also performs some further optimizations in return saving up to 30% of the original size. In my tests, without any pretense of completeness, the difference in processing time between the two algorithms seems very similar to the difference in compression ratios.

Keep in mind, however, that this “on the fly” minification, once the inline code has already been shifted at the bottom of the DOM, is almost always useless, when it is not counterproductive, for SEO/performance optimization purposes, especially if gzip compression is enabled on the server.

***

### Installation
1. Download the latest release and extract the content
2. Use an FTP client to upload **only** the `tidy-assets` folder and the `tidy-asset.php` file into the `plugins` folder of your ZP installation
3. Activate **tidy-assets** in the Plugins > Seo tab of your admin area
4. Visit the plugin options page to set them according to your needs

### Translations
Tidy Assets is translatable by configuring a [Poedit](https://poedit.net/) catalogue with the keywords `gettext_pl`, `ngettext_pl:1,2`. If you wish to provide a translation other than Italian, which is already available, be sure to uncheck the box _Also use default keywords for supported languages_ in your catalogue properties, so that you don’t have to translate strings already managed by Zenphoto translators, but just the plugin specific strings.

### Credits
This plugin has been developed starting from [headConsolidator v1.4.3](https://www.zenphoto.org/news/headConsolidator/) by Stephen Billard (sbillard).

It includes some third party software for optional functionalities:
- [JShrink](https://github.com/tedious/JShrink) and [JSqueeze](https://github.com/tchwork/jsqueeze) for “on the fly” minification
- [jQuery](https://jquery.com/) to replace the version included in Zenphoto.

Refer to their sites for licensing information.

Special thanks to [acrylian](https://github.com/acrylian) for suggestions and support.
