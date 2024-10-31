<?php
/*
Plugin Name: Order Search Repair for WooCommerce
Description: Update older WooCommerce orders with new searchable indexes so that they can easily be found again.
Version: 0.1.2
Author: Lawrie Malen
Author URI: http://www.verynewmedia.com/
Copyright: Lawrie Malen
Text Domain: vnmordersearch
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WC requires at least: 2.5.0
WC tested up to: 3.5.2
*/

///
//	Create version option on activation
///

if (!defined('VNMWOOORDERSEARCH_VERSION')) {
	define('VNMWOOORDERSEARCH_VERSION', '0.1.1');
}

function vnmWooOrderSearch_install() {
	update_option('vnmWooOrderSearch_version', VNMWOOORDERSEARCH_VERSION);
}

register_activation_hook(__FILE__, 'vnmWooOrderSearch_install');

///
//	Kill version option on activation
///

function vnmWooOrderSearch_deactivate() {
	delete_option('vnmWooOrderSearch_version');
}

register_deactivation_hook(__FILE__, 'vnmWooOrderSearch_deactivate');

///
//	Update plugin
///

function vnmWooOrderSearch_versionCheck() {
	if (get_option('vnmWooOrderSearch_version') != VNMWOOORDERSEARCH_VERSION) {
		vnmWooOrderSearch_install();
	}
}

add_action('plugins_loaded', 'vnmWooOrderSearch_versionCheck');

///
//	Add link to WC Settings
///

function vnmWooOrderSearch_pluginSettingsLinks($links) {
	$vnmSettingsLink = '<a href="admin.php?page=vnmWooOrderSearch">' . __('Order Search Repair', 'vnmordersearch') . '</a>';
	
	array_push($links, $vnmSettingsLink);
	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'vnmWooOrderSearch_pluginSettingsLinks');

///
//	Translations
///

function vnmWooOrderSearch_load_textdomain() {
	load_plugin_textdomain('vnmordersearch', false, basename(dirname(__FILE__)) . '/languages');
}

add_action('plugins_loaded', 'vnmWooOrderSearch_load_textdomain');

///
//	Enqueue scripts
///

function vnmWooOrderSearch_loadScripts() {
	
	//	JS
	
	$scriptName = 'orders.js';
	$scriptPath = plugin_dir_path(__FILE__) . '/includes/';
	$scriptURI = plugins_url('/includes/', __FILE__);
	$scriptModifiedVersion = filemtime($scriptPath . $scriptName);
	
	wp_register_script('vnm-ordersearch-script', $scriptURI . $scriptName, 'jquery', $scriptModifiedVersion, true);
	
	//	CSS
	
	$cssFile = 'vnm.css';
	$cssDependency = array();
	$cssModifiedVersion = filemtime($scriptPath . '/' . $cssFile);
	wp_enqueue_style('vnm-ordersearch-style', $scriptURI . '/' . $cssFile, $cssDependency, $cssModifiedVersion);
}

add_action('admin_enqueue_scripts', 'vnmWooOrderSearch_loadScripts', 20);

///
//	Add WC admin menu option
///

function vnmWooOrderSearch_orderSearchMenuItem() {
	add_submenu_page('woocommerce', __('Order Search Repair', 'vnmordersearch'), __('Order Search Repair', 'vnmordersearch'), 'manage_woocommerce', 'vnmWooOrderSearch', 'vnmWooOrderSearch_Details');
}

add_action('admin_menu', 'vnmWooOrderSearch_orderSearchMenuItem');

///
//	Admin Section
///

