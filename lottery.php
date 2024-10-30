<?php
/*
Plugin Name: Lottery
Plugin URI: http://www.seodenver.com/lottery/
Description: Feature daily lottery results on your website from every state in the USA.
Author: Katz Web Services, Inc.
Version: 1.0
Author URI: http://www.katzwebservices.com
*/

/*
Copyright 2010 Katz Web Services, Inc.  (email: info@katzwebservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
	add_action('init', 'lottery_initialize',1);
	add_action( 'widgets_init', 'lottery_load_widget' );
	add_shortcode('Lottery', 'lottery_show_results');
	add_shortcode('lottery', 'lottery_show_results');

	function lottery_initialize() {
		define('LOTTERY_PATH', plugin_dir_url(__FILE__));
		add_action('admin_print_styles-widgets.php', 'lottery_add_stylesheet');
		if(is_admin()) {
			
	    } else {
			add_action('wp_print_styles', 'lottery_add_stylesheet');
		}
		return;
	}
	
	function lottery_add_stylesheet() {
		wp_register_style('lottery-style', LOTTERY_PATH.'lottery.css');
		wp_enqueue_style('lottery-style');
		return;
	}
	
	function lottery_load_widget() {
		register_widget( 'LotteryWidget' );
	}
		
	class LotteryWidget extends WP_Widget {
	 	
	 	var $url = 'http://loref.com/StateData/%%CODE%%/%%CODE%%_L_js.js'; //'http://www.lotteryfeed.com/xml_b/all_results.xml';
	 	
	    function LotteryWidget() {
	    	$widget_options = array('description'=>'Add lottery results to your website!', 'classname' => 'lottery');
	    	$control_options = array('width'=>600); // 600 px wide please
	        parent::WP_Widget(false, $name = 'Lottery Results', $widget_options, $control_options);
	    }
	 
	 
	    function widget($args, $instance) {      	
	    	$args = wp_parse_args( $args, $instance );
	    	$output = '';
	        extract( $args );
	        if(!isset($style)) {
	        	echo '<!-- The Lottery widget has not yet been saved. -->'; 
	        	return;
	        }
	        if(!isset($games)) {
	        	echo '<!-- No games have been selected in the Lottery widget. -->'; 
	        	return;
	        }
	        if(isset($hide)) { 
	        	echo '<!-- The Lottery widget is hidden; "Do not display widget in sidebar" is checked. -->'; 
	        	return; 
	       	}
	        
	        $args['before'] = $before_widget;
			$args['after_title'] = $after_title;
			$args['before_title'] = $before_title;
			$args['after'] = $after_widget;
						
			$output .= "\n\t".$this->lottery_results($args)."\n\t";
					
			$output = apply_filters('lottery_widget', apply_filters('lottery_widget_'.$this->number, $output));
			        
		    echo $output;
	    }
	 	
	 	function r($content, $echo=true) {
	 			$output = '<pre>';
	 			$output .= print_r($content, true);
	 			$output .= '</pre>';
	 		if($echo) {	echo $output; }
	 		else { return $output; }
	 	}
	 	
	 	function load_game_dropdown($instance = array()) {
	 		$results = $this->load_results($instance['state_code']);
	 		if(empty($results)) { return false; }
	 		
	 		$out = '<ul>';
	 		foreach($results as $key => $g) {
				$checked = isset($instance['games'][$key]) ? ' checked="checked"' : '';
		 			$out .= '<li><label for="'.$this->get_field_id('games_'.$instance['state_code'].'_'.$key).'"><input type="checkbox" val="'.$key.'" class="checkbox" id="'.$this->get_field_id('games_'.$instance['state_code'].'_'.$key).'" name="'.$this->get_field_name_array('games', $key).'"'.$checked.'/> '.esc_attr($g['name']).' <small>(ID <span  class="code">'.$key.'</span>)</small></label></li>';
	 		}
	 		$out .= '
	 			</ul>';
	 		
	 		return $out;
	 	}
	 	
	 	function get_field_name_array($base, $field_name) {
	 		return 'widget-' . $this->id_base . '[' . $this->number . ']['.$base.'][' . $field_name . ']';
	 	}
	 	
	 	function load_game($state_id, $game_id) {
	 		$results = $this->load_results();
	 		
	 		if(isset($results[$state_id]['games'][$game_id])) {
	 			return $results[$state_id]['games'][$game_id];
	 		}
	 		return false;
	 	}
	 	
	 	function state_name($state_id) {
	 		$results = $this->load_results();
	 		if(isset($results[$state_id]['state_name'])) {
	 			return $results[$state_id]['state_name'];
	 		}
	 		return false;
	 	}
	 	
	 	function game_results($state_code, $game_id = '', $results = false, $style = 'balls') {
	 		$out = '';
	 		if(!isset($state_code)) { return '<!-- The state has not been chosen. -->'; }
	 		
	 		// If we want to pass an array of states using CSV, we can do that.
	 		// Example: game_results('VT,CO,NC')
	 		$state_code = explode(',',$state_code);
	 		if(sizeof($state_code) == 1) {
	 			$state_code = $state_code[0]; 
	 		} else { // Process each state 
	 			foreach($state_code as $sc) { $out .= $this->game_results($sc); }
	 			return $out;
	 		}
	 		
	 		if(!$results) { $results = $this->load_results($state_code); }
	 		
	 		// If you want all games in the state
	 		if($game_id === '' || (empty($game_id) && $game_id != 0)) {
	 			$games = array();
	 			foreach($results as $key => $result) {
	 				$games[] = $key;
	 			}
	 		} else {
	 			// Multiple games requires only one state.
	 			// Example: game_results('CO', '3870,2176,73,560,1356')
	 			$games = explode(',',$game_id);
	 		}
	 		
	 		if(sizeof($games) == 1) { 
	 			$game_id = $games[0]; 
	 		} else { 
	 			// Process each game id
	 			foreach($games as $gi) {
	 				$out .= $this->game_results($state_code, $gi, $results, $style);
	 			}
	 			return $out;
	 		}
	 		
	 			if(!isset($results["{$game_id}"])) { return '<!-- The game code does not exist -->'; }
	 			
		 		extract($results["$game_id"]);
		 		
		 		$name = lottery_state_lookup($state_code).' '.$name;
		 		$name = apply_filters("lottery_game_name", apply_filters("lottery_game_name_{$state_code}", apply_filters("lottery_game_name_{$state_code}_{$game_id}", $name)));
		 		$date = apply_filters("lottery_game_date", apply_filters("lottery_game_date_{$state_code}", apply_filters("lottery_game_date_{$state_code}_{$game_id}", $draw_date))); // date('d M', strtotime($draw_date));
		 		$caption = apply_filters("lottery_game_caption", apply_filters("lottery_game_caption_{$state_code}", apply_filters("lottery_game_caption_{$state_code}_{$game_id}", $name.' - '.$date)));
		 		
		 		$tableClass = isset($racetime) ? '' : ' lottery_'.$style;
		 		
		 		if(!isset($bonus)) { $last = ' lastrow'; } else { $last = ''; }
		 		
		 		// Get # of cols
		 		if(is_array($numbers)) { $tableSize = sizeof($numbers); }
		 		if(isset($bonus)) { $tableSize++; }
		 		if(isset($multiplier)) { $tableSize++; }
		 		if(isset($wildcard)) { $tableSize++; }
		 		if(isset($racetime)) { $tableSize++; }
		 		if(isset($twobytwo)) { $tableSize++; $tableSize++; }
		 		
		 		$tableClass .= ' lottery_cols_'.$tableSize;
		 		
		 		$out = "
		 		<table cellspacing='0' class='lottery_results $tableClass'>
		 			<caption>$caption</caption>
		 			<thead>
						<tr>";
				if(isset($racetime)) { $out .= "
							<th class='numbers'>1st</th><th class='numbers'>2nd</th><th class='numbers'>3rd</th><th class='racetime'>Race Time</th>";
				} elseif(isset($twobytwo) && is_array($twobytwo)) {  $out .= "
					 		<th colspan='".sizeof($numbers)."' class='numbers{$last}'>Red Balls</th>
					 		<th colspan='".sizeof($numbers)."' class='numbers{$last}'>White Balls</th>
					 		";
						
				} else {  $out .= "
					 		<th colspan='".sizeof($numbers)."' class='numbers{$last}'>Numbers</th>";
				}
				if(isset($bonus)) {
					if(!isset($multiplier)) { $last = ' lastrow'; } else { $last = ''; }
					if(preg_match('/Powerball/ism', $name)) {
						$bonusName = 'Powerball';
					} else if(preg_match('/Hot Ball/ism', $name)) {
						$bonusName = 'Hot Ball';
					} else {
						$bonusName = 'Bonus';
					}
					$bonusName = apply_filters("lottery_game_bonus", apply_filters("lottery_game_bonus_{$state_code}", apply_filters("lottery_game_bonus_{$state_code}_{$game_id}", $bonusName)));
					$out .= "
							<th class='bonus{$last}'>$bonusName</th>";
				}
				if(isset($multiplier)) {
					if(preg_match('/Powerball/ism', $name)) {
						$multiplierName = 'PowerPlay';
					} else {
						$multiplierName = '<acronym title="Multiplier">X</acronym>';
					}
					$multiplierName = apply_filters("lottery_game_mulitplier", apply_filters("lottery_game_multiplier_{$state_code}", apply_filters("lottery_game_multiplier_{$state_code}_{$game_id}", $multiplierName)));
					$out .= "
							<th class='multiplier lastrow'>$multiplierName</th>";
				}
				if(isset($wildcard)) {
					$wildcardName = apply_filters("lottery_game_wildcard", apply_filters("lottery_game_wildcard_{$state_code}", apply_filters("lottery_game_wildcard_{$state_code}_{$game_id}", 'Wild Card')));
					$out .= "
							<th class='wildcard lastrow'>$wildcardName</th>";
				}
					 		
		 		$out .= "
		 				</tr>
		 			</thead>
				<tbody>
		 			<tr>";
		 		if(is_array($numbers)) {
		 		foreach($numbers as $number) {
		 			$numLength = ' class="length'.strlen($number).'"';
		 			$bonusClass = isset($twobytwo) ? ' bonus' : '';
		 			$out .= "<td class='lottery_number lottery_number_$number$bonusClass'><span$numLength>$number</span></td>";
		 		}
		 		}
		 		if(isset($bonus)) {
		 			$numLength = ' class="length'.strlen($bonus).'"';
		 			$out .= "<td class='lottery_number lottery_number_$bonus bonus'><span$numLength>$bonus</span></td>";
		 		}
		 		
		 		if(isset($multiplier)) {
		 			$numLength = ' class="length'.strlen($multiplier).'"';
		 			$out .= "<td class='lottery_number lottery_number_$multiplier multiplier'><span$numLength>$multiplier</span></td>";
		 		}
		 		
		 		if(isset($racetime)) {
		 			$out .= "<td class='lottery_racetime racetime'><span>$racetime</span></td>";
		 		}
		 		if(isset($wildcard)) {
					$out .= "<td class='lottery_racetime wildcard'>".$this->process_wildcard($wildcard)."</td>";
				}
				if(isset($twobytwo) && is_array($twobytwo)) { 
					$numLength = ' class="length'.strlen($twobytwo[0]).'"';
					$out .= "<td class='lottery_number lottery_number_2by2 lottery_number_{$twobytwo[0]}'><span$numLength>".$twobytwo[0]."</span></td>";
					$numLength = ' class="length'.strlen($twobytwo[1]).'"';
					$out .= "<td class='lottery_number lottery_number_2by2 lottery_number_{$twobytwo[1]}'><span$numLength>".$twobytwo[1]."</span></td>";
				}
		 		
		 		$out .= "
		 			</tr>
		 		</table>";
		 		
		 		return $out;	 		
	 	}
	 	
	 	function process_wildcard($wildcard = false) {
	 		switch($wildcard['suit']) {
	 			case 'C': $suit = 'Clubs'; break;
	 			case 'S': $suit = 'Spades'; break;
	 			case 'D': $suit = 'Diamonds'; break;
	 			case 'H': $suit = 'Hearts'; break;
	 		};
	 		
	 		switch($wildcard['card']) {
	 			case 'J': $card = 'Jack'; break;
	 			case 'Q': $card = 'Queen'; break;
	 			case 'K': $card = 'King'; break;
	 			case 'A': $card = 'Ace'; break;
	 		};
	 	
	 		$result = '<span title="'.$card.' of '.$suit.'" class="'.$wildcard['card'].$wildcard['suit'].'">'.$wildcard['card'].$wildcard['suit'].'</span>';
	 		return apply_filters("lottery_game_wildcard_result", $result);
	 	
	 	}
	 	
	 	function lottery_results($args) {
			$gameString = $out = $hide = '';
			extract( $args );
			if(empty($state_code)) { return '<!-- Lottery: The state has not been chosen. -->'; }
			
			/**
			 * Begin HTML output of widget
			 */
			$out .= (isset($before)) ? $before : '';
			
			if(isset($title) && !empty($title) && strtolower($title) != "false") {
				$out .= (isset($before_title, $after_title)) ? $before_title : '<h2 class="lottery_title">';
				$out .= (isset($title)) ? $title : '';
				$out .= (isset($after_title, $before_title)) ? $after_title : '</h2>';
			}
			
			if(isset($games) && is_array($games)) {
				foreach($games as $key => $game) {
					$gameString .= $key.',';
				}
				$gameString = substr($gameString, 0, -1);
			} elseif(isset($games)) { 
				$gameString = $games; 
			}
			
			// Either all games game_results($results) or specific game_results($results, 'game ID')
			$out .= $this->game_results($state_code, $gameString, false, $style);

			if(!empty($lottery_link)) {  // Please help support the plugin author by leaving this code intact :-)
				$out .= $this->lottery_thank_you_link();
			}
			
			$out .= (isset($after)) ? $after : '';
			
			$out = apply_filters('lottery_results', $out); // Since 1.1
	 		return $out;
	 	}
	 	
	 	function lottery_thank_you_link() {
	 		mt_srand(crc32($_SERVER['REQUEST_URI'])); // Keep links the same on the same page
	 		$urls = array(array('url' => 'http://wordpress.org/extend/plugins/lottery/'), array('url' => 'http://www.seodenver.com/lottery/'));
			$url = $urls[mt_rand(0, count($urls)-1)];
			$link = $url['url'];
			$nofollow = (isset($url['nofollow']) && $url['nofollow'] == 1) ? ' rel="nofollow"' : '';
	 		
	 		$links = array(
				'Results by <a href="'.$link.'" title="Online lottery results"'.$nofollow.'>Lottery Results</a>',
				'<a href="'.$link.'" title="Lottery numbers plugin"'.$nofollow.'>Lottery</a> for WordPress',
				'Numbers from the <a href="'.$link.'" title="Lottery drawing results"'.$nofollow.'>Lottery Plugin</a>',
			);
			
	 		$link = '<p class="lottery_link">'.trim($links[mt_rand(0, count($links)-1)]).'</p>';
	 		
	 		mt_srand(); // Make it random again.

	 		return apply_filters('lottery_thank_you_link', $link);
	 	}
	 		 	
	    function update($new_instance, $old_instance) {
	    	// If they change the state, the games should not be checked.
			if($new_instance['state_code'] != $old_instance['state_code']) {
				$new_instance['games'] = array();
			}
			// Clear out the saved data for other states.
			// May cause refresh of data if there are multiple lottery widgets
			// with different states, but keeps DB clear
			foreach(lottery_state_list() as $stateCode => $state) {
				if($stateCode != $new_instance['state_code']) {
					delete_transient('lottery_results_'.$stateCode);
				}
			}
			
			return $new_instance;
	    }
	 	
	 	function mmisset($instance, $name) {
	 		return isset($instance[$name]) ? $instance[$name] : '';
	 	}
	 	
		function process_results($games, $stateCode = '') {
			// Take the javascript result and parse it
			preg_match_all("/\['(.*?)','(.*?)','(.*?)','(.*?)','(.*?)','(.*?)'\]/ism", $games, $matches);
			$games = array();
			foreach($matches[1] as $key=>$val) {
				
				// We want the name of the game even if formatted like this: Pick 3 DAY*Pick 3*Pick_3
				$val = explode('*', $val);
				$games[$key]['name'] = esc_attr($val[0]);
			}
			foreach($matches[2] as $key=>$val) {
				$games[$key]['draw_date'] = esc_attr($val);
			}
			
			foreach($matches[3] as $key=>$val) {
				$numbers = array();
				
				// First we check if it's hyphen separated, like this: 09-25-30-31-46
				// if not, then it's straight numbers
				if(!strpos($val, '-')) {
					$i = 0;
					// If it's numbers, we make the string an array, for consistent formatting
					while($i < strlen($val)) { 
						$numbers[] = (int)$val[$i];
						$i++;
					}
					
					$games[$key]['numbers'] = $numbers;
				} else {
					preg_match_all('/([0-9-]+)(?:\*\*)?(?!([0-9]{1,2}-[0-9]{1,2}))([0-9]{1,2})?(?:x)?(?:\*\*)?(.*)?/ism', $val, $numbermatches);
				
					if(!isset($numbermatches[1])) { return false;}
					
					$numbers = $numbermatches[1][0];
					$numbers = explode('-', $numbers); //string separator, string string [, int limit])
					$games[$key]['numbers'] = $numbers;
					
					if(isset($numbermatches[2][0]) && $numbermatches[2][0] !== '' ||
					   isset($numbermatches[3][0]) && $numbermatches[3][0] !== '' && empty($numbermatches[2][0])
					) {
						if(isset($numbermatches[3][0]) && empty($numbermatches[2][0])) {
							$games[$key]['bonus'] = (int)$numbermatches[3][0];
							unset($numbermatches[3][0]);
						} else {
							$games[$key]['bonus'] = (int)$numbermatches[2][0];
						}
					}
					
					if(isset($numbermatches[3][0]) && $numbermatches[3][0] !== '') {
						$games[$key]['multiplier'] = (int)$numbermatches[3][0];
					}
					
					if(isset($numbermatches[4][0]) && $numbermatches[4][0] !== '') {
						$extraMatch = trim($numbermatches[4][0]);
						if(preg_match('/([0-9]{1,2}\-[0-9]{1,2})/ism', $extraMatch)) {
							unset($games[$key]['bonus']);
							$extraMatch = explode('-', $extraMatch);
							$games[$key]['twobytwo'] = $extraMatch;
						} elseif(!is_numeric(substr($extraMatch, 0, 1))) {
							// This is Wild Card
							$card = substr($extraMatch, 0, 1);
							$suit = substr($extraMatch, 1, 2);
							$games[$key]['wildcard'] = array('card' => $card, 'suit'=>$suit);
						} elseif(preg_match('/\s?([0-9]{1}\;[0-9]{2}\.[0-9]{2})/ism', $extraMatch)) {
							// California-only Racetime
							$games[$key]['racetime'] = trim(str_replace(';', ':', $extraMatch));
						} else {
							$games[$key]['multiplier'] = (int)$numbermatches[4][0];
						}
					}
				}
			}
			
			foreach($matches[4] as $key=>$val) {
				$games[$key]['next_date'] = esc_attr($val);
			}
			
			foreach($matches[5] as $key=>$val) {
				$games[$key]['started'] = esc_attr($val);
			}
			
			foreach($matches[6] as $key=>$val) {
				$games[$key]['drawing_id'] = (int)$val;
				$games[$key]['state'] = array('code' => esc_attr($stateCode), 'name' => esc_attr(lottery_state_lookup($stateCode)));
			}
			
			if(!empty($games)) {
				return $games;
			} 
			return false;
		}
		
		
	 	function load_results($stateCode = 'CA', $force = false) {
	 		$results = false;
	 		if(isset($_REQUEST['cache'])) { $force = true; }
	 		
	 		if($force) { 
			 	delete_transient('lottery_results_'.$stateCode);
		 	} else {
		 		$results = maybe_unserialize(get_transient('lottery_results_'.$stateCode));
		 		if($results) { return $results; }
		    }
		    
		    if(!$results) {
	 			$results = wp_remote_retrieve_body(wp_remote_get(str_replace('%%CODE%%', $stateCode, $this->url)));
	 			$results = $this->process_results($results, $stateCode);
	    	}
		    
		    // Save for six hours; some games are multiple times per day
		    set_transient('lottery_results_'.$stateCode, maybe_serialize($results), 60*60*6);
	    	
	    	return $results;
	    	
	 	}
	 	
	    function form($instance) {
	    	if(!function_exists('wp_remote_get')) {
	    		echo '<h2>Sorry, Charlie.</h2><p class="description">This plugin does not support your version of WordPress. Please upgrade.</p>';
	    		return;
	    	}
	    	$error = '';
	    	
	    	$title = $this->mmisset($instance, 'title');
	        $formcode = $this->mmisset($instance, 'formcode');
	        $inputsize = $this->mmisset($instance, 'inputsize');
	        ?>
	        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	        <?php 
	           $states = lottery_state_list();
	           $out = '
	           <div class="alignleft" style="width:250px;">
	           	<h3><label for="'.$this->get_field_id('state_code').'">1. Select a State</label></h3>
	           	<p class="description">First, select a state for the lottery results.'; if(empty($instance['state_code'])) { $out .= '<strong>Then save the widget.</strong>'; } $out .='</p>
	           	<select name="'.$this->get_field_name('state_code').'" id="'.$this->get_field_id('state_code').'">';
	           $out .= '<option value="">Select a State</option>';
	           foreach($states as $key => $state) {
	           		$out .= $this->lottery_create_option($state, $key, $instance);
	           }
	           $out .= '</select>
	           </div>';
	           echo $out;
	           $out = '';
	           if(!empty($instance['state_code'])) {
		           $out .= '<div class="alignleft" style="padding-left:20px;">
		 			<h3>2. Select Games</h3>
		 			<p class="description">For which games you would like to display results?</p>
		 			';
		           $out .= $this->load_game_dropdown($instance);
		           $out .= '</div>';
		       }
		       echo $out;
	           $out = '';
	           if(!empty($instance['state_code'])) {
	           		$ballsChecked = $plainChecked = $numbersChecked = '';
	           		
	           		if(empty($instance['style']) || $instance['style'] == 'balls') { $ballsChecked = ' checked="checked"';}
	           		if(isset($instance['style'])) {
	           			if($instance['style'] == 'plain') { $plainChecked = ' checked="checked"';}
	           			if($instance['style'] == 'numbers') { $numbersChecked = ' checked="checked"';}
	           		}
		           $out .= '<div class="clear"></div>';		
		           $out .= '<h3>3. Select a Game Style</h3>';
		           $out .= '
		           		<div class="alignleft" style="width:28%; padding-right:4%;">
			           		<label for="'.$this->get_field_id('style_balls').'">
			           		<p><input class="radio" id="'.$this->get_field_id('style_balls').'" name="'.$this->get_field_name('style').'" type="radio" value="balls"'.$ballsChecked.' /> <span>Balls</span></p>
			           		<table class="lottery_results lottery_balls" style="width:50%">
								<tr>
									<td class="lottery_number lottery_number_5"><span class="length1">5</span></td>
									<td class="lottery_number lottery_number_46 bonus"><span class="length2">46</span></td>
									<td class="lottery_number lottery_number_12 multiplier"><span class="length2">12</span></td>
								</tr>
							</table>
							</label>
						</div>
						<div class="alignleft" style="width:28%; padding-right:4%;">
							<label for="'.$this->get_field_id('style_numbers').'">
							<p><input class="radio" id="'.$this->get_field_id('style_numbers').'" name="'.$this->get_field_name('style').'" type="radio" value="numbers"'.$numbersChecked.' /> <span>Numbers</span></p>
							<table class="lottery_results lottery_numbers" style="width:50%">
								<tr>
									<td class="lottery_number lottery_number_5"><span class="length1">5</span></td>
									<td class="lottery_number lottery_number_46 bonus"><span class="length2">46</span></td>
									<td class="lottery_number lottery_number_12 multiplier"><span class="length2">12</span></td>
								</tr>
							</table>
							</label>
						</div>
						<div class="alignleft" style="width:28%; padding-right:1%;">
							<label for="'.$this->get_field_id('style_plain').'">
							<p><input class="radio" id="'.$this->get_field_id('style_plain').'" name="'.$this->get_field_name('style').'" type="radio" value="plain"'.$plainChecked.' /> <span>Plain Text</span></p>
							<table class="lottery_results" style="width:50%">
								<tr>
									<td class="lottery_number lottery_number_5"><span class="length1">5</span></td>
									<td class="lottery_number lottery_number_46 bonus"><span class="length2">46</span></td>
									<td class="lottery_number lottery_number_12 multiplier"><span class="length2">12</span></td>
								</tr>
							</table>
							</label>
						</div>
					
					';
	          $out .= '<div class="clear"></div>';
	          echo $out;
	         ?>
	         <h3><label for="<?php echo $this->get_field_id('hide'); ?>">4. Hide Widget <small>(Optional)</small></label></h3>
	         <?php
	          
	        $gameCSV = array();
	        if(isset($instance['games'])) { foreach($instance['games'] as $key => $game) { $gameCSV[] = $key; }}
	        echo '<div class="wrap">'. $this->lottery_get_checkbox($this->mmisset($instance,'hide'), $this->get_field_id('hide'),$this->get_field_name('hide'), '<p style="display:inline;">Do not display widget in sidebar.</p>');
	        ?>
	        <p><span class="howto">You can embed the form in post or page content by using the following code:</span> <code>[lottery<?php echo !empty($title) ? ' title="'.$title.'"' : ''; echo !empty($instance['state_code']) ? ' state="'.$instance['state_code'].'"' : ''; echo isset($instance['style']) ? ' style="'.$instance['style'].'"' : ''; echo !empty($gameCSV) ? ' games="'.implode(',', $gameCSV).'"' : ''; echo isset($instance['lottery_link']) ? '' : ' link=false'; ?>]</code>
	        </p>
	        </div>
	        <h3 style="padding-top:.75em;"><label for="<?php echo $this->get_field_id('lottery_link'); ?>">5. Show Thanks <small>(Appreciated)</small></label></h3>
			  <?php 
			  echo $this->make_notice_box($this->lottery_get_checkbox($this->mmisset($instance,'lottery_link'), $this->get_field_id('lottery_link'),$this->get_field_name('lottery_link'), '<p style="display:inline;">&nbsp;<strong>Checking this box improves your odds of winning the lottery! <img src="'.get_bloginfo('url').'/'.WPINC.'/images/smilies/icon_cool.gif" width="15" height="15" alt="Thanks a million!" /></strong></p><p style="padding-top:.5em;line-height:1.4;">Checking this box adds a link to the plugin page, letting users know how you got this sweet widget. <span class="description">If you like this plugin, please check this box.</span></p>'), 'update');
		}
	          echo '<div class="clear"></div>';
	          
	}

	function make_notice_box($content, $type="error") {
        $output = '';
        if($type!='error') { $output .= '<div style="background-color: rgb(255, 255, 224);border-color: rgb(230, 219, 85);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        } else {
            $output .= '<div style="background-color: rgb(255, 235, 232);border-color: rgb(204, 0, 0);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        }
        $output .= '<p style="line-height: 1; margin: 0.5em 0px; padding: 2px;">'.$content.'</div>';
        return($output);
    }
    	
	function lottery_create_option($name, $value, $instance) {
		$selected = (isset($instance['state_code']) && $instance['state_code'] == $value) ? ' selected="selected"': '';
		return "<option value='$value'$selected>$name</option>\n";
	}
	     	  
	function lottery_get_checkbox($setting = '', $fieldid = '', $fieldname='', $title = '', $value = 'yes', $checked = false, $disabled = false) {
		$checkbox = '
			<input type="checkbox" id="'.$fieldid.'" name="'.$fieldname.'" value="'.$value.'"';
				if($checked || !empty($setting)) { $checkbox .= ' checked="checked"'; }
				if($disabled)  { $checkbox .= ' disabled="disabled"';}
				$checkbox .= ' class="checkbox" />
			<label for="'.$fieldid.'">'.__($title).'</label>';
	    return $checkbox;
	}	
	
} // End Class

