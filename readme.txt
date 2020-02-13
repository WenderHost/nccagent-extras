=== NCCAgent Extras ===
Contributors: the_webist
Requires at least: 4.5
Tested up to: 5.3.2
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Helper code for the NCC website.

== Description ==

I've built the NCC website using Elementor and the Hello Elementor theme. This is where I add additional code and CSS.

== Changelog ==

= 1.5.0 =
* `wp ncc import` now creates new Carrier Products when no `Row_ID` is provided.
* Added `_states_to_array()` to `wp ncc` to handle cleaning States data.

= 1.4.9 =
* Removing `title` attribute from page in `[supercrumbs]`.
* Adding `--carrier=<carrier_name>` option to `wp ncc export`.

= 1.4.8 =
* Adding `[team_member_list]`.

= 1.4.7 =
* Adding `[marketer_contact_details]`.

= 1.4.6 =
* Adding `[marketer_testimonials]`.

= 1.4.5 =
* Moving user's Team Member/Marketer association to the user's meta.

= 1.4.4 =
* Responsive styling for `[mymarketer /]`.

= 1.4.3 =
* Styling for Team Member photos in admin listing.
* Initial implementation of `[mymarketer /]` shortcode for displaying Marketer information on the My Profile page.

= 1.4.2 =
* Adding `Staff Type(s)` and `Photo` columns to Team Members admin listing.
* Adding `admin.css` for styling Team Members columns admin listing.

= 1.4.1 =
* Adding dropdown to last page in breadcrumb for `[supercrumbs]`.
* Refactoring "down chevron" for `[supercrumbs]`.

= 1.4.0 =
* Adding `[supercrumbs]` shortcode for "Super Breadcrumbs" with dropdown menus for child pages.

= 1.3.1 =
* Removing unpublished products from the Plan Finder.

= 1.3.0 =
* Adding Marketer Database

= 1.2.0 =
* Adding Plan Finder Help Graphic option

= 1.1.0 =
* Adding `wp ncc export` for exporting a CSV of Carriers and Products
* Adding `wp ncc import` for importing a CSV of Carriers and Products

= 1.0.3 =
* Bug fix: Striping HTML from DataTables `<select />` options.

= 1.0.2 =
* Linking Carriers and Products in search results

= 1.0.1 =
* Updating NPM packages
* Adding `GitHub Plugin URI` to plugin header for compatibility with the [GitHub Updater plug](https://github.com/afragen/github-updater)

= 1.0.0 =
* Initial release