function vnmWooOrderSearch_Details() {
	
	wp_enqueue_script('vnm-ordersearch-script');
	
	$plugin = plugin_basename(__FILE__);
	
	?>
	
	<div id="vnmadmin" class="wrap vnmordersearch">
		
		<h2>
			<?php _e('Order Search Repair for WooCommerce', 'vnmordersearch'); ?>
		</h2>
		
		<p>
			<?php
				_e('The Order Search Repair will find all orders that do not have a searchable index for WooCommerce 3+ (i.e. where you can\'t find them by customer name, street or postcode) and update them.', 'vnmordersearch');
			?>
		</p>
		
		<p>
			<?php
				_e('Please note that this process can be quite intensive and, depending on the number of orders, can take quite a long time. You shouldn\'t close this page or change pages while it\'s taking place.', 'vnmordersearch');
			?>
		</p>
		
		<?php
			///
			//	Submission form
			///
		?>
		
		<div class="form">
			
			<div id="vnm-form-blocker" class="vnm-form-blocker">
				<div class="spinner is-active"></div>
			</div>
			
			<input type="hidden" id="action" data-name="action" value="vnmWooOrderSearch_ajaxSQL" />
			
			<table id="vnm-ordersearch-form" class="form-table">
				<tr>
					<th>
						<label for="limit"><?php _e('Limit', 'vnmordersearch'); ?>:</label>
					</th>
					
					<td>
						<input type="number" id="limit" data-name="limit" placeholder="100" max="500" value="100" required />
						<p class="description">
							<?php _e('Enter the number of orders to process at a time. This should be no more than 500.', 'vnmordersearch'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th>
						<label for="offset"><?php _e('Offset', 'vnmordersearch'); ?>:</label>
					</th>
					
					<td>
						<input type="number" id="offset" data-name="offset" placeholder="0" />
						<p class="description">
							<?php _e('SQL option only: Enter the offset you want to start the process at; for example, to skip the first 500 orders, enter <code>500</code>', 'vnmordersearch'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th>
						<label for="modify"><?php _e('Update all order indexes', 'vnmordersearch'); ?>:</label>
					</th>
					
					<td>
						<label for="modify">
							<input id="modify" data-name="modify" name="updatetype" checked="checked" type="radio" />
							<?php _e('Update all orders so that they are fully searchable.', 'vnmordersearch'); ?>
						</label>
						<p class="description">
							<?php _e('This will update your live database, so make sure you\'ve backed up!', 'vnmordersearch'); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th>
						<label for="sql"><?php _e('Output as SQL', 'vnmordersearch'); ?>:</label>
					</th>
					
					<td>
						<label for="sql">
							<input id="sql" data-name="sql" name="updatetype" type="radio" />
							<?php _e('Output the new search indexes as an SQL query', 'vnmordersearch'); ?>
						</label>
						<p class="description">
							<?php _e('If you want to update your database manually via SQL, select this option. This process will be slightly quicker, but you will have to make the SQL update yourself, and be wary of any syntax-breaking strings in the resulting SQL queries.', 'vnmordersearch'); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<div class="errormessage empty-error-message">
				<p>
					<?php
						_e('Please enter a limit for the number of orders to process per chunk (we suggest 100-200, and no more than 500); and check at least one option for either modifying the search indexes and/or outputting the results as SQL.', 'vnmordersearch');
					?>
				</p>
			</div>
			
			<div class="errormessage timeout-message">
				<p>
					<?php
						_e('The process took too long to respond. Lower the <code>limit</code> value and try again.', 'vnmordersearch');
					?>
				</p>
			</div>
			
			<p class="submit">
				<button type="button" class="button-primary ajax-send">
					<?php _e('Update orders', 'vnmordersearch'); ?>
				</button>
			</p>
		</div>
		
		<?php
			///
			//	Responses
			///
		?>
		
		<p id="vnm-ordersearch-progress" class="ordersearch-text" data-prefix="<?php _e('Working...', 'vnmordersearch'); ?>" data-postfix=" <?php _e('orders processed', 'vnmordersearch'); ?>" data-done="<?php _e('Done!', 'vnmordersearch'); ?>"></p>
		
		<p class="submit cancel">
			<button type="button" class="button ajax-abort">
				<?php _e('Cancel process', 'vnmordersearch'); ?>
			</button>
		</p>
		
		<div id="vnm-ordersearch-sql-wrapper">
			<textarea id="vnm-ordersearch-sql" readonly></textarea>
		</div>
		
		<?php
			///
			//	Retrieve total number of orders (without doing anything)
			///
		?>
		
		<hr />
		
		<div class="form unsearchable-orders">
			<div class="vnm-form-blocker">
				<div class="spinner is-active"></div>
			</div>
			
			<input type="hidden" class="action" data-name="action" value="vnmWooOrderSearch_ajaxGetTotalOrders" />
			
			<p>
				<?php _e('Retrieve the total number of orders that do not have a searchable index. Note that this does not affect your orders in any way.', 'vnmordersearch'); ?>
			</p>
			
			<p class="get-total">
				<button type="button" class="button-secondary ajax-retrieve">
					<?php _e('Get total number of unindexed orders', 'vnmordersearch'); ?>
				</button>
			</p>
			
			<p id="vnm-ordersearch-total" class="ordersearch-text" data-working="<?php _e('Working...', 'vnmordersearch'); ?>" data-result="<?php _e('Done! {total} orders found.', 'vnmordersearch'); ?>"></p>
			
		</div>
		
		<a href="http://www.verynewmedia.com/" id="vnmlogo" target="_blank">
			<?php _e('Developed by Very New Media&trade;'); ?>
		</a>
		
	</div>
	
	<?php
}

///
//	Get an associative array of a post's custom meta
///

function vnmWooOrderSearch_get_custom_array($id, $metaType = 'post') {
	
	switch ($metaType) {
		case 'post' : {
			return array_map( function($a){ return $a[0]; }, get_post_meta($id));
		}
	}
}

///
//	Ajax function for searching orders
///

function vnmWooOrderSearch_ajaxSQL() {
	
	$offset = (int)sanitize_title($_REQUEST['offset']);
	$limit = (int)sanitize_title($_REQUEST['limit']);
	
	$isSQL = sanitize_title($_REQUEST['sql']) == 'true' ? true : false;
	$isModify = sanitize_title($_REQUEST['modify']) == 'true' ? true : false;
	$isLoop = sanitize_title($_REQUEST['loop']) == 'true' ? true : false;
	
	$args = array(
		'post_type' => 'shop_order',
		'post_status' => 'any',
		'posts_per_page' => $limit,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_billing_address_index',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => '_billing_address_index',
				'value' => '',
			),
		),
		'suppress_filters' => false,
	);
	
	if ($isSQL) {
		$args['offset'] = $offset;
	}
	
	$orderPosts = get_posts($args);
	
	$i = 0;
	
	$jsonArray = array();
	
	$_max = 75;
	
	$sqlString = '';
	
	if (count($orderPosts)) {
		foreach ($orderPosts as $orderPost) {
			
			$custom = vnmWooOrderSearch_get_custom_array($orderPost->ID);
			
			$fieldsArray = array(
				'_first_name',
				'_last_name',
				'_company',
				'_address_1',
				'_address_2',
				'_city',
				'_state',
				'_postcode',
				'_country',
				'_email',
				'_phone',
			);
			
			$billingArray = array();
			$shippingArray = array();
			
			foreach($fieldsArray as $field) {
				$billKey = '_billing' . $field;
				$shipKey = '_shipping' . $field;
				
				if (isset($custom[$billKey])) {
					$billingArray[] = esc_sql($custom[$billKey]);
				}
				
				if (isset($custom[$shipKey])) {
					$shippingArray[] = esc_sql($custom[$shipKey]);
				}
			}
			
			$billingString = implode(' ', $billingArray);
			$shippingString = implode(' ', $shippingArray);
			
			//	Deal with edge cases where, for whatever reason, the actual billing/shipping details are empty
			
			if (ctype_space($billingString)) {
				$billingString = '{Empty}';
			}
			
			if (ctype_space($shippingString)) {
				$shippingString = '{Empty}';
			}
			
			//	SQL output
			
			if ($isSQL) {
				
				if ($i == 0) {
					if ($isLoop == true || $sqlString != '') {
						$sqlString .= ';' . "\n";
					}
					
					$sqlString .= 'INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ' . "\n";
				} else {
					$sqlString .= ',' . "\n";
				}
				
				$sqlString .= "('" . $orderPost->ID . "', '_billing_address_index', '" . $billingString . "'),\n";
				$sqlString .= "('" . $orderPost->ID . "', '_shipping_address_index', '" . $shippingString . "')";
			}
			
			//	Modify records
			
			if ($isModify) {
				update_post_meta($orderPost->ID, '_billing_address_index', $billingString);
				update_post_meta($orderPost->ID, '_shipping_address_index', $shippingString);
			}
			
			$i++;
			
			if ($i > $_max) {
				$i = 0;
			}
		}
	} else {
		$jsonArray['status'] = 'completed';
	}
	
	$jsonArray['response'] = 'success';
	$jsonArray['limit'] = $limit;
	$jsonArray['newoffset'] = $offset + count($orderPosts);
	
	if ($isSQL) {
		$jsonArray['sql'] = $sqlString;
	}
	
	if ($isModify) {
		$jsonArray['modify'] = 'true';
	}
	
	wp_send_json($jsonArray);
}

add_action('wp_ajax_vnmWooOrderSearch_ajaxSQL', 'vnmWooOrderSearch_ajaxSQL');

///
//	Ajax function for finding the total number of orders with missing search indeces
///

function vnmWooOrderSearch_ajaxGetTotalOrders() {
	
	$args = array(
		'post_type' => 'shop_order',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => '_billing_address_index',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => '_billing_address_index',
				'value' => '',
			),
		),
		'suppress_filters' => false,
	);
	
	$orderPosts = get_posts($args);
	
	$jsonArray = array();
	
	$jsonArray['response'] = 'success';
	$jsonArray['total'] = count($orderPosts);
	wp_send_json($jsonArray);
}

add_action('wp_ajax_vnmWooOrderSearch_ajaxGetTotalOrders', 'vnmWooOrderSearch_ajaxGetTotalOrders');

?>