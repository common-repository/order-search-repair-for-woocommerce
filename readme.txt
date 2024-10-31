=== Order Search Repair for WooCommerce ===
Contributors: indextwo
Donate link: https://www.paypal.me/indextwo
Tags: woocommerce, orders, search, repair, database, update
Requires at least: 4.7
Tested up to: 4.8.3
Stable tag: 0.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Order Search Repair for WooCommerce scans all of the orders in your WooCommerce store and updates them to make your orders fully searchable again.

== Description ==

Following the major v3 update to WooCommerce, searching for older orders by postcode, full name or address no longer works - the search process was optimised, but the changes were not applied retroactively to existing orders. Order Search Repair for WooCommerce scans all of the orders in your WooCommerce store and updates them to make your orders fully searchable again.

Features include:

* Ajaxified processing to update the orders in manageable chunks
* Customisable limit for the number of orders to process at a time so that even low-powered servers can process without issue, and beefier setups can get it done in less time
* Options to immediately update the orders live, or output an SQL query so that you can do it in the database yourself
* Customisable offset so that you can skip any orders you don't need (SQL export option only)
* Ability to cancel the process at any point if you need to, with a live tally of the number of orders already processed if you need to come back to it later.

== Installation ==

Upload the Order Search Repair plugin to your website & activate it as normal. Then click on *Order Search Repair* under the WooCommerce menu. 

Make sure to back up your database before making any significant changes!

== Frequently Asked Questions ==

= How many orders can be processed at once? =

Order Search Repair runs its operations in manageable chunks which it will process automatically, one after the other. rather than trying to process all orders in one go. The recommended maximum limit is 500 orders per chunk.

= How long does it take? =

That entirely depends on the number of orders in your store. Order Search Repair has been tested with on stores ranging from 400 orders (which took about 10 seconds); to over 50,000 orders (which took about 25 minutes). On average, each batch request (varying from 100 to 500 orders per chunk) can take between 15 and 30 seconds.

= Can I just leave it running in the background? =

You can absolutely leave it running and go make yourself a coffee, as long as you don't exit the page. Doing so will effectively cancel the update process, and you won't know how many orders have been processed.

= Will it time out? =

Depending on the specification of your server and installation, you may find the process takes a lot longer than anticipated. In that event, there is a 60-second timeout when waiting for the server to respond, after which it will show an alert. If this is happening to you, try lowering the limit for the number of requests.

= Can I see how many orders will be affected? =

As of version `0.1.2`, you sure can. Click on the *Get total number of unindexed orders* button and it will retrieve the total number of orders that either have their search index missing, or the value is empty. Note that nothing will be done at this point - it's just to give you an idea of how many orders need updating.

= What's the difference between updating orders and Output as SQL? =

Selecting *Update all order indexes* will instantly modify the meta for every processed order in that chunk, updating your site as it goes. *Output as SQL* does _not_ make any changes to your site; it simply outputs the raw SQL queries so that you can run the update directly on the database yourself.

= Why would I want to output as SQL rather than update the live orders? =

Order Search Repair was originally coded for a large-scale site with several thousand orders. Running the process on the live site would have meant potentially eating up resources and writing to the database while live transactions were going on. So, the option to simply output the SQL was added so that it didn't do anything except read from the database while it was running. Then, the database could be updated later, and it would only take a few seconds to parse even several thousand SQL queries.

= Should I back up my database? =

Absolutely 100% definitely yes. There is virtually no risk of anything drastically bad going wrong, as even in the worst case, the tool will only affect the `wp_postmeta` table. However, as with any plugin that makes significant changes to your database, you should _always_ back up as a precaution before doing anything.

= Why can't I set an offset for updating live orders? =

When updating live orders (rather than exporting SQL queries), the plugin is updating the post's meta on the fly; as such, the query that searches for older orders in the first place doesn't pick up orders that have been updated. So, if you were to include an offset, it would actually skip over orders that need to be updated. Hence, no offset.

== Screenshots ==

== Changelog ==

= 0.1.3 =
*Release Date - 12 December 2018*

* BUG FIX: Typo led to billing & shipping indexes both being set as billing
* Checked compatibility up to Wordpress 4.9.8 and WooCommerce 3.5.2

= 0.1.2 =
*Release Date - 05 November 2017*

* Order Search Repair now also checks for empty search indexes (as well as ones where the index doesn't exist)
* Added feature to retrieve the total number of orders with missing or blank search indexes
* Added edge-case handler where actual billing or shipping details might be blank (for some unknown reason) and populate the search index with `{Empty}` string to avoid infinite loop
* Checked compatibility up to Wordpress 4.8.3 and WooCommerce 3.2.2

= 0.1.1 =
*Release Date - 22 September 2017*

* Checked compatibility up to Wordpress 4.8.2 and WooCommerce 3.1.2

= 0.1 =
*Release Date - 11 May 2017*

* Initial release

== Upgrade Notice ==
