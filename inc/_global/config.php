<?php
/**
 * _global/config.php
 *
 * Author: pixelcave
 *
 * Global configuration file
 *
 */

// Include required classes
require __DIR__ . '/../_classes/Template.php';

// **************************************************************************************************
// CODEBASE OBJECT
// **************************************************************************************************

//                              : Name, version and assets folder's name
$cb = new Template('Codebase', '5.10', '/assets');

// **************************************************************************************************
// DATABASE CONNECTION (using PDO)
// **************************************************************************************************

$dbHost   = 'localhost';                   // or '127.0.0.1'
$dbName   = 'demobaqu_generatormain';      // your database name
$dbUser   = 'demobaqu_generatoruser';      // your database username
$dbPass   = 'hFG[c8!ayz4t';                // your database password
$charset  = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=$charset";

// PDO options for error handling and fetch mode
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create the PDO instance
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (\PDOException $e) {
    // In production, log this exception rather than displaying it
    die('Database Connection Failed: ' . $e->getMessage());
}

$site = [
    // …
    'timezone'           => 'Europe/London',   // ← pick any from http://php.net/manual/en/timezones.php
    // …
];

// **************************************************************************************************
// FETCH SITE SETTINGS
// **************************************************************************************************

$stmt   = $pdo->query("SELECT * FROM `demon_site_settings` ORDER BY `id` DESC LIMIT 1");
$site   = $stmt->fetch();

// Fallback defaults in case some settings are NULL or missing
$site_name        = $site['site_name']        ?? 'Your Site Name';
$site_tagline     = $site['site_tagline']     ?? '';
$site_description = $site['site_description'] ?? '';
$site_url         = $site['site_url']         ?? '';
$logo_url         = $site['logo_url']         ?? '';
$favicon_url      = $site['favicon_url']      ?? '';
$site_robots      = $site['site_robots']      ?? 'index, follow';
$site_author      = $site['site_author']      ?? '';

// **************************************************************************************************
// GLOBAL META & OPEN GRAPH DATA (FROM DATABASE)
// **************************************************************************************************

//                              : The data is added in the <head> section of the page
$cb->author      = $site_author;
$cb->robots      = $site_robots;
$cb->title       = $site_name . ($site_tagline ? ' – ' . $site_tagline : '');
$cb->description = $site_description;

//                              : The URL of your site, used in Open Graph Meta Data
$cb->og_url_site  = $site_url;

//                              : The URL of your image/logo, used in Open Graph Meta Data
$cb->og_url_image = $logo_url ?: $favicon_url;

// **************************************************************************************************
// GLOBAL GENERIC
// **************************************************************************************************

// ''                           : default color theme
// 'elegance'                   : elegance color theme
// 'pulse'                      : pulse color theme
// 'flat'                       : flat color theme
// 'corporate'                  : corporate color theme
// 'earth'                      : earth color theme
$cb->theme          = '';

// true                         : Enables Page Loader screen
// false                        : Disables Page Loader screen
$cb->page_loader    = false;

// true                          : Remembers active color theme between pages using localStorage
// false                         : Does not remember the active color theme
$cb->remember_theme = true;

// **************************************************************************************************
// GLOBAL INCLUDED VIEWS
// **************************************************************************************************

//                              : Useful for adding different sidebars/headers per page or per section
$cb->inc_side_overlay = '';
$cb->inc_sidebar      = '';
$cb->inc_header       = '';
$cb->inc_footer       = '';

// **************************************************************************************************
// GLOBAL SIDEBAR & SIDE OVERLAY
// **************************************************************************************************

// true                         : Left Sidebar and right Side Overlay
// false                        : Right Sidebar and left Side Overlay
$cb->l_sidebar_left            = true;

// true                         : Mini hoverable Sidebar (screen width > 991px)
// false                        : Normal mode
$cb->l_sidebar_mini            = false;

// true                         : Visible Sidebar (screen width > 991px)
// false                        : Hidden Sidebar (screen width < 992px)
$cb->l_sidebar_visible_desktop = true;

// true                         : Visible Sidebar (screen width < 992px)
// false                        : Hidden Sidebar (screen width < 992px)
$cb->l_sidebar_visible_mobile  = false;

// true                         : Dark themed Sidebar
// false                        : Light themed Sidebar
$cb->l_sidebar_dark            = false;

// true                         : Hoverable Side Overlay (screen width > 991px)
// false                        : Normal mode
$cb->l_side_overlay_hoverable  = false;

// true                         : Visible Side Overlay
// false                        : Hidden Side Overlay
$cb->l_side_overlay_visible    = false;

// true                         : Enables a visible clickable Page Overlay when Side Overlay opens
// false                        : Disables Page Overlay when Side Overlay opens
$cb->l_page_overlay            = true;

// true                         : Custom scrolling (screen width > 991px)
// false                        : Native scrolling
$cb->l_side_scroll             = true;

// **************************************************************************************************
// GLOBAL HEADER
// **************************************************************************************************

// true                         : Fixed Header
// false                        : Static Header
$cb->l_header_fixed = false;

// ''                           : Classic Header style
// 'modern'                     : Modern Header style
// 'dark'                       : Dark themed Header (works only with classic Header style)
// 'light-glass'                : Light themed Header with transparency by default
// 'dark-glass'                 : Dark themed Header with transparency by default
$cb->l_header_style = 'modern';

// **************************************************************************************************
// GLOBAL MAIN CONTENT
// **************************************************************************************************

// ''                           : Full width Main Content
// 'boxed'                      : Full width Main Content with a specific maximum width
// 'narrow'                     : Full width Main Content with a percentage width
$cb->l_m_content = 'boxed';

// **************************************************************************************************
// GLOBAL MAIN MENU
// **************************************************************************************************

// It will get compared with the url of each menu link to make the link active.
$cb->main_nav_active = basename($_SERVER['PHP_SELF']);

// You can use the following array to create your main menu
$cb->main_nav = array();
