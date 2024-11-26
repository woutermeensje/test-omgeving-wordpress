=== PRO Plugin ===
Contributors: wpgetapi
Tags: api, external api, rest-api, connect, custom-endpoints, endpoint, rest
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 6.7
Version: 3.5.10

A Premium WordPress plugin to extend the free WPGetAPI plugin.


== Changelog ==

= 3.5.10 - 30/Oct/2024 =

* COMPATIBILITY: Replace deprecated jQuery 3 code with new code.
* FEATURE: Integrate with Ninja Forms
* FIX: Displaying the Ninja Table shortcode on a page caused a 500 error.
* FIX: Issue when passing data in any variable with parentheses, it removes spaces within parentheses
* FIX: Ninja Table column settings were removed when displayed on the page.

= 3.5.9 - 28/Aug/2024 =

* FEATURE: Integrate with Ultimate Member Forms.

= 3.5.8 - 08/Jul/2024 =

* SECURITY: wpDataTable Integration: inject nonce to wpDataTable JSON file name.
* FEATURE: Integrate Ninja Tables.
* FIX: Multiple sentences in translation function.
* FIX: Resolved the warning that occurs when adding a date token without specifying the format.
* FIX: The caching logic was giving previous API requests response data when the query string are different in the API request.
* FIX: WPForms integration is not working when the display setting is set to redirect.
* TWEAK: Add WPGetAPI required plugin header.

= 3.5.7 - 01/Apr/2024 =

* FEATURE: Integrate with WSForms.
* FIX: wpDataTables data can't refresh data from the endpoint when two wpDataTables shortcodes are rendered on the same page. 
* FIX: wpDataCharts shortcode render didn't refresh data from the datatable when two wpDataCharts shortcodes are rendered on the same page.
* FIX: Resolved the warning in PHP 8.2: "Creation of dynamic property WpGetApi_Extras_Extend::$output is deprecated".
* FIX: Resolved the warning in PHP 8.2: "PHP Deprecated: Constant FILTER_SANITIZE_STRING is deprecated"


= 3.5.6 (2023-12-19) =
- New - add new filter wpgetapi_modify_line_items_wrapper
- Fix - if Gravity Forms Action has a validation error in the form, do not send to API, abort and send standard validation error instead.

= 3.5.5 (2023-11-22) =
- New - add new filter wpgetapi_query_variables_explode_separator
- Update - modify WooCommerce action when using line_items

= 3.5.4 (2023-11-10) =
- Update - modify AJAX button so output is independent to each button when using multiple buttons.
- Update - add date_created to Gravity Forms Action Log when using validation.

= 3.5.3 (2023-10-31) =
- New - add new Root field for wpDataTables to be able to set the default root of a table.
- Update - modify getting attribute names within WooCommerce actions. Thanks @Samir.

= 3.5.2 (2023-10-24) =
- Update - rewrite body tokens when Raw encoding is set.

= 3.5.1 (2023-10-19) =
- Update - fix issue with wpDataTables not creating new table on some servers setups.
- Update - add check for attributes in WooCommerce products within actions.

= 3.5.0 (2023-10-09) =
- Update - major rewrite of wpDataTables integration.
- Update - add attributes to WooCommerce products within actions.

= 3.4.4 (2023-10-09) =
- New - Add filter to set a default root within wpDataTables - 'wpgetapi_pro_wpdatatables_table_root'.
- Fix - WPForms and wpDataTables integration was not updating the table on form submission.
- Fix - issue with calling is_plugin_active() function on CF7 action.

= 3.4.3 (2023-10-02) =
- Fix - fix "Deprecated: Required parameter $response_code follows optional parameter $action"

= 3.4.2 (2023-09-29) =
- Fix - issue when looping through items within WooCommerce line_items token.

= 3.4.1 (2023-09-28) =
- Fix - error within body tokens when sending json.

= 3.4.0 (2023-09-28) =
- New - New validation and display options in Contact Form 7 & Gravity Forms Actions.
- Update - Big UI update.
- Fix - Modify how looping line items works within Tokens.

= 3.3.6 (2023-09-21) =
- Update - add response code to Actions. This make it possible to redirect after an Action based on the response code using wpgetapi_after_$action 

= 3.3.5 (2023-09-20) =
- Update - modify Actions for WPForms, Contact Form 7 and Gravity Forms to return confirmations if there are errors.

= 3.3.4 (2023-09-19) =
- Update - modify Actions for WPForms, Contact Form 7 and Gravity Forms to allow displaying the results.

= 3.3.3 (2023-09-19) =
- New - add IP address to CF7 action args.
- Fix - allow actions to passed when within a float(), integer(), or boolean() tag in admin.

