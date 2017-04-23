=== Skroutz.gr & Bestprice.gr XML Feed for Woocommerce ===
Plugin URI: http://emspace.gr/
Description: XML feed creator for Skroutz.gr & Bestprice.gr for Woocoommerce
Version: 1.0.11
Author: emspace.gr
Author URI: http://emspace.gr/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Contributors: mpbm23,emspacegr
Tags: ecommerce, e-commerce,  wordpress ecommerce, xml, feed

Requires at least: 4.0
Tested up to: 4.5.1
Stable tag: 1.0.10

Create Skroutz.gr and Bestprice.gr XML feeds for Woocommerce

== Description ==

With this plugin you can create XML feeds for Skroutz.gr and Bestprice.gr.


== Frequently Asked Questions ==

= When in Stock Availability =
Dropdown  option "When in Stock Availability"   with options will show for all in Stock products 
"Available", "1 to 3 days", "4 to 7 days", "7+ days" as avalability

= If a Product is out of Stock =
Dropdown  option "If a Product is out of Stock"  with options will 
"Include as out of Stock or Upon Request" or "Exclude from feed"

= Add mpn/isbn to product =

To add mpn/isbn to the product just fill in the SKU field of woocommerce


= Add color =

To add the color to a product , in order to be printed on the XML feed add an attribute with Slug "color" , Type "Select" and Name of your choice

= Add manufacturer =

To add the manufacturer to a product , in order to be printed on the XML feed add an attribute with Slug "manufacturer" , Type "Select" and Name of your choice

OR

Brands plugins are supported to be shown as manuftacturer.


= Add sizes =

To add the size to a product , in order to be printed on the XML feed add an attribute with Slug "size" , Type "Select" and Name of your choice
Then created variable product with this attribute.

If you have stock management enabled on variations, sizes with stock lower or equal to 0 will not be shown on the feed

= Remove item from feed =

If you want to remove items from the feed toy can add as special field in the product edit area "onfeed" with value "no"

= Backorder =
If you have enabled backorder and set to notify ,the product will be shown as Upon order and not in stock. 

If you have selected Yes the product will be shows as available and in stock. 

If you have selected no to backorder the product will be not available. 


== Changelog ==
= Version: 1.0.11 =
Added color as attribute

= Version: 1.0.10 =
Update for skroutz availabilities and fixes

= Version: 1.0.7 =
Performance improvement by selecting active feeds (Both, Skroutz only, Bestprice only).
Added Features and features selections for Bestprice feed

= Version: 1.0.6 =
Added dropdown  option "When in Stock Availability"   with options
"Available", "1 to 3 days", "4 to 7 days", "7+ days"

Added dropdown  option "If a Product is out of Stock"  with options
"Include as out of Stock or Upon Request", "Exclude from feed"

= Version: 1.0.5 =
Fixed error on products with no attached or featured image.

= Version: 1.0.4 =
Category path bug fix.

= Version: 1.0.3 =
Added mpn as special field, added feautured image 2nd try and it now shows only available sizes for variable product , loop optimization

= Version: 1.0.2 =
Create full path categories for skroutz and bestprice

= Version: 1.0.1 =
added feature to skip product.
Set Special field on Product  "onfeed" with value "no" 

= Version: 1.0.0 =
Initial Release



