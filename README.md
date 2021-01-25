# Tidy Assets
##  A plugin to rearrange [Zenphoto’s](https://www.zenphoto.org/) resources

### What it does
Tidy Assets shifts all Zenphoto JavaScript elements, including inline scripts and optionally CSS resources, to the bottom of the body element. This could improve the user experience on their first visit to your site, by delaying rendering-blocking resources.

It also offers the option to replace the outdated version of jQuery included in Zenphoto with the latest version available at the time of this plugin release date. The latter feature, however, is not suitable for every existing Zenphoto theme or plugin, as some of them still use functions that have been dropped in recent updates of jQuery. This is also the reason why Zenphoto doesn’t provide a newer version of the library yet.

Both of the above features could have a positive SEO impact, since the loading speed of a page is one of the parameters involved in determining its ranking. Moreover, the upgraded version of jQuery has a smaller size also, as some support for very old browsers has been removed.

Attributes such as type=“text/javascript” and type=“text/css” are no longer needed in HTML5 and are stripped from the output. Please note that other attributes such as “async” or “defer” are also deleted. This may change in future versions, but these attributes will most likely no longer be needed after the resources have been moved to the end of the body. However, they can also be left unchanged (and unmoved) using a filter option, as described in the next section.

In addition to the CSS and JS elements, the remaining HTML is also cleaned up slightly, by trimming lines and replacing multiple instances of white spaces or tabs with a single white space. Empty class attributes of HTML elements are dropped too.

### How it works
Tidy Assets collects the most part of the Zenphoto PHP output, starting from the first line printed by the `theme_head` registered filters and stopping at the position where the `theme_body_close` register filter is placed in the theme scripts, which is just before the closing body tag. To do so, the filters of this plugin are set with a priority of 99999. The collected output is then parsed into JS, CSS and Other Items. CSS and JS file references with duplicate base names, if any, are reduced to just the first instance (paths are ignored). The inline JS is consolidated into one script tag only, and can be optionally minified “on the fly”, with [JShrink](https://github.com/tedious/JShrink) or [JSqueeze](https://github.com/tchwork/jsqueeze). At this point, Other Items are reprinted first, followed by CSS files references, JS files references and at last by the consolidated inline script.

That said, if some plugin sets a priority higher than 99999 for its `theme_head` filter, its output will of course not get processed. The opposite happens for the `theme_body_close` filter, so that the output of filters with a priority lesser than 99999 (all of them, I hope) will be excluded from processing, leaving untouched the order of its items. This allows themes and other plugins to pass variables from PHP to JavaScript if they use `theme_body_close` for their JS assets.

Elements belonging to shifting groups, which must instead remain in their original positions for important reasons (i.e. some CSS needed at the top of head section or some analytical JS), can be kept apart from processing by using a filter option filled with suitable identification strings.

### What it takes
All of the above comes at a very low cost in terms of processing time, which with default options is lesser than 1 millisecond on both my local server and my live server. With inline JS minification active, time rises to ∼ 2 - 15ms, depending on the number of plugins enabled, on the server CPU speed/load and on the algorithm chosen for minification. By removing unnecessary characters, JShrink reduces JS size by approximately 20%. It’s a bit faster than JSqueeze, which in return does some further optimizations as well, sparing up to 30% of the original size. In my tests, without any claims of completeness, the difference in processing time between the two algorithms is very similar to the difference in compression ratios.

Keep in mind, however, that this “on the fly” minification, once inline code has already been shifted at the bottom of the DOM, is almost always useless when not counterproductive for SEO/performance optimization purposes, especially if gzip compression is enabled on the server.

***
### Installation
1. Download the latest release and extract the content
2. Use an FTP client to upload **only** the `tiny-assets` folder, including its contents, and the `tiny-asset.php` file into the `plugins` folder of your ZP installation
3. Activate **tiny-assets** in the Plugins > Seo tab of your admin area
4. Visit the plugin options page to set them according to your needs

### Translations
Tidy Assets is translatable by configuring a [Poedit](https://poedit.net/) catalogue with the keywords `gettext_pl`, `ngettext_pl:1,2`. If you wish to provide a translation other than Italian, which is already available, be sure to uncheck the box _Also use default keywords for supported languages_ in your catalogue properties, so that you don’t have to translate strings already managed by Zenphoto translators, but just the plugin specific strings.

### Credits
This plugin has been developed starting from [headConsolidator v1.4.3](https://www.zenphoto.org/news/headConsolidator/) by Stephen Billard (sbillard).

It includes some third party libraries, though they are not used with default settings. These are [JShrink](https://github.com/tedious/JShrink) and [JSqueeze](https://github.com/tchwork/jsqueeze), for optional “on the fly” minification, and [jQuery](https://jquery.com/), to optionally replace the version included in Zenphoto. Please refer to their sites for license information.

A special thanks to [acrylian](https://github.com/acrylian) for suggestions and support.