= 3.3.2 (2023-09-15) =
- Fix - stripslashes within the Actions log for values sent to API.

= 3.3.1 (2023-09-14) =
- Fix - update the 'pre_user_registered' Action to check for validation errors first.

= 3.3.0 (2023-09-14) =
- New - add new filters and actions that run before and after the endpoint call within the Actions.
- New - add new 'pre_user_registered' Action. This runs after a user attempts registration but before they are actually registered.

= 3.2.7 (2023-09-12) =
- Update - modify (line_items) token to accept XML.
- Update - modify WooCommerce line items to include extra custom fields added by external plugins.

= 3.2.6 (2023-09-11) =
- Update - add order products meta fields to actions. These are custom fields that might be created with extra plugins.

= 3.2.5 (2023-09-08) =
- Fix - Raw data in body was not getting put back into raw format when passing tokens.

= 3.2.4 (2023-08-29) =
- New - Add full support for order meta_data and line item meta_data.

= 3.2.3 (2023-08-28) =
- Fix - Issue with adding an extra wc- to before WooCommerce statuses when calling multiple endpoints on the same action.

= 3.2.2 (2023-08-25) =
- New - Add order type to WooCommerce actions.

= 3.2.1 (2023-08-25) =
- New - Add ability to link wpDataTables to confirmation within Formidable Forms.

= 3.2.0 (2023-08-22) =
- New - Add loopable line items.
- Update - add new shortcode attribute img_link to allow linking an image when using format='html'.
- Update - Add line items, shipping, fees and coupons items to actions.

= 3.1.6 (2023-08-15) =
- New - JSON encode array values if passed through Actions.
- New - Add ability to link wpDataTables to confirmation within WPForms.

= 3.1.5 (2023-08-14) =
- Fix - bug with adding line items in WooCommerce Actions.

= 3.1.4 (2023-08-14) =
- New - add Fluent Forms Action.

= 3.1.3 (2023-08-08) =
- Update - update tokens to make sure there are no curly braces within token. If there are, skip it. This is for GraphQL compatability.

= 3.1.2 (2023-07-28) =
- New - add Elementor Forms Action.

= 3.1.1 (2023-07-27) =
- Fix - make extra check for string when doing strpos checks in hmac_signatures.

= 3.1.0 (2023-07-21) =
- New - add wpDataTables built-in integration.

= 3.0.0 (2023-07-17) =
- New - add Actions to allow calling an endpoint on certain actions.

= 2.6.2 (2023-07-03) =
- Update - update WooCommerce tokens to get product data within orders including meta values.

= 2.6.1 (2023-06-13) =
- Update - allow img_key attribute to work when outputting a single key via nested data.

= 2.6.0 (2023-06-12) =
- Fix - error with pp() function. Function removed.

= 2.5.9 (2023-06-12) =
- Update - add new shortcode attributes for AJAX - button_id for adding an ID to the button and ajax_output for allowing to change the output div to whatever you like. This needs to be used like ajax_output="#my_div_with_id" or ajax_output="#my_div_with_class"

= 2.5.8 (2023-06-09) =
- Update - add compatibility for WPForms Name fields, using first and last names

= 2.5.7 (2023-05-30) =
- Update - for caching, only cache if successfull response code of 200.

= 2.5.6 (2023-05-26) =
- Update - add compatability for files in a Gravity Forms multi-part form.

= 2.5.5 (2023-05-22) =
- New - compatability for tokens and WPForms file upload. USes (system:post:wpforms_57895_8|name) format - where wpforms_FORMID_INPUTID|returnkey.

= 2.5.4 (2023-05-17) =
- Update - add endpoint_variables to caching and update the way the hash is created for all variables.

= 2.5.3 (2023-05-12) =
- New - add filter to Paid Memberships Pro integration. wpgetapi_pmp_saved_data filter allows filtering of the saved data from the API on checkout.

= 2.5.2 (2023-05-12) =
- New - add WPForms date fields and array fields such as checkboxes.
- Update - update FILE token for Gravity Forms. Now using the default GF code to use hashed URL.
- Update - update Paid Memberships Pro integration. Now using PMP action hook 'pmpro_after_checkout'. Saves the API response in usermeta field '_wpgetapi_pmp_checkout'

= 2.5.1 (2023-05-05) =
- New - add new option for Paid Memberships Pro plugin using wpgetapi_on : pmp_membership. Gets data using POST tokens.
- New - add compatibility for POST tokens within Elementor forms.
- Fix - update FILE token for Gravity Forms so that it checks for renamed files as GF rename duplicate files with a number.

= 2.5.0 (2023-05-04) =
- New - add new FILE token for Gravity Forms single file upload.

= 2.4.9 (2023-05-04) =
- Fix - change colons in nodes to an underscore for XML.

