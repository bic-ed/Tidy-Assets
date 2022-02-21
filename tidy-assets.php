<?php
/**
 * Developed starting from
 * {@link https://www.zenphoto.org/news/headConsolidator/ headConsolidator v1.4.3}
 * by Stephen Billard (sbillard).
 *
 * Tidy Assets collects the most part of the Zenphoto’s PHP output, starting
 * from the first line printed by the "theme_head" registered filters and
 * stopping at the position where the "theme_body_close" filter registering is
 * placed in the PHP theme scripts, which is just before the closing body tag.
 * The collected output is then parsed into JS, CSS and Other Items. The inline
 * JS is consolidated into one script tag only, and can be optionally minified
 * “on the fly”, with JShrink or JSqueeze. At the end, Other Items are printed
 * first, followed by CSS files references, JS files references and at last by
 * the consolidated inline script.
 *
 * @author Antonio Ranesi (bic-ed)
 * @license GPL v2 or later
 * @package plugins
 * @subpackage tidy-assets
 */

$plugin_is_filter = 1 | THEME_PLUGIN;
$plugin_description = gettext_pl("Shifts all Zenphoto JavaScript elements, including inline scripts and optionally CSS resources, to the bottom of the body element. It also offers the opportunity to remove the jQuery Migrate plugin, which is included with Zenphoto to ensure compatibility with older themes and plugins.", "tidy-assets");
$plugin_author = 'Antonio Ranesi (bic-ed)';
$plugin_version = '1.0.0';
$plugin_date = '25/01/2021';
$plugin_category = gettext('SEO');
$plugin_URL = "http://www.antonioranesi.it/pages/tidy-assets-zenphoto-plugin";
$plugin_siteurl = "http://www.antonioranesi.it/pages/tidy-assets-zenphoto-plugin";

$option_interface = 'tidyAssetsOptions';

zp_register_filter('theme_head', 'tidyAssets::startCollecting', 99999);
zp_register_filter('theme_body_close', 'tidyAssets::parseAndOutput', 99999);


class tidyAssetsOptions {
  /**
  * class instantiation function
  *
  */
  function __construct() {
    setOptionDefault('tidy-assets_jquery', false);
    setOptionDefault('tidy-assets_jq_migrate', 0);
    setOptionDefault('tidy-assets_css', 0);
    setOptionDefault('tidy-assets_comments', 1);
    setOptionDefault('tidy-assets_minify', false);
    setOptionDefault('tidy-assets_skip', '');
  }

