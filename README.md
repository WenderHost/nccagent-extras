# NCCAgent Extras #
**Contributors:** [the_webist](https://profiles.wordpress.org/the_webist)  
**Requires at least:** 4.5  
**Tested up to:** 5.4  
**Stable tag:** 2.4.1  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Helper code for the NCC website.

## Description ##

I've built the NCC website using Elementor and the Hello Elementor theme. This is where I add additional code and CSS.

## Changelog ##

### 2.4.1 ###
* Bumping Carrier Documents Library min-height to 1200px.

### 2.4.0 ###
* Setting `min-height: 800px` for the Carrier Documents Library to prevent "page jump to footer" on "shorter" listing views.
* Updating "log in" link for logged out users who access the Carrier Documents Library.

### 2.3.9 ###
* Setting "Cigna - Medicare Advantage Only" to "Standard Contracting" and "Cigna - All but Medicare Advantage" to "SureLC".
* Product Finder: Moving "State Availability" inside "Plan Details".
* Adding navigation note for Document Library users.

### 2.3.8 ###
* Added CSS to prevent background/<body> scroll while JetMenu mega menu is open.
* Added CSS utility classes for showing/hiding mobile mega menu items based on user's logged-in status.

### 2.3.7 ###
* Setting ul > li bullets to "chevron-circle-right".

### 2.3.6 ###
* Left aligning text for Product Finder dropdowns and Marketer Display.
* Removing transient for `[team_member_list/]` query.

### 2.3.5 ###
* Changing bulleted list bullets to NCC Navy.

### 2.3.4 ###
* Updating link text from "Permalink:..." to "View this information as a web page."
* Changing "Log In" link text to "Log In or Register".
* Adding a transient for `[team_member_list/]` query.
* Adding bulleted list options.

### 2.3.3 ###
* Allow Plan by State Selector to function when the user has not selected a "State", but we have a saved "State".

### 2.3.2 ###
* Pre-select the Plan Selector when we have a saved "State".

### 2.3.1 ###
* Making chiclets look less "clickable".
* Product Finder: Made Market's telephone number a clickable link.
* Adding Product Kit Request and Online Contracting CTAs to Carrier pages.
* Skipping non-published products during Carrier export.
* Not showing non-published products in Carrier accordion.


### 2.3.0 ###
* Adding the Online Contracting settings.

### 2.2.1 ###
* Preventing Product Finder Selector from redirecting when a "State" has not been selected.
* Adding Product Finder Selector notification message below drop down.
* Adding "by State" to the Product Finder Selector heading.

### 2.2.0 ###
* Plan by State Selector which facilitates loading the Product Finder with the State and Product selected.
* Updating Kit Request URL.

### 2.1.7 ###
* Adding Product Kit CTA to Product Finder product descriptions.

### 2.1.6 ###
* Adding scroll to top when user changes the page for the Product Finder.
* Updating the sort for the Product Finder &gt; Product dropdown (Medicare products first followed by all other products in alphabetical order).
* Updating Product Finder "See more information right here" link text to "See plan details right here".

### 2.1.5 ###
* HOTFIX: Marketer avatar was overflowing Product Finder.

### 2.1.4 ###
* Updating Quick Links:
  * Restoring link back to Carrier and setting link text to "All ${carrier_name} Products".
  * Updating link text for online contracting to "Contract with ${carrier_name} Online".
* Styling for Marketer Testimonials.

### 2.1.3 ###
* Adding Marketer's state to the marketer title displayed in the Product Finder.
* Mobile styling tweaks to the marketer display in the Product Finder.

### 2.1.2 ###
* Adding a `create_user_message`.

### 2.1.1 ###
* Removing `ncc_set_html_mail_content_type()` and setting the content type directly in calls to `wp_mail()` with the proper email header.

### 2.1.0 ###
* Adding an NCC Settings > Email page for defining the "Delete User Message" sent to a user whose account has been deleted/unapproved.
* Implementing ACF JSON storage for keeping ACF settings in sync.
* Have WordPress generate the user password when submitting the Elementor form named `wordpress_and_hubspot_registration`.
* Updating link for "Register". Changing from `/login` to `/register`.
* Alerting user when trying to register with an email that already exists.

### 2.0.8 ###
* Updating `.details-link` and `#reset-form` links to match heading font.

### 2.0.7 ###
* Updating "Free Carrier Contracting Kit" to "Free Carrier Product Kit".
* Styling for Product Finder: adjusting `font-family` for various elements.

### 2.0.6 ###
* Square aspect ratio for Team Member photos.

### 2.0.5 ###
* Matching Marketer Testimonial display to Elementor Testimonial.

### 2.0.4 ###
* HOTFIX: Moving `preventDefault()` call in accordion click handler so that Carrier &gt; Product permalinks will work.

### 2.0.3 ###
* Adding Carrier Docs link to Quick Links.
* Adding transient caching to `ncc_get_template()`.
* Refactoring `.accordion-toggle` JS to work with all child elements.

### 2.0.2 ###
* Removing `<code/>` around state chiclets.
* Disabling loading of fonts via `main.scss` to allow theme/Elementor to handle this. Doing so restores `<strong/>` to proper font-weight.
* Updating Font Awesome icons.
* Supercrumbs: Adding Carrier name on Carrier and Carrier > Product pages.

### 2.0.1 ###
* HOTFIX: Commiting compiled SCSS.

### 2.0.0 ###
* Product Finder design updates:
  * Removed underline of product titles.
  * Removed zebra striping of product results.
  * Added Carrier &gt; Product permalink to "More information" child row.
  * Increased cell padding for result rows.
  * Updated state "chiclet" color scheme.
  * Changed "Product Details" link text to "See more information right here".
  * Removed parent product table cell's bottom border which child row is displayed.
  * Repositioned "reset form" link next to "Product Finder" header.
* Quick Links updates:
  * Removed "Back to ${carrier}" link.
  * Changed "Contacting Kit" link text to "Product Kit".
  * Fixed "Online Contracting" link.

### 1.9.6 ###
* Capitalizing Carrier Document Library breadcrumbs.
* Standardizing HTML heading elements for Carrier &gt; Product pages.
* Displaying Carrier Document Library link on Carrier &gt; Products pages.

### 1.9.5 ###
* Adding `<meta http-equiv="X-UA-Compatible" content="IE=edge;" />` for IE11 compatibility.

### 1.9.4 ###
* Adding "reset form" link to Product Finder.

### 1.9.3 ###
* Updating Carrier Documents library to use secure links (i.e. https).

### 1.9.2 ###
* Adding `HS_AGENT_REGISTRATION_FORM_ID` to `wp-config.php`.
* Adding `alternate_product_name` to `wp ncc carriers import`.
* Testing for empty "date" fields inside `_map_row_values()` to prevent "Review Date" and "Source File Date" from being set as the current day's date when that field is empty in the CSV.

### 1.9.1 ###
* Updating labels for Cigna in the "Contract Online" GravityForm.

### 1.9.0 ###
* Updating all trailing supercrumbs to turquoise.
* Removing "Carrier", "Product", and "Carrier > Product" breadcrumb when we are on one of those pages.
* Adding Carrier name to Product permalink text.
* Translating "Lost your password?" to "Forgot password?"
* Changing `/my-profile/` to `/dashboard/`.
* Filtering Top Bar nav with `add_dashboard_link()` to provide "My Dashboard" link to logged in users.
* Adding `[logouturl]` shortcode for use in Elementor buttons or anywhere else we need to add a logout link.
* Updating supercrumbs on Team Member pages to display "About > Staff".

### 1.8.6 ###
* BUGFIX: Correcting reference to Product Finder API.

### 1.8.5 ###
* Changing "Plan Finder" to "Product Finder".

### 1.8.4 ###
* Updating `Register` link to redirect to `/login/`.
* Updating Supercrumbs to only show the current page if that page has child elements.

### 1.8.3 ###
* Updating Plan Finder product links to use `/carrier/${carrier}/${product}` permalinks.
* Updating current item for Supercrubms to use `<span/>` instead of `<strong/>`.
* Updating `ul > li` chevrons to `#26ace2`.
* Handling links to `/product/${product}/${carrier}`. Showing a list of products under a category when there are multple ones for a Carrier or redirecting to the `/carrier/${carrier}/${product}/` page if there is only one.

### 1.8.2 ###
* Styling for Supercrumbs: increasing right margin from 5 to 8px, updating down chevron to NCC Blue `#26ACE2`.

### 1.8.1 ###
* Accouting for multiple names in a Team Member's first name.

### 1.8.0 ###
* Updating default Avatar.

### 1.7.9 ###
* Plan Finder: Updating styles for plans.
* Plan Finder: Updating styles for marketer display.
* Added `permalink` to Team Member REST API.

### 1.7.8 ###
* Online Contracting: Adding options for Aetna/Cigna SureLC &amp; Standard sign ups.
* Supercrumbs: Removing "bold" text from last breadcrumb.

### 1.7.7 ###
* Updating the breadcrumb separator to a slash.
* Removing hyperlink URL display from print CSS.

### 1.7.6 ###
* Adjusting CSS for ul > li with chevrons.

### 1.7.5 ###
* Updating Plan Finder accordion control.
* Updating CSS for Carrier products accordion.

### 1.7.4 ###
* Updating link text for meeting schedule link (e.g. "Book a time with `$firstname`")
* `ncc_get_template()` now accepts `search` and `replace` arguments.
* Adding `npm run devbuild` for building dev CSS.
* FIX: Removing chevrons from Gravity Forms checkbox.
* FIX: Removing chevrons from `.elementor-icon-list-items`.

### 1.7.3 ###
* FIX: Removing chevrons from Gravity Forms forms.

### 1.7.2 ###
* FIX: Removing chevrons from directory lister.
* Updating Agent Docs login link to point to `/login/`.

### 1.7.1 ###
* Adding "Quick Links" for display at the bottom of "Product &gt; Carrier" and "Carrier &gt; Product" pages.
* Adding chevron SVG as list item image for unordered lists.

### 1.7.0 ###
* NEW: Saved `state` for Plan Finder. Plan Finder now remembers your search settings after you browse to another page on the site.

### 1.6.2 ###
* NEW: `ncc_get_template()` for loading templates from `lib/html/`.
* Converting `lib/fns/utilities.php` to function name-based namespacing.
* Adding templates for "Contract Online CTA" and "Free Carrier Contracting Kit CTA".

### 1.6.1 ###
* NEW: `[carrierproduct]` shortcode.
* NEW: `[productcarrier]` shortcode.
* Added permalink to `[acf_carrier_products]` product descriptions.
* FIX: Updating the column number for sorting in the Plan Finder product dropdown.

### 1.6.0 ###
* Adding rewrite tag for `carrierproduct`.
* `[carrierdocs]` shortcode.
* `[carrierproduct]` shortcode.

### 1.5.9 ###
* Adding rewrites for `/carrier/${carrier}/${product}` paths.

### 1.5.8 ###
* Disabling Custom Login URL to allow for functioning of iThemes Security Plugin.
* Adding login redirect to user dashboard after login.

### 1.5.7 ###
* Updating `wp ncc carriers import` to handle Lower/Upper Issue Age import

### 1.5.6 ###
* Plan Finder: Updating drop down placeholder styling (`color: #000; font-weight: normal`).
* Plan Finder: Updating `plan-finder.js` to reference the correct column when ordering Products.
* Updating `[mymarketer]` to show nothing when no Marketer is assigned to the user.
* Adding `global.js` with `e.preventDefault()` for main menu link clicks where `href="#"`.
* Updating `[team_member_list]` to link to all Marketers.

### 1.5.5 ###
* Adjusting the CSS for Marketer photos.
* Adding a "No Marketer Found" alert to `[mymarketer]` when a user either doesn't have a Marketer assigned or their assigned Marketer has a `post_status` other than "publish".

### 1.5.4 ###
* Adding calendar link to Marketer's display

### 1.5.3 ###
* Adding import/export of "Review Date", "Source File Name", and "Source File Date" for `wp ncc carriers import/export`.

### 1.5.2 ###
* Adding `wp ncc users import`.
* Updating user import to include `NPN` and `Marketer`.
* Adding `Email` column for admin Team Member CPT list.

### 1.5.1 ###
* Adjusting CSS for Marketer Display in Product Finder.

### 1.5.0 ###
* `wp ncc import` now creates new Carrier Products when no `Row_ID` is provided.
* Added `_states_to_array()` to `wp ncc` to handle cleaning States data.

### 1.4.9 ###
* Removing `title` attribute from page in `[supercrumbs]`.
* Adding `--carrier=<carrier_name>` option to `wp ncc export`.

### 1.4.8 ###
* Adding `[team_member_list]`.

### 1.4.7 ###
* Adding `[marketer_contact_details]`.

### 1.4.6 ###
* Adding `[marketer_testimonials]`.

### 1.4.5 ###
* Moving user's Team Member/Marketer association to the user's meta.

### 1.4.4 ###
* Responsive styling for `[mymarketer /]`.

### 1.4.3 ###
* Styling for Team Member photos in admin listing.
* Initial implementation of `[mymarketer /]` shortcode for displaying Marketer information on the My Profile page.

### 1.4.2 ###
* Adding `Staff Type(s)` and `Photo` columns to Team Members admin listing.
* Adding `admin.css` for styling Team Members columns admin listing.

### 1.4.1 ###
* Adding dropdown to last page in breadcrumb for `[supercrumbs]`.
* Refactoring "down chevron" for `[supercrumbs]`.

### 1.4.0 ###
* Adding `[supercrumbs]` shortcode for "Super Breadcrumbs" with dropdown menus for child pages.

### 1.3.1 ###
* Removing unpublished products from the Plan Finder.

### 1.3.0 ###
* Adding Marketer Database

### 1.2.0 ###
* Adding Plan Finder Help Graphic option

### 1.1.0 ###
* Adding `wp ncc export` for exporting a CSV of Carriers and Products
* Adding `wp ncc import` for importing a CSV of Carriers and Products

### 1.0.3 ###
* Bug fix: Striping HTML from DataTables `<select />` options.

### 1.0.2 ###
* Linking Carriers and Products in search results

### 1.0.1 ###
* Updating NPM packages
* Adding `GitHub Plugin URI` to plugin header for compatibility with the [GitHub Updater plug](https://github.com/afragen/github-updater)

### 1.0.0 ###
* Initial release