= 2.4.8 (2023-04-25) =
- New - add body_variables for shortcode.

= 2.4.7 (2023-04-24) =
- Fix - issue when using multiple ajax buttons.

= 2.4.6 (2023-04-21) =
- Fix - modify keys attribute for shortcode to now return null if not found. Other improvements with this function.
- Fix - allow post id to pass through ajax button.

= 2.4.5 (2023-04-20) =
- New - Add compatibility for Formidable Forms.

= 2.4.4 (2023-04-19) =
- New - Add ability to set a delay when chaining endpoints.

= 2.4.3 (2023-04-18) =
- New - add body variables into caching.
- Fix - issue with $filter variable not being set within post tokens.

= 2.4.2 (2023-04-18) =
- New - add ability to work with Lifter LMS orders.

= 2.4.1 (2023-03-17) =
- New - allow raw data in body post fields to pass through unaffected.

= 2.4.0 (2023-03-16) =
- New - Add chaining of API calls using the shortcode and the new 'chain' token. 

= 2.3.5 (2023-03-01) =
- New - Add image shortcode attributes.

= 2.3.4 (2023-02-21) =
- New - Add the option to call an API on user registration.
- Enhancement - Add disabled attribute to AJAX button on click.

= 2.3.3 (2023-02-20) =
- New - Add spinner and hide button shortcode attributes.

= 2.3.2 (2023-02-17) =
- New - Add ability to recognise WPForms inputs with tokens and add filter to allow shortcode in confirmation messages.
- New - Add hmac_signature. Follows this format base64_encode(hash_hmac('sha256', $request, $key, true)).
- Enhancement - 'keys' attribute can now include multiple items and multiple nesting like so keys="{content},{author},{tags|0}"

= 2.3.1 (2023-02-16) =
- Fix - Issue with outputting duplicate data when using multiple endpoints and both set to format='html'.

= 2.3.0 (2023-02-14) =
- New - AJAX button integration. Click a button, call an endpoint.
- New - Ability to add links to any item within the HTML output.

= 2.2.0 (2023-02-06) =
- New - Gutenberg block integration.

= 2.1.3 (2023-02-03) =
- New - add ability of to send XML formatted data in Body POST fields.

= 2.1.2 (2023-01-31) =
- Fix - add new function check to fix error with tokens when Woocommerce not installed.
- Fix - issue with keys only going 9 levels deep.

= 2.1.1 (2023-01-30) =
- New - Woocommerce order token.
- New - add new filter 'wpgetapi_query_variables_delimiter' to change the comma delimiter to anything else.

= 2.1.0 (2023-01-20) =
- New - add a custom Contact Form 7 form-tag. This allows sending form data to your API.

= 2.0.0 (2023-01-13) =
- New - major update to licenses.

= 1.6.0 (2023-01-10) =
- Enhancement - better wrapping and class names when using the html format attribute in shortcode.

= 1.5.0 (2022-11-29) =
- New - introduce tokens. System, date, user and post tokens.

= 1.4.8 (2022-11-16) =
- Fix - make sure query_variables is not empty if trying to cache

= 1.4.7 (2022-08-19) =
- Fix - bypass XML when set to debug

= 1.4.6 (2022-08-02) =
- Enhancement - add caching for dynamic query_variables
- Fix - issue with looping update calls back to wpgetapi.com

= 1.4.5 (2022-07-06) =
- Enhancement - add new option within 'format' to allow HTML formatting of output

= 1.4.4 (2022-05-24) =
- Enhancement - add new attribute 'format' within shortcode that allows formatting of a number 

= 1.4.3 (2022-05-13) =
- Enhancement - add option to base64 encode in the headers

= 1.4.2 (2022-05-12) =
- Fix - set cache option to true to allow caching of the update call

= 1.4.1 (2022-03-18) =
- Enhancement - add the option to use body variables

= 1.4.0 (2022-03-17) =
- Enhancement - add the option to use header variables

= 1.3.1 (2022-02-17) =
- Fix - cache time defaulting to 30 instead of 0

= 1.3.0 (2022-02-08) =
- Enhancement - add one-click update from within WordPress admin
- Fix - breaking change to follow proper naming conventions within main plugin

= 1.2.0 (2021-12-14) =
- Enhancement - add the option to use query_variables within shortcodes

= 1.1.0 (2021-11-09) =
- Enhancement - add the option to use endpoint_variables

= 1.0.1 (2021-11-02) =
- Bug fixes

= 1.0.0 (2021-10-27) =
- Initial Release

== Upgrade Notice ==
* 3.5.10: Achieved compatibility with jQuery 3 version, added a feature like integration with the Ninja Forms, and fixed various issues. A recommended update for all.