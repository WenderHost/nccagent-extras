# NCCAgent Extras #
**Contributors:** [the_webist](https://profiles.wordpress.org/the_webist)  
**Requires at least:** 4.5  
**Tested up to:** 5.6  
**Stable tag:** 4.0.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Helper code for the NCC website.

## Description ##

I've built the NCC website using Elementor and the Hello Elementor theme. This is where I add additional code and CSS.

## Changelog ##

### 4.0.2 ###
* Disabling "HubSpot Marketer Chat" in the REST API.

### 4.0.1 ###
* Adding validation for `wordpress_and_activecampaign_registration` form.
* Disabling HubSpot integrations. Specifically, we're removing the HubSpot tracking code from the footer by not including `lib/fns/hubspot.php` in `nccagent-extras.php`.

### 4.0.0 ###
* Adding creation of ActiveCampaign contacts from new user registration.
* Adding `ncc_get_state_name()` for retrieving a state name given a two letter state abbreviation.

### 3.9.0 ###
* Updating Product Finder product listings by changing "State Availability > Current as of" to "Last review date:" and removing the "Current as of" date for "Plan Information".

### 3.8.4 ###
* Disabling `gform_confirmation` hook because having this hook enabled was preventing a page redirect confirmation for the "Online Contracting" form.

### 3.8.3 ###
* Checking HubSpot chat status before attempting to open.
* Adding Marketer Chat to Product Finder.
* Adding Marketer Chat to `[marketer_contact_details]` shortcode.

### 3.8.2 ###
* Updating "Chat with..." link in `[mymarketer]` to use the Marketer's first name.

### 3.8.1 ###
* Updating "Schedule a Meeting" link in `[mymarketer]` to include the Marketer's first name.

### 3.8.0 ###
* Initial HubSpot chat setup for Marketers.
* Adding "Chat Now" link to the `[mymarketer]` shortcode used on the Agent Dashboard.

### 3.7.7 ###
* Adding `.BambooHR-ATS-Jobs-List` to exclusions in CSS for list item bullets.

### 3.7.6 ###
* Adding `.BambooHR-ATS-Department-List` to exclusions in CSS for list item bullets.

### 3.7.5 ###
* Adding trailing slashes to URLs.

### 3.7.4 ###
* Setting GravityForms submit buttons to `#fe0000`.

### 3.7.3 ###
* Disabling "Close All" when opening accordion sections in `accordion.js`.

### 3.7.2 ###
* Updating `ncc_is_elementor()` to `ncc_is_elementor_edit_mode()`. The function was previously reporting whether or not the current page was built with Elementor. What we want to know is if we are editing inside Elementor, and now the renamed function correctly reports that information.

### 3.7.1 ###
* Adding Elementor display for `[contracting_confirmation]` shortcode.
* Adding `ncc_is_elementor()` to `lib/fns/utilities.php` for detecting if the view is from inside the Elementor Editor.

### 3.7.0 ###
* Adding `[contracting_confirmation]` shortcode which displays the "Thank You" page information found in "NCC Settings &gt; Online Contracting".

### 3.6.4 ###
* CSS Tweak: Removing NCC Dark Grey for heading color inside Beamer widget.

### 3.6.3 ###
* Adding Product Finder Help Graphic option to "Product Finder" page settings.
* Bugfix: Product Finder Table was loading results when no fitlers were selected. To fix I added an additional condition for resetting the table when there is a saved state with empty values. Previously, I was only checking for `null` values.

### 3.6.2 ###
* Adding Issue Dates to Carrier &gt; Product pages.

### 3.6.1 ###
* Updating invalid Carrier &gt; Products redirect to a 301 Redirect.
* Disabling `.fa-home` in `cache-busters.css`.

### 3.6.0 ###
* Saving Product Finder DataTables data in a single location so that the saved state for the finder carries over between different pages (e.g. settings on `/product-finder/` will carry over to `/plans/`).

### 3.5.3 ###
* Adding a 302 Redirect for invalid Carrier &gt; Products so that we'll redirect back to the parent Carrier.

### 3.5.2 ###
* Loading `.fa-bars` and `.fa-home` via inline CSS to avoid "FLUC" ("Flash of Unstyled Content") due to Font Awesome being cached and deferred.

### 3.5.1 ###
* Adjusting `plan-by-state-selector.js` to dynamically reference the proper browser Local Storage path based on the page slug for the Product Selector.

### 3.5.0 ###
* Rewriting Product Import/Export for better CSV handling.

### 3.4.1 ###
* Only load `products-import-export.js` on "Carriers &gt; Import/Export" and "Products &gt; Import/Export".

### 3.4.0 ###
* GUI for Import/Export of Carrier &gt; Products.
* Adding required `permission_callback` for `nccagent/v1/products` and `nccagent/v1/verifyAccount`.

### 3.3.0 ###
* Adding "Plan Year" to Carrier > Products.
  * "Plan Year" displays in the Product Finder, individual Product pages, and Carrier > Product pages.
  * "Plan Year" is included in `wp ncc carriers export` and `wp ncc carriers export`.

### 3.2.0 ###
* Adding proper user registration validation via the `elementor_pro/forms/validation` hook.
  - Checking user email for duplicates.
  - Checking NPN/username for duplicates.

### 3.1.6 ###
* Updating "Kit Request" text in product finder and on Carrier pages.
* Adding background-color and padding to `.kit-details` in Kit Request section.

### 3.1.5 ###
* Updating VPN Link field to a text field to support VPN paths rather than full URLs.

### 3.1.4 ###
* Leaving `<script/>` tags in message returned by `get_online_contracting_message()` to allow for page redirects by GravityForms JS.

### 3.1.3 ###
* Checking for existence of user with NPN when running `register_user_and_send_lead_to_hubspot()`.

### 3.1.2 ###
* Setting new users' username to their NPN when running `register_user_and_send_lead_to_hubspot()`.

### 3.1.1 ###
* Only retrieve `post_status=publish` pages for Supercrumbs dropdowns.

### 3.1.0 ###
* Correcting typo in supercrumbs: "Anncillaries" to "Ancillaries".

### 3.0.9 ###
* SEO Title filtering for Carriers, Products, and Carrier > Products.

### 3.0.8 ###
* Updating supercrumbs to work with Product Finder located at `/product-finder/` URL.

### 3.0.7 ###
* BUGFIX: Inlining `accordion.js` was causing issues with NitroPack, so I've enqueued it via `wp_enqueue_scripts()`.

### 3.0.6 ###
* Adding Issue Ages to Carrier Product listings.
* Adding Handlebars processing with `ncc_hbs_render_template()`.

### 3.0.5 ###
* Updating Product Kit request text to support `typeof selectedState === 'undefined'` in the Product Finder.
* Removing chevron bullets from lists in HubSpot forms.

### 3.0.4 ###
* Disabling password strength meter on `wp-login.php`.

### 3.0.3 ###
* Removing "Black Book: Agent Resources" from drop down links in the "Black Book" section.

### 3.0.2 ###
* Moving "Prescription Drug Plan" up with the Medicare products in the Product Finder drop down.

### 3.0.1 ###
* Updating Carrier Document Library alert to reference the "Back" button.

### 3.0.0 ###
* Correct capitlization for "CSO" and "CSI" in Carrier Documents Library breadcrumbs.
* Adding directory and file icons to the Carrier Documents Library.

### 2.9.9 ###
* Updating the CSG API route to authenticate users via an all numeric value for "Email" which means we are authenticating against the user's NPN or by an email address which means we are authenticating against the user's `user_email` field in their profile.

### 2.9.8 ###
* Product page edits:
  * Trailing slashes for Product + Carrier links in `acf_get_product_carriers()`.
  * Responsive CSS for Carrier listing in `acf_get_product_carriers()`.
  * Updated error message + admin email for invalid Product + Carrier URLs.

### 2.9.7 ###
* BUGFIX: Correcting the URL generated for "View this plan information as a web page." on non-accordion Carrier > Product listings.

### 2.9.6 ###
* BUGFIX: String concatenation issue was preventing Plan Information from displaying on Medicare related products.
* Adding `ncc_is_medicare_product()` for checking product titles to see if they're medicare products.

### 2.9.5 ###
* Disabling shortlinks.
* Restricting Marketer Thumbnail size limit to Product Finder (this fixes the small marketer photos on the Staff and Marketer pages).

### 2.9.4 ###
* Bugfix: Checking for an object in `acf_get_carrier_products()` as deleted Products remove the object reference from the ACF field.

### 2.9.3 ###
* Removing left/right border from Plan Information on Carrier > Product pages without accordions.
* Add trailing slashes to links.

### 2.9.2 ###
* Adjusting line-height for "Products by State" button in Carrier Docs Library.
* Not capitalizing "by" in Carrier Docs Library breadcrumbs.

### 2.9.1 ###
* Updating `.elementor-alert .elementor-alert-description` font-size to 16px.
* Carrier Document Library edits:
  * Using a slash as the seperator for the breadcrumbs.
  * Increasing font-size to 18px.
  * Moving the "Back" button above the breadcrumbs and changing the button text from "To Parent Directory" to "Back".
  * Special styling for "Products by State" Carriers.

### 2.9.0 ###
* Showing extra dropdown items in Supercrumbs on "All Carriers" and "All Products" pages.

### 2.8.9 ###
* Updating Product Finder Marketer Display to work on wide screen layout.
* Fixing warnings related to unset variables.

### 2.8.8 ###
* Adding a note in Product Finder which targets IE as follows:
  * We use a media query to target IE 10 and 11 ([link](https://www.mediacurrent.com/blog/pro-tip-how-write-conditional-css-ie10-and-11/)).
  * For <= IE 9.X.X, we use IE Conditional tags.
* Adding `css_classes` attribute to `ncc_get_alert()`.

### 2.8.7 ###
* Setting Product Finder page length to 40.
* Specifying supercrumb dropdown pages on the Carriers & Products page.

### 2.8.6 ###
* Matching Product Finder pagination style to the rest of the site.

### 2.8.5 ###
* Adding `<meta name="format-detection" content="telephone=no">` to `<head>` to prevent automatic `tel:` linking on mobile.

### 2.8.4 ###
* Product Finder edits:
  * Centering pagination below Product Finder.
  * Setting page length to 30.

### 2.8.3 ###
* Updating Carrier > Product accordions.

### 2.8.2 ###
* Updating Product Finder accordions.

### 2.8.1 ###
* Updating link text for "${carrier} Agent Support Black Book" and "${carrier} Document Library" in Quick Links.
* Restoring "Big CTAs" to Carrier > Product pages.
* Setting headings to h3 in Product Finder.

### 2.8.0 ###
* Adding a special cases switch to breadcrumbs which handles the Products and Carriers pages.

### 2.7.9 ###
* Linking Marketer photo.
* Restoring My Marketer display.

### 2.7.8 ###
* Tweaking the text linking to Marketer pages (i.e. "See the states `{firstname}` serves and testimonials from agents.").
* Standardizing the product layout between the Product Finder and Product pages.

### 2.7.7 ###
* Bug fix: Properly escaping variable for `${selectedState}` in Product Finder.

### 2.7.6 ###
* Product Finder updates:
  * Bug fix: Fixing "State Availability" heading when a state isn't selected.
  * Italizing note for Medicare products and adding note to PDP products.
  * Adding a "Quick Links" header above the quick links.

### 2.7.5 ###
* Adding image bullets to blog post content.

### 2.7.4 ###
* HOTFIX: Updating the `li` background-image CSS specificity so that it doesn't break the layout of the Mega Menu.

### 2.7.3 ###
* Updating `li` background-image to apply to `ul > li` inside `.elementor-widget-container`.

### 2.7.2 ###
* Updating marketer testimonials heading from "Testimonials" to "Testimonials from Agents".
* Adding "See {team_member's} testimonials and states served." link to marketers listing.

### 2.7.1 ###
* Only show Carrier Docs Directory alert to logged in users.
* Adding `[marketer_states]` shortcode.
* Adding "Log Out" to "My Dashboard" link.
* Updating `[supercrumbs]` for paginated archives.

### 2.7.0 ###
* Product Finder tweaks:
  * Adding review dates.
  * Adding "Issue ages..." to top of product description.
  * Adding "Some information may vary by state..." note to Medicare products.
  * Adding extra links below Product description.
  * Styling adjustments to the Product Kit Request CTA.

### 2.6.2 ###
* Clearing a saved Carrier when using the "Plan by State" drop down.

### 2.6.1 ###
* Moving Standard Carriers above SureLC Carriers message on thank you page for signing up for Online Contracting.
* Updating the navigation note above the Carrier Documents Library.
* Updating `lib/fns/utilities.php::ncc_get_alert()` to support an empty `title`.

### 2.6.0 ###
* Updating Carrier Documents Library to "smooth scroll" to the top of the page every time we load a new directory listing.

### 2.5.9 ###
* Adding Company field to user registration and displaying Company field on user profile.

### 2.5.8 ###
* Adjusting Carrier > Products code to remove unpublished product prior to building the HTML output.

### 2.5.7 ###
* Turning off "email change" notification when importing users via `wp ncc users import`.

### 2.5.6 ###
* Adding switch to disable "Delete User" message upon user deletion.
* Checking to see if a variable exists before trying to add associated user meta field to DB.

### 2.5.5 ###
* HOTFIX: Checking for a variable before doing a comparison inside `custom_id_attribute()` function for WP Nav Menu.

### 2.5.4 ###
* Updating user import:
  * Setting NPN as username.
  * Saving all additional user meta provided in CSV.

### 2.5.3 ###
* Adding HTML id attribute to `mobile-mega-menu-extra-links` menu items.

### 2.5.2 ###
* Adding `Alt_Product_Name_2` option to `wp ncc carriers import/export`.

### 2.5.1 ###
* Adjusting Top Bar "Log In or Register" link to point to `/login-register/`.

### 2.5.0 ###
* Removing unpublished Carriers from `acf_get_product_carriers()`.

### 2.4.9 ###
* Dequeuing Font Awesome icons from Elementor.

### 2.4.8 ###
* Adding `[custom_loop_post_excerpt]` for use inside an Ele Custom Skin.

### 2.4.7 ###
* Updating bottom margin for `ul > li` elements.
* Adding `Desc_Review_Date` and `States_Review_Date` to Carrier &gt; Products.
* Removing `Review_Date` from Carrier &gt; Products.

### 2.4.6 ###
* Adding "Select a state..." default option for Plan by State selector when user is not logged in.
* Adjusting blog breadcrumbs to include a link to the "blog" and not show the title of the post when viewing a post single.

### 2.4.5 ###
* Removing Contract Online CTA from the top of Carrier pages.
* Adding Quick Links to main Carrier page.

### 2.4.4 ###
* Reworking layout of Product Finder results to function like an accordion (titles only).

### 2.4.3 ###
* Note to <= IE 11 users about the Product Finder not working in their browser.

### 2.4.2 ###
* Adjusting bullets for lists such that they don't scale down for each subsequent list item.

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
