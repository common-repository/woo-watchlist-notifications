<?php
/*
 * Plugin Name: Woocommerce Watchlist Notifications
 * Description: A simple watchlist plugin to get notification alerts when any new product will be added to the stock and if that will be matched to your watchlist criteria.
 * Author: Priya Jain
 * Version: 1.0.0
 * Author URI: http://cisin.com/
 * License: GPL2+
 */
if( !class_exists( 'WWN' ) ) {

	class WWN {

		private static $_this;

		private static $_version;

		private static $scripts_version;

		private static $admin_settings;

		private static $watchlist;

		public static $table_name;

		public static $endpoint = 'my-watchlist-keywords';

		function __construct() {

			global $wpdb;
		
			if( isset( self::$_this ) )
				wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.', get_class( $this ) ) );
			
			self::$_this = $this;

			self::$_version = '1.0.0';

			self::$table_name = $wpdb->prefix . 'woocommerce_watchlist_notification';
			
			require( 'includes/class_watchlistNotfications.php' );

			require( 'admin/class-admin-settings.php' );

			self::$admin_settings = new Woocommerce_watchlist_notifications_settings
			();

			self::$watchlist = new watchlistNotifications();
			
			// Create tables on plugin activation
			register_activation_hook( __FILE__, array( 'WWN', 'wwn_install' ) );
			//Flush rewrite rules on plugin deactivation
			register_deactivation_hook( __FILE__, array( $this, 'wwn_activate_deactivate' ));
			// Load the textdomain
			add_action( 'plugins_loaded', array( $this, 'wwn_load_textdomain' ) );
			// Define javascript vars
			add_action( 'wp_head', array( $this, 'wwn_add_js_vars' ) );	
			// Enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'wwn_load_js_css' ) );
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'wwn_add_endpoints' ) );
			//add query variables for the plugin
			add_filter( 'query_vars', array( $this, 'wwn_add_query_vars' ), 0 );
			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'wwn_endpoint_title' ) );
			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'wwn_my_watchlist_menu' ) );
			//Set content for newly added My watchlist page
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'wwn_endpoint_content' ) );
			// AJAX action to handle the "add to my watchlist" button click
			add_action( 'wp_ajax_wwn-addtowatchlist', array( $this, 'wwn_add_to_watchlist' ) );	
			// AJAX action to handle the "remove from my watchlist" button click
			add_action( 'wp_ajax_wwn-removewatchlist', array( $this, 'wwn_remove_keyword_watchlist' ) );
			// AJAX action to handle the "remove from my watchlist" button click
			add_action( 'wp_ajax_wwn-removewatchlistbyid', array( $this, 'wwn_remove_keyword_by_id_from_watchlist' ) );	
			// Render the button in the product page
			add_action( 'woocommerce_single_product_summary', array( $this, 'wwn_add_button_watchlist' ), 38 );	
			// If a product is deleted, remove it from the watchlist table
			add_action( 'delete_post', array( $this, 'wwn_remove_from_watchlist' ) );
			// On post a new product it will sent a notification email to the user for their matched keywords.
			add_action( 'publish_product', array($this, 'wwn_sent_notification_email_function') );
			/** add login popup if user is not already logged in **/
			add_action( 'wp_ajax_nopriv_ajaxlogin', array( $this, 'wwn_ajax_login' ) );
			
			add_action( 'wp_ajax_ajaxlogin', array( $this, 'wwn_ajax_login' )  );

		}

		static function this() {
		
			return self::$_this;
		
		}
		/** 
		** Create table for adding data to the watchlist.
		**/
		function wwn_install() {

			global $wpdb;

			$wpdb->query(
				'CREATE TABLE `' . self::$table_name . '` (
  					`id` bigint(20) NOT NULL AUTO_INCREMENT,
  					`user_id` bigint(20) NOT NULL,
  					`watchlist_keyword` varchar(256) NOT NULL,
  					`watchlist_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;'
			);

			flush_rewrite_rules();
		}
		/*
		** Flush cache on activation & deactivation
		*/
		function wwn_activate_deactivate() {
		    flush_rewrite_rules();
		}
		

		/**
		** String translations on multilanguage websites.	
		**/	
		function wwn_load_textdomain() {

			load_plugin_textdomain( 'watchlist_notification', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		}

		/**
		** Adding js variable for ajax url and custom messages.
		**/	
		function wwn_add_js_vars() {

			?>

			<script type="text/javascript">
				var wwn_ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				var wwn_msg_ok = '<?php echo empty(get_option('wwn_after_added_button_label')) ? 'Added keyword to my watchlist' : get_option('wwn_after_added_button_label'); ?>';
				var wwn_msg_ko = '<?php _e( 'ERROR!', 'wwn' ); ?>';
			</script>

			<?php

		}

		/**
		** Loading js/css for the watchlist plugin.
		**/	
		function wwn_load_js_css() {
			wp_enqueue_script( 'jquery' );
		    wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js', array( 'jquery' ) );
		    wp_enqueue_style( 'datatables-style', '//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css' );
		    wp_register_script( 'wwn-main', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), self::$scripts_version, true );
			wp_enqueue_script( 'wwn-main' );
			wp_register_style( 'wwn-dynamiccss',  plugins_url( 'assets/css/dynamic-style.php', __FILE__ ), array(), self::$scripts_version, false);
			wp_enqueue_style( 'wwn-dynamiccss' );
			wp_register_style( 'wwn-css', plugins_url( 'assets/css/styles.css', __FILE__ ), array(), self::$scripts_version, false );
			wp_enqueue_style( 'wwn-css' );
			wp_enqueue_script( 'wwn-js', plugins_url( 'assets/js/ajax-login-script.js', __FILE__ ), array( 'jquery' ), self::$scripts_version, true );
	        wp_localize_script('wwn-js', 'wwn_ajax_function', array(
	            'ajaxurl' => admin_url('admin-ajax.php')
	        ));     
	    }	

		/**
		 * Register new endpoint to use inside My Account page.
		 *
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		 */
		public function wwn_add_endpoints() {
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
			flush_rewrite_rules();
		}
		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function wwn_add_query_vars( $vars ) {
			$vars[] = self::$endpoint;
			return $vars;
		}
		/**
		 * Set endpoint title.
		 *
		 * @param string $title
		 * @return string
		 */
		public function wwn_endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = __( 'My Watchlist', 'woocommerce' );
				remove_filter( 'the_title', array( $this, 'wwn_endpoint_title' ) );
			}
			return $title;
		}
		/**
		 * Insert the new endpoint into the My Account menu.
		 *
		 * @param array $items
		 * @return array
		 */
		public function wwn_my_watchlist_menu( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			// Insert your custom endpoint.
			$items[ self::$endpoint ] = __( 'My Watchlist', 'woocommerce' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;
			return $items;
		}
		/**
		 * Endpoint HTML content.
		 */
		public function wwn_endpoint_content() {
			if( is_user_logged_in() ) {
				echo '<div class="wwn-watchlist"><h2>' . __( 'My watchlist', 'wwn' ).'</h2>';
				echo '<div class="add-on-watchlist" align="right"><a href="javascript:void(0);" class="add-keyword">' . __( 'Add to watchlist', 'wwn' ).'</a></div>';
				self::$watchlist->add_new_keyword(get_current_user_id() ); 
				self::$watchlist->render_list( get_current_user_id() );
				echo '</div>';
			}
		}

		 /**
		** Add "Add to watchlist" button to the product page.
	    **/
		function wwn_add_button_watchlist() {
			// Show the button only if the customer is currently logged in
			if( is_user_logged_in() ) {
				// Check if the customer has already added the product to the watchlist
				// If not, then show the button
				global $product;
				$product_title = get_the_title($product->post->ID); 
				if( !self::$watchlist->product_in_watchlist( $product_title, get_current_user_id() ) ) {
					self::$watchlist->show_button( $product_title );
				}else{
					self::$watchlist->show_addedbutton( $product_title );
				}
			}else{
				self::$watchlist->login_popup();
			}
		}	

		/**
		** Function for adding product to the watchlist.
		**/
		function wwn_add_to_watchlist() {
			$response = false;

			if( is_user_logged_in() ) {
				$product_title = trim($_POST['product_title']);
				if( $product_title != "") {
					 $response = self::$watchlist->add_product_to_watchlist( get_current_user_id(), $product_title);
				}
			}
			if( $response ) {
				die( json_encode(array( 'response' => 'OK' )));
			} else {
				die( json_encode(array( 'response' => 'KO' )));
			}			

		}

		/**
		 * Remove keyword from watchlist, If already added.
		 *
		 */
		function wwn_remove_keyword_watchlist() {

			$response = false;

			if( is_user_logged_in() ) {
				$product_title = trim($_POST['product_title']);
				if( $product_title != "") {
					$response = self::$watchlist->remove_keyword_from_watchlist( get_current_user_id(), $product_title);
				}
			}
			if( $response ) {
				die( json_encode(array( 'response' => 'OK' )));
			} else {
				die( json_encode(array( 'response' => 'KO' )));
			}			

		}

		/**
		 * Remove keyword from watchlist by using ID, If already added in my-account page.
		 *
		 */
		function wwn_remove_keyword_by_id_from_watchlist() {

			$response = false;

			if( is_user_logged_in() ) {
				$id = trim($_POST['id']);
				if( $id != "") {
					$response = self::$watchlist->remove_keyword_by_id_from_watchlist( get_current_user_id(), $id);
				}
			}
			if( $response ) {
				die( json_encode(array( 'response' => 'OK' )));
			} else {
				die( json_encode(array( 'response' => 'KO' )));
			}			

		}	

		/**
		** Remove product from watchlist from the single product page.
		**/
		function wwn_remove_from_watchlist( $post_id ) {
			if( is_user_logged_in() ) {
				$product_title = get_the_title($post_id);
				self::$watchlist->remove_product_from_watchlist( $product_title );
			}
		}

		/**
		** Function to check if new product has been published and sent email to the user	
		*/
		public function wwn_sent_notification_email_function($post_id){
			if( is_user_logged_in() ) {
				global $post; 
    			$post = get_post($post_id);
    			if ($post->post_status != 'publish' || $post->post_type != 'product') {
			        return;
			    }
			    if ( $post->post_date != $post->post_modified ){
					return;
				}
				else{
					self::$watchlist->sent_stock_alert_notification_email($post_id ); 
				}
			}
		}


		function wwn_ajax_login(){
			// First check the nonce, if it fails the function will break
		    //check_ajax_referer( 'ajax-login-nonce', 'security' );
		    // Nonce is checked, get the POST data and sign user on
		    $info = array();
		    $info['user_login'] = $_POST['username'];
		    $info['user_password'] = $_POST['password'];
		    $info['remember'] = true;

		    $user_signon = wp_signon( $info, false );
		    if ( is_wp_error($user_signon) ){
		        echo 0;
		    } else {
		    	echo 1;
		    }

		    die();
		}

	}

}	

new WWN();