///
//  SHORTCODE
///
function lottery_show_results($atts) {
	global $post;
	$Lottery = new LotteryWidget();
	
	// According to WP rules, links may not be added upon activation without user choosing to show them.
	// by adding the shortcode, you are taking action. If you want to remove the link, add link=false or link=0
	$atts = shortcode_atts(array('title' => NULL, 'style' => 'balls','link' => true, 'games' => NULL, 'state_code' => '', 'state'=> ''), $atts);
	
	if(isset($atts['state'])) {
		$atts['state_code'] = $atts['state'];
		unset($atts['state']);
	}
	if($atts['link'] == '0' || $atts['link'] === '' || strtolower($atts['link']) == 'false') {
		$atts['link'] = false;
	}
	$atts['lottery_link'] = $atts['link'];
	unset($atts['link']);
	
	return $Lottery->lottery_results($atts);
}


function lottery_state_lookup($stateCode) {
	$states = lottery_state_list();
	return isset($states[$stateCode]) ? $states[$stateCode] : false;
}
		
function lottery_state_list() {
	return array(
		'AZ'=>"Arizona",  
		'AR'=>"Arkansas",  
		'CA'=>"California",  
		'CO'=>"Colorado",  
		'CT'=>"Connecticut",  
		'DE'=>"Delaware",  
		'DC'=>"District Of Columbia",  
		'FL'=>"Florida",  
		'GA'=>"Georgia",  
		'ID'=>"Idaho",  
		'IL'=>"Illinois",  
		'IN'=>"Indiana",  
		'IA'=>"Iowa",  
		'KS'=>"Kansas",  
		'KY'=>"Kentucky",  
		'LA'=>"Louisiana",  
		'ME'=>"Maine",  
		'MD'=>"Maryland",  
		'MA'=>"Massachusetts",  
		'MI'=>"Michigan",  
		'MN'=>"Minnesota",  
		'MO'=>"Missouri",  
		'MT'=>"Montana",
		'NE'=>"Nebraska",
		'NH'=>"New Hampshire",
		'NJ'=>"New Jersey",
		'NM'=>"New Mexico",
		'NY'=>"New York",
		'NC'=>"North Carolina",
		'ND'=>"North Dakota",
		'OH'=>"Ohio",  
		'OK'=>"Oklahoma",  
		'OR'=>"Oregon",  
		'PA'=>"Pennsylvania",  
		'RI'=>"Rhode Island",  
		'SC'=>"South Carolina",  
		'SD'=>"South Dakota",
		'TN'=>"Tennessee",  
		'TX'=>"Texas",  
		'VT'=>"Vermont",  
		'VA'=>"Virginia",  
		'WA'=>"Washington",  
		'WV'=>"West Virginia",  
		'WI'=>"Wisconsin"
	);
}
	
?>
