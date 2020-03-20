=== NCCAgent Extras ===
Contributors: the_webist
Requires at least: 4.5
Tested up to: 5.3.2
Stable tag: 1.7.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Helper code for the NCC website.

== Description ==

I've built the NCC website using Elementor and the Hello Elementor theme. This is where I add additional code and CSS.

== Changelog ==

= 1.7.9 =
* Plan Finder: Updating styles for plans.
* Plan Finder: Updating styles for marketer display.
* Added `permalink` to Team Member REST API.

= 1.7.8 =
* Online Contracting: Adding options for Aetna/Cigna SureLC &amp; Standard sign ups.
* Supercrumbs: Removing "bold" text from last breadcrumb.

= 1.7.7 =
* Updating the breadcrumb separator to a slash.
* Removing hyperlink URL display from print CSS.

= 1.7.6 =
* Adjusting CSS for ul > li with chevrons.

= 1.7.5 =
* Updating Plan Finder accordion control.
* Updating CSS for Carrier products accordion.

= 1.7.4 =
* Updating link text for meeting schedule link (e.g. "Book a time with `$firstname`")
* `ncc_get_template()` now accepts `search` and `replace` arguments.
* Adding `npm run devbuild` for building dev CSS.
* FIX: Removing chevrons from Gravity Forms checkbox.
* FIX: Removing chevrons from `.elementor-icon-list-items`.

= 1.7.3 =
* FIX: Removing chevrons from Gravity Forms forms.

= 1.7.2 =
* FIX: Removing chevrons from directory lister.
* Updating Agent Docs login link to point to `/login/`.

= 1.7.1 =
* Adding "Quick Links" for display at the bottom of "Product &gt; Carrier" and "Carrier &gt; Product" pages.
* Adding chevron SVG as list item image for unordered lists.

= 1.7.0 =
* NEW: Saved `state` for Plan Finder. Plan Finder now remembers your search settings after you browse to another page on the site.

= 1.6.2 =
* NEW: `ncc_get_template()` for loading templates from `lib/html/`.
* Converting `lib/fns/utilities.php` to function name-based namespacing.
* Adding templates for "Contract Online CTA" and "Free Carrier Contracting Kit CTA".

= 1.6.1 =
* NEW: `[carrierproduct]` shortcode.
* NEW: `[productcarrier]` shortcode.
* Added permalink to `[acf_carrier_products]` product descriptions.
* FIX: Updating the column number for sorting in the Plan Finder product dropdown.

= 1.6.0 =
* Adding rewrite tag for `carrierproduct`.
* `[carrierdocs]` shortcode.
* `[carrierproduct]` shortcode.

= 1.5.9 =
* Adding rewrites for `/carrier/${carrier}/${product}` paths.

= 1.5.8 =
* Disabling Custom Login URL to allow for functioning of iThemes Security Plugin.
* Adding login redirect to user dashboard after login.

= 1.5.7 =
* Updating `wp ncc carriers import` to handle Lower/Upper Issue Age import

= 1.5.6 =
* Plan Finder: Updating drop down placeholder styling (`color: #000; font-weight: normal`).
* Plan Finder: Updating `plan-finder.js` to reference the correct column when ordering Products.
* Updating `[mymarketer]` to show nothing when no Marketer is assigned to the user.
* Adding `global.js` with `e.preventDefault()` for main menu link clicks where `href="#"`.
* Updating `[team_member_list]` to link to all Marketers.

= 1.5.5 =
* Adjusting the CSS for Marketer photos.
* Adding a "No Marketer Found" alert to `[mymarketer]` when a user either doesn't have a Marketer assigned or their assigned Marketer has a `post_status` other than "publish".

= 1.5.4 =
* Adding calendar link to Marketer's display

= 1.5.3 =
* Adding import/export of "Review Date", "Source File Name", and "Source File Date" for `wp ncc carriers import/export`.

= 1.5.2 =
* Adding `wp ncc users import`.
* Updating user import to include `NPN` and `Marketer`.
* Adding `Email` column for admin Team Member CPT list.

= 1.5.1 =
* Adjusting CSS for Marketer Display in Product Finder.

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
