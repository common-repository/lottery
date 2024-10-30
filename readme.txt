=== Lottery Results ===
Tags: lottery, lotto, lottery results, lottery winners, powerball, mega millions, online lottery, online lottery results, lottery drawing, lottery numbers, megabucks, hot lotto, wild card, wildcard, pick 3, pick 4, 2By2, Win 4, sweet millions, cash 5, cash derby, adopt-me
Requires at least: 2.8
Tested up to: 4.0
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Lottery%20Results&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Feature daily lottery results on your website.

== Description ==
<h4>The official <a href="http://www.seodenver.com/lottery/">Lottery Results</a> page is the place for support & additional information</h4>

<h3>Show lottery results on your website.</h3>

<h4>View results from all 43 states with lotteries</h4>

* Choose which games you want to display
* Choose from different lotto results layouts
* Embed results in your content using the `[lottery]` shortcode.

The widget automatically gets updated results every 6 hours, then they are stored in your website for very fast load times.

<h3>Get results the most popular lottery games:</h3>

* Powerball
* Mega Millions
* Megabucks
* Hot Lotto
* Wild Card
* Win for Life
* Pick 3 / Pick 4
* 2By2
* Win 4
* Sweet Millions
* Cash 5

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the widgets page (Appearance > Widgets)
1. Drag the Lottery Results widget to a sidebar.
1. Choose a state from the dropdown, save the widget
1. Check boxes next to each game you would like to appear in the widget
1. Choose the style of the lotto results
1. Save the widget again
1. If you want the form to be embedded in content, instead of shown in the sidebar, check the checkbox for "Do not display widget in sidebar", then follow the instructions for inserting the shortcode into your content where you would like the form to be displayed.

<h3>Using the lottery shortcode</h3>
<code>[lottery state="CO" games="1,2,3" style="numbers" title="Colorado Lottery" link=true]</code>

* `state` - The two letter state abbreviation
* `games` - If you don't enter this, all games in the state will be displayed. If you configure the widget, you'll see "ID = #" next to each game. These numbers should be used. You can enter one game (`games="1"`) or multiple, separated by a comma (as shown above).
* `style` - Three options exist: `balls` (yellow, red and green lottery balls with text numbers), `numbers` (circles with a gray border and images of numbers), or `plain` (simple text numbers; able to be styled using your own CSS). Default: `balls`
* `link` - Show a link to plugin page. To turn off, add `link="false"` or `link="0"` to the `lottery` shortcode. Default: true


== Screenshots ==

1. How the widget appears in the Widgets panel 
2. How the signup form appears in the sidebar of a site using the twentyten theme

== Frequently Asked Questions == 

= My state is missing! =
Unfortunately Alabama, Alaska, Hawaii, Mississippi, Nevada, Utah & Wyoming have no lottery system. If you're in this state, contact your law maker and get a lottery going! It's a great way to raise money for education, conservation, and other great causes.

= What is the plugin license? =

* This plugin is released under a GPL license.

= What filters exist? =
The following filters have been added to allow for modification of code using `apply_filters()`:

* `lottery_widget`, `lottery_widget_{$widget_number}`
* `lottery_results`
* `lottery_game_name`
* `lottery_game_date`
* `lottery_game_caption`, `lottery_game_caption_{$state_code}`, `lottery_game_caption_{$state_code}_{$game_id}`
* `lottery_game_bonus`, `lottery_game_bonus_{$state_code}`, `lottery_game_bonus_{$state_code}_{$game_id}`
* `lottery_game_multiplier`, `lottery_game_multiplier_{$state_code}`, `lottery_game_multiplier_{$state_code}_{$game_id}`
* `lottery_game_wildcard`, `lottery_game_wildcard_{$state_code}`, `lottery_game_wildcard_{$state_code}_{$game_id}`, `lottery_game_wildcard_result`
* `lottery_thank_you_link`

= How do I use the `apply_filters()` functionality? =
If you want to change some code in the widget, you can use the WordPress `add_filter()` function to achieve this.

You can add code to your theme's `functions.php` file that will modify the widget output. Here's an example:
<pre>
function my_example_function($widget) { 
	// The $widget variable is the output of the widget
	// This will replace 'this word' with 'that word' in the widget output.
	$widget = str_replace('this word', 'that word', $widget);
	// Make sure to return the $widget variable, or it won't work!
	return $widget;
}
add_filter('lottery_widget', 'my_example_function');
</pre>

= How do I remove the titles of the games (the captions)? =

If you want to remove game captions from the widget, add the following to your active theme's functions.php file:

<pre>
add_filter('lottery_game_caption', 'lottery_return_no_caption');
function lottery_return_no_caption($caption = null) {
	return '';
}
</pre>

== Changelog ==

= 1.0 =
* Initial plugin release.

== Upgrade Notice ==

= 1.0 = 
* Bingo!