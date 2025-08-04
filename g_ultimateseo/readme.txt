=== G_UltimateSEO ===
Contributors: Gerald Ferreira
Tags: seo, sitemap, meta tags, performance, social media, schema, content analysis
Requires at least: 5.3
Tested up to: 5.8
Requires PHP: 7.2
Stable tag: 1.0
License: Proprietary
License URI: https://geraldferreira.com/license

G_UltimateSEO is a revolutionary SEO optimization and performance plugin designed to elevate your WordPress site's search engine visibility and user experience.

== Description ==

G_UltimateSEO is a cutting-edge WordPress plugin tailored for boosting your website's SEO effectiveness and overall performance. This plugin provides an intuitive and comprehensive suite of tools for optimizing your content, managing meta tags, improving site speed, and ensuring your site is search engine friendly.

Key Features:
- **SEO Content Analysis**: In-depth analysis of your content for optimal keyword placement, readability, and SEO effectiveness.
- **XML Sitemaps**: Automated generation of XML sitemaps to enhance search engine indexing.
- **Meta Tag Management**: Easy control over meta titles, descriptions, and social media sharing information.
- **Content Insights**: Insights into the most used words and phrases in your content to guide your keyword strategy.
- **Social Media Optimization**: Tailored tags and formats for enhanced social media sharing.
- **Performance Optimization**: Features aimed at improving site speed, including advanced caching and image optimization.
- **Schema Markup Generation**: Support for a variety of schema types to help search engines understand your content better.
- **SEO Scoring**: A dynamic scoring system providing immediate feedback on your content's SEO readiness.

== Installation ==

1. Upload the `g_ultimateseo` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the G_UltimateSEO menu in your WordPress admin to configure the plugin.

== Frequently Asked Questions ==

= Is G_UltimateSEO easy to use for beginners? =
Absolutely! G_UltimateSEO is designed with a user-friendly interface, making it accessible for users of all skill levels.

= Does G_UltimateSEO support custom post types? =
Yes, G_UltimateSEO provides comprehensive support for custom post types in WordPress.

= How do I get support for the plugin? =
For support, please visit https://geraldferreira.com/support.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release of G_UltimateSEO. Upgrade for a complete SEO solution for your WordPress site.

== Additional Resources ==
- Website: https://geraldferreira.com
- Support: https://geraldferreira.com/support
- Documentation: https://geraldferreira.com/documentation/g_ultimateseo


g-ultimate-seo/
├── g_ultimateseo.php                  [Main plugin file, entry point]
├── admin/
│   ├── db.php                         [Database setup and activation hooks]
│   ├── g_menu.php                     [Admin menu setup]
│   ├── mail/
│   │   ├── smtp.php
│   │   ├── smtp_settings.php
│   │   ├── send_test_email_tab.php
│   │   ├── dmarc_dkim_check_tab.php
│   │   ├── email_logger.php
│   │   ├── email_logs.php
│   │   └── functions.php
│   │
│   ├── pages/
│   │   └── add-site.php
│   │
│   └── siteinfo/
│       ├── siteinformation.php
│       ├── siteinformationadmin.php
│       ├── security-info.php
│       ├── siteplugins.php
│       ├── siteemailinfo.php
│       ├── sitebrokenlinks.php
│       ├── site404errors.php
│       ├── siteemailreport.php
│       ├── activity.php
│       └── activitylog.php
│
├── css/
│   └── styles.css
│
├── includes/
│   ├── ajax.php
│   ├── helpers.php
│   ├── render-site-table.php
│   ├── site-handler.php
│   └── files.php                      [Centralized includes]
│
├── menu_items/
│   ├── g_settings.php
│   └── g_seo_settings.php
│
├── js/
│   └── scripts.js                     [Optional, for custom JS/AJAX]
│
├── assets/                            [Optional, for images/icons used in the plugin]
│   ├── images/
│   └── icons/
│
├── languages/                         [Optional, for translations]
│   └── g-ultimate-seo.pot
│
└── readme.txt                         [Optional, recommended for plugin documentation]

 Explanation of Each Folder & File:
Root (g-ultimate-seo/)

g_ultimateseo.php: Main plugin entry file with plugin headers, activation/deactivation hooks.

admin/

Contains all admin-related PHP files.

db.php: Database activation scripts and table definitions.

g_menu.php: Defines the main admin menu and submenus.

admin/mail/

Handles SMTP email settings and functionalities.

SMTP configuration, test email sending, email logging, DMARC/DKIM/SPF checks.

admin/pages/

Admin-specific pages or functionality (e.g., adding sites).

admin/siteinfo/

Site monitoring, security checks, plugins overview, broken links checker, 404 error logs, and user activities.

css/

Plugin CSS styles (styles.css).

includes/

Helper scripts, AJAX handlers, and common logic files.

menu_items/

Settings-specific callbacks and field definitions.

js/ (Optional but Recommended)

JavaScript files for handling AJAX requests and dynamic functionality.

assets/ (Optional)

Images or icons used within the plugin admin pages.

languages/ (Optional)

Translation files for internationalization.

readme.txt (Optional but Recommended)

Detailed plugin description, instructions, changelog, and installation steps