  function getOptionsSupported() {
    // global $_zp_admin_tab;
    $jshrink = '<a rel="noopener" target="_blank" href="https://github.com/tedious/JShrink">JShrink</a>';
    $jsqueeze = '<a rel="noopener" target="_blank" href="https://github.com/tchwork/jsqueeze">JSqueeze</a>';
    $options = array(
      gettext_pl("jQuery Host", "tidy-assets") => array(
        'key' => 'tidy-assets_jquery',
        'type' => OPTION_TYPE_SELECTOR,
        'order' => 90,
        'selections' => array(
          // '1.' . gettext_pl("Self hosted", "tidy-assets") => '/plugins/tidy-assets/jquery/jquery-3.5.1.min.js',
          'jQuery' => 'https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous',
          'Google' => 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js',
          'Microsoft' => 'https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.6.0.min.js',
          'Cloudflare' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js',
          'jsDelivr' => 'https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js',
        ),
        'null_selection' => gettext_pl("Self Hosted", "tidy-assets"),
        'desc' => gettext_pl("Select your preferred server among self hosting (recommended) or one of the CDN services listed on the jQuery website.", "tidy-assets") . " "
        . gettext_pl("The self hosted version is the one provided by your Zenphoto installation.", "tidy-assets")
        . " <strong>"
        . gettext_pl("The CDNs hosted version is", "tidy-assets")
        . " v3.6.0 </strong></p>"
        . "<p class='notebox'>"
        . gettext_pl("<strong>Notice:</strong> Provision of assets from a CDN service is explicitly discouraged by Zenphoto guidelines. It may result in the need to be accounted for in your privacy policy terms (in some jurisdictions). Select <strong>Self Hosted</strong> if you are not sure how to do it correctly or if you want to keep full control of your site.", "tidy-assets")
        . "</p>"
      ),
      gettext_pl("Remove jQuery Migrate", "tidy-assets") => array(
        'key' => 'tidy-assets_jq_migrate',
        'type' => OPTION_TYPE_CHECKBOX,
        'order' => 15,
        'desc' => gettext_pl("Check to remove jQuery Migrate from your site front-end.", "tidy-assets")
        . "<br>"
        . "<p class='notebox'>" . gettext_pl("Check that everything works as expected after enabling this option.", "tidy-assets")
      ),
      gettext_pl("Apply to CSS assets", "tidy-assets") => array(
        'key' => 'tidy-assets_css',
        'type' => OPTION_TYPE_CHECKBOX,
        'order' => 20,
        'desc' => gettext_pl("Shifts the CSS assets to the bottom of the <code>body</code> element as well, just before the JavaScript elements.", "tidy-assets")
      ),
      gettext_pl("Output comments", "tidy-assets") => array(
        'key' => 'tidy-assets_comments',
        'type' => OPTION_TYPE_CHECKBOX,
        'order' => 30,
        'desc' => gettext_pl("Print comments in HTML output to let you know what has been relocated and where. Processing time is printed as well, in a comment at the end of the plugin output.", "tidy-assets")
      ),
      gettext_pl("Minify inline JavaScript", "tidy-assets") => array(
        'key' => 'tidy-assets_minify',
        'type' => OPTION_TYPE_SELECTOR,
        'order' => 40,
        'selections' => array(
          gettext_pl("With JSqueeze", "tidy-assets") => 'tidy-assets_jsqueeze',
          gettext_pl("With JShrink", "tidy-assets") => 'tidy-assets_jshrink',
        ),
        'null_selection' => gettext('No'),
        'desc' => sprintf(gettext_pl("By removing unnecessary characters, %s reduces the consolidated script size by approximately 20%%. It’s a bit faster than %s, which in return does some further optimizations as well, sparing up to 30%% of the original size. The difference in processing time between the two algorithms is very similar to the difference in compression ratios. Note that this <strong>“on the fly” minification</strong>, once inline code has already been shifted at the bottom of the DOM, <strong>is almost always useless</strong> when not counterproductive for SEO/performance optimization purposes, especially if gzip compression is enabled on the server.", "tidy-assets"), $jshrink, $jsqueeze)
      ),
      gettext_pl("Skip elements", "tidy-assets") => array(
        'key' => 'tidy-assets_skip',
        'type' => OPTION_TYPE_TEXTAREA,
        'order' => 45,
        'multilingual' => false,
        'desc' => '<p>'
        . gettext_pl("This is a filter used to prevent shifting specific elements that must remain in their original positions. For each of these items, if you have any, enter an unique identification string here, one per line. Leave blank to disable filtering.", "tidy-assets")
        . '</p><p>'
        . gettext_pl("For JS and CSS files, the best candidate is of course the file link, without quotes. If the link for a CSS file is <code>href=\"some_path/some_file.css\"</code>, you should use <code>some_path/some_file.css</code>, but even just <code>some_file.css</code> might be enough.", "tidy-assets")
        . '</p><p>'
        . gettext_pl("For an inline JS script, you will need to spot some part of the code that is not repeated identically in any other script. It could be the name of a function, your analytics code or so. A script containing <code>function pageselectCallback</code> could be added to the list by typing <code>function pageselectCallback</code> or even just <code>pageselectCallback</code>, provided that there are no other inline script containing the same string.", "tidy-assets")
        . '<p>'
      ),
    );
    return $options;
  }
  function handleOption($option, $currentValue) {

  }
}

class tidyAssets {

  static function startCollecting() {
    ob_start();
  }

