=== Blocks for Discogs Users ===
Plugin URI: https://www.greyforest.digital/plugins/blocks-for-discogs-users
Author: Greyforest
Author URI: https://www.greyforest.digital
Contributors: GreyforestDigital
Donate link: https://www.greyforest.digital/donate
Tags: discogs, api, collection, inventory, vinyl
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0.2
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg blocks for Discogs Users to embed collections, wantlists, profiles, collection values, and custom lists with tons of customization options.

== Description ==

Gutenberg blocks for Discogs Users to embed collections, wantlists, profiles, collection values, and custom lists with tons of customization options.

Take the music collection that you've spent a lifetime building and share it on your own website with total control over the look.

Powered by the Discogs API, this plugin requires a valid Discogs account and personal access token to use.

Options for front-end sorting, filtering, and pagination to easily navigate large sets of titles.

Display data from any user whose profile is set to public - not just your own.

## USER COLLECTION
* Display titles from a user's Collection as grid or list
	* Choose default desktop column count
	* Built-in responsive layout adjustments for smaller screens
* Control which fields are displayed
    * Artist, format, genres, image, labels, title, and year
* Set default sorting, sort order, and per page options
* Allow users to sort on front end or use default settings
* Fetches 100 releases maximum per page with front-end pagination features
* Works for any username with public collection

## USER WANTLIST
* Display titles from a user's Wantlist as grid or list
	* Choose default desktop column count
	* Built-in responsive layout adjustments for smaller screens
* Control which fields are displayed
    * Artist, format, genres, image, labels, title, and year
* Set default sorting, sort order, and per page options
* Allow users to sort on front end or use default settings
* Fetches 100 releases maximum per page with front-end pagination features
* Works for any username with public wantlist

## USER COLLECTION VALUE
* Display a user's Collection Value in a simple 4-column table
* Control which fields are displayed
	* Collection count, maximum value, median value, minimum value
* Only works for username connected to personal access token

## USER PROFILE
* Display a visually-pleasing User Profile section featuring your Discogs profile data
* Header section with banner graphic or background color
* Statistics from Discogs database entries
	* Releases contributed, rated, in collection, for sale, and in wantlist
* Control which fields are displayed
    * Avatar, banner, collection value, profile link, location, registration date, profile text, and all statistics mentioned above
* Visual customization of colors and borders
* Easy to style with CSS or inherits text colors from parent elements
* Works for any public username (collection value requires matching username/token)

## USER LIST
* Display titles from a user's custom List as grid or list
	* Choose default desktop column count
	* Built-in responsive layout adjustments for smaller screens
* Control which fields are displayed
    * List Name and Description
	* Release Image, title, and comment
* Built-in one click helper to retrieve all list IDs from user
* Works for any username with public list

## USER LISTS
* Display a listing of a user's lists (listception??)
* Control which fields are displayed
	* List image, title, description, and link to list on Discogs
* Works for any username with public lists

## SETTINGS PAGE
* Fields for providing username and personal access token to blocks globally
* Visual API rate limit interface for keeping track of current rate limits
* Transient cache interface for clearing outdated data
* Debug log for tracking every API call to Discogs

## CACHING
One of the most important features in this plugin is the ability to cache Discogs API calls in temporary transients. This has several benefits:
* Reduces the amount of times your token needs to "ping" Discogs, preventing API overages or your site being blocked for overuse
* Drastically reduces the page load time after data has been cached

Caching works for all blocks and types of API calls, WITH THE EXCEPTION of when users are allowed to sort/filter on the front-end (user collections and wantlists). Allowing sort parameters turns caching off, and will require an API call on every page load.

## COMPATIBILITY
* This plugin requires a minimum PHP version of 8.0
* This plugin requires Gutenberg Editor to be activated
* This plugin requires an active Discogs account and personal access token

### NOTICE
*Your use of this plugin as an individual and a site owner is governed by the terms outlined on Discogs' ["Terms of Use"](https://support.discogs.com/hc/en-us/articles/360009334593-API-Terms-of-Use).*

*This plugin is not affiliated with, endorsed by, or built in collaboration with Discogs.


== Screenshots ==

1. Display a grid or list of titles from a user’s default collection, with options for displaying any/all fields (image, title, artist, year, label, genres, styles)
1. Display a grid or list of titles from a user’s wantlist, with options for displaying any/all fields (image, title, artist, year, label, genres, styles)
3. Display user collection value with choice of release count, minimum, median, and maximum value in responsive columned layout
4. Customizable, realtime display of your profile information, including choice of stats from your account in responsive columned layout
5. Display a simple listing of a user’s custom lists, with options for displaying any/all fields (image, title, dates, description)
6. Display a grid or list of titles from a user’s custom list, with options for choosing which fields to display


== Support ==

If you have questions, need help, or just want to share feedback, I recommend using the Support tab on the WordPress.org plugin page. It's the best way to reach out quickly and keep everything organized. I appreciate positive reviews if you liked this plugin or found it useful.

For general inquiries, you can also reach out at [https://www.greyforest.digital/contact](https://www.greyforest.digital/contact).

== Changelog ==

= 1.0.2 -> July 10th, 2026 =
* Compatibility check for WordPress 7.0.0 + 7.0.1

= 1.0.1 -> March 19th, 2026 =
* Fixed admin-ajax.php unauthorized user bug caused by current_user_can() function placement in logging admin_init() action

= 1.0.0 -> January 16th, 2026 =
* Initial commit to repository