  static function parseAndOutput() {
    $data = ob_get_contents();
    ob_end_clean();
    /**
     * Unique identification strings to recognize the elements not to be moved
     * @var array
     */
    $skip = explode(PHP_EOL, getOption('tidy-assets_skip'));
    foreach ($skip as $key => $value) {
      if (empty($value)) {
        unset($skip[$key]);
      }
    }

    if (getOption('tidy-assets_comments')) {
      $start = microtime(true);
    }

    // JavaScript assets
    $matches = tidyAssets::extract($data, '~<script.*src="(.*)".*></script>~mU', $skip);
    $js_files = array();
    $use_cdn_jq = getOption('tidy-assets_jquery');
    $remove_jq_migrate = getOption('tidy-assets_jq_migrate');
    while (!empty($matches[1])) { // flush out the duplicates. Earliest wins
      $file = array_pop($matches[1]);
      if ($use_cdn_jq && $file == "/zp-core/js/jquery.min.js") { // Use CDN for jQuery
        $file = $use_cdn_jq;
      }
      if ($remove_jq_migrate && $file == "/zp-core/js/jquery-migrate.min.js") { // Remove jQuery Migrate
        continue;
      }
      $js_files[basename($file)] = $file;
    }

    // Inline JavaScript
    $matches = tidyAssets::extract($data, '~<script.*>(.*)</script>~mUs', $skip);
    $jsi = $matches[1];
    if (!empty($jsi)) {
      $inline_js = '';
      foreach ($jsi as $somejs) {
        $inline_js .= '  ' . trim($somejs) . "\n";
      }
    }

    // CSS assets (if requested)
    if (getOption('tidy-assets_css')) {
      $matches = tidyAssets::extract($data, '~<link.*rel="stylesheet".*href="(.*)".*>~mU', $skip);
      $css_files = array();
      while (!empty($matches[1])) { // flush out the duplicates. Earliest wins
        $file = array_pop($matches[1]);
        $css_files[basename($file)] = $file;
      }
    }

    // Other Items
    $other_items = explode("\n", $data);
    foreach ($other_items as $key=>$line) {
      $line = trim($line);
      if (empty($line)) {
        unset($other_items[$key]);
      } else {
        // While we are here, let's do some additional cheap cleaning:
        // 1.Removing the empty class attribute produced sometimes by some Zenphoto functions
        $line = str_replace(' class=""', '', $line);
        // 2.Replacing multiple spaces/tabs with a single space
        $line = preg_replace('/\s\s+/', ' ', $line);
        $other_items[$key] = $line;
      }
    }

    // Start printing the output: Other Items...
    if (isset($start)) {
      echo "<!-- Tidy Assets Start-> Unmoved items, no changes before this line -->\n";
    }
    $other_items = implode("\n", $other_items);
    echo $other_items . "\n";

    // ...CSS Assets...
    if (!empty($css_files)) {
      $css_files = array_reverse($css_files);
      $css_files = '<link rel="stylesheet" href="'
      . implode('">' . "\n" . '<link rel="stylesheet" href="', $css_files) . '">' . "\n";
      if (isset($start)) {
        echo "<!-- Tidy Assets-> CSS files -->\n";
      }
      echo $css_files;
    }

    // ...JavaScript Assets...
    if (!empty($js_files)) {
      $js_files = array_reverse($js_files);
      $js_files = '<script src="'
      . implode('"></script>'."\n".'<script src="', $js_files) . '"></script>'."\n";
      if (isset($start)) {
        echo "<!-- Tidy Assets-> JavaScript files -->\n";
      }
      echo $js_files;
    }

    // ...Inline JavaScript...
    if (!empty($inline_js)) {
      $comment = "<!-- Tidy Assets-> Consolidated Inline JavaScript";
      if ($use_minifier = getOption('tidy-assets_minify')) {
        if ($use_minifier == 'tidy-assets_jshrink') {
          $comment .= ", minified with JShrink";
          require_once 'tidy-assets/JShrink/Minifier.php';
          $inline_js = \JShrink\Minifier::minify($inline_js) . "\n";
        } else {
          $comment .= ", minified with JSqueeze";
          require_once 'tidy-assets/jsqueeze/JSqueeze.php';
          $jz = new \Patchwork\JSqueeze();
          $inline_js = $jz->squeeze($inline_js) . "\n";
        }
      }
      if (isset($start)) {
        echo $comment . " -->\n";
      }
      echo "<script>\n" . $inline_js . "</script>\n";
    }

    // ...Ending comment and processing time.
    if (isset($start)) {
      $end = microtime(true);
      echo "<!-- Tidy Assets End-> No changes after this line. Completed in "
      . round(($end - $start) * 1000, 2) . " ms -->\n";
    }
    /* NOTE: Uncomment below to print the filtering array for debugging */
    // echo "<!-- Filter's search strings\n======================\n";
    // print_r($skip);
    // echo "====================== -->\n";
  }
  /**
   * Stores matches of regex and remove them from searched string (if not on the filter list)
   * @author sbillard
   * @author bic-ed
   * @param  string $data     Before the first run, this is the collected HTML output (ob_get_contents()).
   *                          After that, already processed items are purged time by time.
   * @param  string $pattern  query string
   * @param  array  $filter   Search strings for detecting items not to be moved (bic-ed)
   * @return array            Filtered matches
   */
  static function extract(&$data, $pattern, $filter) {
    preg_match_all($pattern, $data, $matches);
    foreach ($matches[0] as $key => $found) {
      if (!empty($filter)) {
        if (tidyAssets::strposArray($matches[1][$key], $filter)) {
          unset($matches[1][$key]);
        } elseif (!empty($matches[1][$key])) { // Otherwise, parsing inline JS purges unmoved JS files
          $data = trim(str_replace($found, '', $data));
        }
      } else {
        $data = trim(str_replace($found, '', $data));
      }
    }
    return $matches;
  }
  /**
   * strpos on each value of an array
   * @param  string   $haystack The string to search in
   * @param  array    $needle   Each value is a search string
   * @return boolean            True on first match; False if no matches
   */
  static function strposArray($haystack, $needle) {
    foreach($needle as $what) {
        if((strpos($haystack, $what)) !== false) return true;
    }
    return false;
  }
}
