<?php 
/**
 * @wordpress-plugin
 * Plugin Name: Shop Manager Admin for WooCommerce
 * Plugin URI:  https://www.zorem.com/shop
 * Description: Save time managing your WooCommerce shop! Shop Manager Admin adds a customizable WooCommerce quick-links menu to the WordPress admin bar (frontend & backend).
 * Version:     3.1.2
 * Author:      zorem
 * Author URI:  http://www.zorem.com/
 * License:     GPL-2.0+
 * License URI: http://www.zorem.com/
 * Text Domain: woocommerce-shop-manager-admin-bar
 * WC tested up to: 4.0
 */
class woo_shop_manager_admin {
	
	/**
	 * Sales Report Email for WooCommerce
	 *
	 * @var string
	 */
	public $version = '3.1.2';
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		if ( $this->is_wc_active() ){
			//start adding hooks
			$this->init();
		}
	}
	
	/**
	 * Check if WC is active
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}
		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Display WC active notice
	 *
	 * @access public
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( __( 'Please install and activate %sWooCommerce%s for Shop Manager Admin for WooCommerce!', 'woocommerce-shop-manager-admin-bar' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}
	
	/*
	* init when class loaded
	*/
	public function init(){
		
		/***** Init Hook *****/
		add_action('plugins_loaded', array( $this,'sma_load_textdomain'));	
		
		/*
		* Method enqueue_script
		* js/css load
		*/
		add_action( 'admin_enqueue_scripts', array( $this,'sma_admin_include_script'), 200 );
		
		
		//ajax save admin api settings
		add_action( 'wp_ajax_sma_general_settings_form_update', array( $this, 'sma_general_settings_form_update_callback') );
		add_action( 'wp_ajax_sma_dashboard_settings_form_update', array( $this, 'sma_dashboard_settings_form_update_callback') );
		add_action( 'wp_ajax_sma_login_settings_form_update', array( $this, 'sma_login_settings_form_update_callback') );
		add_action( 'wp_ajax_sma_wc_settings_form_update', array( $this, 'sma_wc_settings_form_update_callback') );	
		add_action( 'wp_ajax_sma_admin_menu_settings_form_update', array( $this, 'sma_admin_menu_settings_form_update_callback') );		
		
		/*call for admin menu*/
		add_action('admin_menu', array( $this,'woocommerce_admin_menu_register_settings_register_options_page'), 100);
		
		/*call for admin footer text*/
		add_filter('admin_footer_text', array( $this,'change_admin_footer'));
		
		/*call for login page footer text*/
		add_action( 'login_footer', array( $this,'sma_login_page_footer') );
		
		add_filter('login_headertext',array( $this,'logo_headertitle'), 10, 1); 
		
		/*call for dashboard widgets*/ 
		add_action('wp_dashboard_setup', array( $this,'remove_dashboard_widgets' ));
	
		/*** Code for display admin bar in backend or not  ***/
		$admin_bar_backend = get_option('admin_bar_backend', 'yes');
		
		if($admin_bar_backend == 'yes'){
			add_action( 'admin_bar_menu', array( $this,'wsmab_zorem_woocommerce_admin_bar_menu'), 98 );		
		}	
		
		/*
		* add custom columns of Total Spend in user admin panel
		*/
		add_filter( 'user_contactmethods', array( $this, 'new_total_spend_column'), 10, 1 );
		add_filter( 'manage_users_columns', array( $this, 'new_modify_total_spend_column_table') );
		add_filter( 'manage_users_custom_column', array( $this,'new_modify_total_spend_row_table'), 10, 3 );	
			
		/*
		* add custom columns of Order Count in user admin panel
		*/
		add_filter( 'user_contactmethods', array( $this,'new_order_count_column'), 10, 1 );
		add_filter( 'manage_users_columns', array( $this,'new_modify_order_count_column_table') );
		add_filter( 'manage_users_custom_column', array( $this,'new_modify_order_count_row_table'), 10, 3 );	
		
		add_action( 'wp_head', array( $this,'zorem_woocommerce_admin_bar_style') );
		add_action( 'admin_head', array( $this,'zorem_woocommerce_admin_bar_style') );
		
		/* change custom logo call */
		add_action( 'login_enqueue_scripts', array( $this,'change_login_page_logo') );
		
		/* change logo URL call */
		add_filter( 'login_headerurl', array( $this,'change_loginlogo_url' ) );
		
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ),  array( $this , 'my_plugin_action_links' ) );
		
		if(!isset($_GET['page'])) {
			return;
		}
		if($_GET['page'] == 'woocommerce_shop_manager_admin_option'){
			// Hook for add admin body class in settings page
			add_filter( 'admin_body_class', array( $this, 'sma_post_admin_body_class' ), 100 );
		}
	}
	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	function my_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=woocommerce_shop_manager_admin_option' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
		), $links );
		return $links;
	}
	
	
	/* 
	* plugin textdomain function 
	*/	
	function sma_load_textdomain() {
		load_plugin_textdomain( 'woocommerce-shop-manager-admin-bar', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
	}
	
	function sma_post_admin_body_class($body_class) {
			
			$body_class .= 'sma-shop-manager-admin-setting';
	 
			return $body_class;
	}
	
	/* 
	* include js and css function for admin 
	*/	
	function sma_admin_include_script(){
		
		// Add condition for css & js include for admin page  
		if(!isset($_GET['page'])) {
				return;
		}
		if( $_GET['page'] != 'woocommerce_shop_manager_admin_option') {
			return;
		}
		
		// Add the color picker css file       
		wp_enqueue_style( 'wp-color-picker' );
		
		// Add the WP Media 
		wp_enqueue_media();
		
		// Add select2 css & js file
		wp_enqueue_style('select2-sma', plugins_url('/assets/css/select2.min.css', __FILE__ ));
		wp_enqueue_script('select2-sma', plugins_url('/assets/js/select2.min.js', __FILE__));
		
		// Add tiptip js and css file
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_enqueue_script( 'jquery-tiptip' );
		
		// Add custom css & js file 
		wp_enqueue_style( 'sma_admin_style', untrailingslashit( plugins_url( '/', __FILE__ ) ) .'/assets/css/admin-style.css', array(), $this->version );
		wp_enqueue_style( 'sma-material-css', plugin_dir_url(__FILE__) . 'assets/css/material.css', array(), $this->version );
		wp_enqueue_script( 'sma-material-min-js', plugin_dir_url(__FILE__) . 'assets/js/material.min.js', array(), $this->version );
		wp_enqueue_script( 'sma_admin_script', plugins_url( '/assets/js/admin-script.js', __FILE__ ), array( 'jquery','wp-color-picker' ), $this->version );
			
	}	
	
	/* 
	* Register admin menu page function
	*/
	function woocommerce_admin_menu_register_settings_register_options_page() {
		add_submenu_page( 'woocommerce', 'Shop Manager Admin', 'Shop Manager Admin', 'manage_options', 'woocommerce_shop_manager_admin_option', array( $this,'woocommerce_admin_options_page' )); 
	}
	
	/*
	 * get_zorem_pluginlist
	 * 
	 * return array
	*/
	public function get_zorem_pluginlist(){
		
		if ( !empty( $this->zorem_pluginlist ) ) return $this->zorem_pluginlist;
		
		if ( false === ( $plugin_list = get_transient( 'zorem_pluginlist' ) ) ) {
			
			$response = wp_remote_get( 'https://www.zorem.com/wp-json/pluginlist/v1/' );
			
			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$body    = $response['body']; // use the content
				$plugin_list = json_decode( $body );
				set_transient( 'zorem_pluginlist', $plugin_list, 60*60*24 );
			} else {
				$plugin_list = array();
			}
		}
		return $this->zorem_pluginlist = $plugin_list;
	}
	
	/* 
	* admin page design function 
	*/
	function woocommerce_admin_options_page(){
		?>
		<div class="main-title"><img class="sma-plugin-logo" src="<?php echo plugin_dir_url(__FILE__)?>assets/images/SMA-logo.png"></div>
			<div class="woocommerce sma_admin_layout">
				<div class="sma_admin_content">
				
					<input id="tab1" type="radio" name="tabs" class="sma_tab_input"  data-tab="general" checked>
					<label for="tab1" class="sma_tab_label first_label"><?php _e('General', 'woocommerce'); ?></label>
					
					<input id="tab2" type="radio" name="tabs" class="sma_tab_input" data-tab="dashboard" <?php if(isset($_GET['tab']) && ($_GET['tab'] == 'dashboard')){ echo 'checked'; } ?>>
					<label for="tab2" class="sma_tab_label"><?php _e('Dashboard', 'woocommerce'); ?></label>
					<input id="tab3" type="radio" name="tabs" class="sma_tab_input" data-tab="login_page" <?php if(isset($_GET['tab']) && ($_GET['tab'] == 'login_page')){ echo 'checked'; } ?>>
					<label for="tab3" class="sma_tab_label"><?php _e('Login Page', 'woocommerce-shop-manager-admin-bar'); ?></label>
					<input id="tab4" type="radio" name="tabs" class="sma_tab_input" data-tab="woo_commerce"<?php if(isset($_GET['tab']) && ($_GET['tab'] == 'woo_commerce')){ echo 'checked'; } ?>>
					<label for="tab4" class="sma_tab_label"><?php _e('WooCommerce', 'woocommerce'); ?></label>
					<input id="tab5" type="radio" name="tabs" class="sma_tab_input" data-tab="admin_menu" <?php if(isset($_GET['tab']) && ($_GET['tab'] == 'admin_menu')){ echo 'checked'; } ?>>
					<label for="tab5" class="sma_tab_label"><?php _e('Admin Menu', 'woocommerce-shop-manager-admin-bar'); ?></label>
					<div class="sma_nav_doc_section">					
								<a target="blank" href="https://www.zorem.com/docs/shop-manager-admin-for-woocommerce/" style="color:#252E3C;"><?php _e('Documentation'); ?></a>
					</div>
					
					<?php require_once( 'views/sma_general_tab.php' );?>
					<?php require_once( 'views/sma_dashboard_tab.php' );?>
					<?php require_once( 'views/sma_login_page_tab.php' );?>
					<?php require_once( 'views/sma_woo_commerce_tab.php' );?>
					<?php require_once( 'views/sma_admin_menu_tab.php' );?>
							
				</div>
			</div>
			<div id="sma-toast-example" aria-live="assertive" aria-atomic="true" aria-relevant="text" class="mdl-snackbar mdl-js-snackbar">
				<div class="mdl-snackbar__text"></div>
				<button type="button" class="mdl-snackbar__action"></button>
			</div>           			
		<?php
	}
	
	/*
	* settings form save for General tab
	*/
	function sma_general_settings_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'general_form_action', 'general_form_nonce_field' ) ) {
			
			update_option( 'admin_bar_backend', $_POST[ 'admin_bar_backend' ] );
			update_option( 'dashboard_footer_text', $_POST[ 'dashboard_footer_text' ] );
		
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* settings form save for Dashboard tab
	*/
	function sma_dashboard_settings_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'dashboard_form_action', 'dashboard_form_nonce_field' ) ) {
	
			update_option( 'hide_all_panels', $_POST[ 'hide_all_panels' ] );
			update_option( 'remove_welcome_panel', $_POST[ 'remove_welcome_panel' ] );
			update_option( 'remove_wp_events', $_POST[ 'remove_wp_events' ] );
			update_option( 'remove_quick_draft', $_POST[ 'remove_quick_draft' ] );
			update_option( 'remove_dashboard_right_now', $_POST[ 'remove_dashboard_right_now' ] );
			update_option( 'remove_dashboard_activity', $_POST[ 'remove_dashboard_activity' ] );
			update_option( 'remove_woocommerce_dashboard_status', $_POST[ 'remove_woocommerce_dashboard_status' ] );
			update_option( 'remove_woocommerce_reviews', $_POST[ 'remove_woocommerce_reviews' ] );
			
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* settings form save for login page tab
	*/
	function sma_login_settings_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'login_form_action', 'login_form_nonce_field' ) ) {
			
			update_option( 'image_path', $_POST[ 'image_path' ] );
			update_option( 'logo_width', $_POST[ 'logo_width' ] );
			update_option( 'bottom_margin', $_POST[ 'bottom_margin' ] );
			update_option( 'bg_color', $_POST[ 'bg_color' ] );
			update_option( 'font_color', $_POST[ 'font_color' ] );
			update_option( 'form_font_color', $_POST[ 'form_font_color' ] );
			update_option( 'form_bg_color', $_POST[ 'form_bg_color' ] );
			update_option( 'btn_color', $_POST[ 'btn_color' ] );
			update_option( 'login_footer_text', $_POST[ 'login_footer_text' ] );		
			
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* settings form save for woocommerce tab
	*/
	function sma_wc_settings_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'wc_form_action', 'wc_form_nonce_field' ) ) {
			
			update_option( 'processing_order_count', $_POST[ 'processing_order_count' ] );
			update_option( 'display_order_count', $_POST[ 'display_order_count' ] );
			update_option( 'display_total_spend', $_POST[ 'display_total_spend' ] );
			update_option( 'horizontal_scroll_orders_admin', $_POST[ 'horizontal_scroll_orders_admin' ] );
			
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* settings form save for Shop Menu tab
	*/
	function sma_admin_menu_settings_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'admin_menu_form_action', 'admin_menu_form_nonce_field' ) ) {
			
			update_option( 'sma_hide_admin_menu', $_POST[ 'admin_menu' ] );
			
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/* 
	* Dashboard widgets customize function 
	*/
	function remove_dashboard_widgets() {	  
		
		global $wp_meta_boxes;
		$hide_all_panels = get_option('hide_all_panels');
		$remove_welcome_panel = get_option('remove_welcome_panel');
		$remove_wp_events = get_option('remove_wp_events');
		$remove_quick_draft = get_option('remove_quick_draft');	
		$remove_dashboard_right_now = get_option('remove_dashboard_right_now');
		$remove_dashboard_activity = get_option('remove_dashboard_activity');
		$remove_woocommerce_dashboard_status = get_option('remove_woocommerce_dashboard_status');
		$remove_woocommerce_reviews = get_option('remove_woocommerce_reviews');
		$display_total_spend = get_option('display_total_spend');		
		$display_order_count = get_option('display_order_count');		
		$processing_order_count = get_option('processing_order_count');
		
		$current_role = wp_get_current_user(); 
		
		foreach ($current_role->roles as $key=>$value){
	
			if( $remove_wp_events != 'yes' ){
			remove_action('welcome_panel', 'wp_welcome_panel');		
			}
			if($remove_wp_events != 'yes'){
				unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);	
			}
			if($remove_quick_draft != 'yes'){
				unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
			} 
			if($remove_dashboard_right_now != 'yes' ){			
				unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
			}
			if($remove_dashboard_activity != 'yes' ){			
				unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
			}
			
			if($remove_woocommerce_dashboard_status != 'yes' ){			
				remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');		
			}
			if($remove_woocommerce_reviews != 'yes' ){			
				remove_meta_box( 'woocommerce_dashboard_recent_reviews', 'dashboard', 'normal'); 
			}
		}
		
	}
	
	/* 
	* dashboard footer text function 
	*/
	function change_admin_footer(){
		
		$dashboard_footer_text = get_option('dashboard_footer_text');
		echo '<span id="footer-note">'.$dashboard_footer_text.'</span>';
		
	}
	
	/// order status function ////
	function get_orders_count_from_status( $status ){
		
		$processing_order_count = get_option('processing_order_count');	
		if( $processing_order_count != 'yes' ){ return; }
		global $wpdb;
	
		// We add 'wc-' prefix when is missing from order staus
		$status = 'wc-' . str_replace('wc-', '', $status);
	
		$count = $wpdb->get_var("
			SELECT count(ID)  FROM {$wpdb->prefix}posts WHERE post_status LIKE '$status' AND `post_type` LIKE 'shop_order'
		");
		//echo $count; exit;
		if ( $count != '0'){ return " (".$count.")"; }
		
	}
	
	/* 
	* admin bar menu design backend function 
	*/
	function wsmab_zorem_woocommerce_admin_bar_menu(){
	
		/*include file shop manager menu options*/ 
		//require_once( 'include/sma_shop_manager_menu.php' );
		
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );	
		
		global $wp_admin_bar;
		$processingordercount = woo_shop_manager_admin()->get_orders_count_from_status( "processing" );
		
		$enable_taxes = wc_tax_enabled();
		
		global $wp_meta_boxes;
	
		$admin_menu = get_option('sma_hide_admin_menu', '1');

		//print_r($admin_menu);
		if(empty($admin_menu)){
			
			$admin_menu = array();
		}
		if ( class_exists( 'WooCommerce' ) || class_exists( 'Woocommerce' ) ) {
			
			/** Main item URL helpers */
			$wsmab_woocomerce_main_url_orders   = ( current_user_can( 'edit_shop_orders' ) ) ? admin_url( 'edit.php?post_type=shop_order' ) : '#';
			$wsmab_woocomerce_settings_url   = admin_url( 'admin.php?page=wc-settings' );
			$wsmab_woocomerce_main_url_settings = ( current_user_can( 'manage_woocommerce' ) ) ?  $wsmab_woocomerce_settings_url : '#';
				
			/** Filter the main item icon's url */
			$wsmab_woocomerce_main_url = apply_filters(
				'wcaba_filter_main_item_url',
				( defined( 'WP_DEBUG') && WP_DEBUG ) ? $wsmab_woocomerce_main_url_settings : $wsmab_woocomerce_main_url_orders
			);
			
			$wsmab_menu_main_item_title = apply_filters(
				'wsmab_filter_menu_main_item',
				_x( 'Shop Manager', 'Translators: Main item', 'woocommerce-shop-manager-admin-bar' )
			);
	
			/** Filter the main item name's tooltip */
			$wsmab_menu_main_item_title_tooltip = apply_filters(
				'wsmab_filter_menu_main_item_tooltip',
				_x(
					'Current Orders - WooCommerce Shop',
					'Translators: Main item - for the tooltip',
					'woocommerce-shop-manager-admin-bar'
				)
			);
			
			/** Filter the main item icon's class/display */
			$wsmab_main_icon_display = apply_filters(
				'wsmab_filter_main_icon_display',
				'icon-woocommerce'
			);
			
			$prefix = 'ddw-woocommerce-';
			$wsmab_main = 'wsmab_main';
			$woocommerce = 'woocommerce';
			$wordpress = 'wordpress';
			$page_builder = 'page-builder';
			$plugin_setting = 'plugin-setting';
			$pages = $prefix . 'pages';
			$posts = $prefix . 'posts';
			$users = $prefix . 'users';
			$all_users = $prefix . 'all_users';
			$ux_blocks = $prefix . 'ux-blocks';
			$wpml = $prefix . 'wpml';
			$contact = $prefix . 'contact';
			$products = $prefix . 'products';
			$orders = $prefix . 'orders';
			$o_all_order = $prefix . 'o-all-order';
			$wcgroup = $prefix . 'wcgroup';
			$taxes = $prefix . 'taxes';
			$coupons = $prefix . 'coupons';	
			$reports = $prefix . 'reports';
			$status = $prefix . 'status';
			$order_reports = $prefix . 'order-reports';
			$sales_by_date = $prefix . 'sales-by-date';
			$sales_by_product = $prefix . 'sales-by-product';
			$sales_by_cat = $prefix . 'sales-by-cat';
			$coupons_by_date = $prefix . 'coupons-by-date';
			$customer_download = $prefix . 'customer-download';
			$sales_by_country = $prefix . 'sales-by-country';
			$sales_by_channel = $prefix . 'sales-by-channel';
			$customer_reports = $prefix . 'customer-reports';
			$customer_vs_guests = $prefix . 'customer-vs-guests';
			$customer_list = $prefix . 'customer-list';
			$stock_reports = $prefix . 'stock-reports';
			$taxes_reports = $prefix . 'taxes-reports';
			$taxes_by_code = $prefix . 'taxes-by-code';
			$taxes_by_date = $prefix . 'taxes-by-date';
			$shop_settings = $prefix . 'shop-settings';
			$product_shop_settings = $prefix . 'product-shop-settings';
			$shipping_settings = $prefix . 'shipping-settings';
			$payment_settings = $prefix . 'payment-settings';
			$email_settings = $prefix . 'email-settings';
			$subscription_emails = $prefix . 'subscription-emails';
			$subscription_reports = $prefix . 'subscription-reports';
			$subscription_events_by_date = $prefix . 'subscription-events-by-date';
			$upcoming_recurring_revenue = $prefix . 'upcoming-recurring-revenue';
			$all_order_status = wc_get_order_statuses();	
			global $wpdb;
			$order_status = wc_get_order_statuses();
			$order_status = array_keys($order_status);
			$order_status_string = implode("','",$order_status);		
			
			$order_totals = apply_filters( 'woocommerce_reports_sales_overview_order_totals', $wpdb->get_row( "
			
			SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
			
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			
			WHERE meta.meta_key = '_order_total'
			
			AND posts.post_type = 'shop_order'
			
			AND posts.post_status IN ( '" . $order_status_string . "' )
			
			" ) );
			$all_order_label = __( 'All Orders', 'woocommerce-shop-manager-admin-bar' ).'&nbsp;('.$order_totals->total_orders.')';
			
			/** Add the top-level menu item */
			$wp_admin_bar->add_menu( array(
				'id'    => $wsmab_main,
				'title' => $wsmab_menu_main_item_title,
				'href'  => esc_url( $wsmab_woocomerce_main_url ),
				'meta'  => array(
					'class' => $wsmab_main_icon_display,
					'title' => $wsmab_menu_main_item_title_tooltip
				)
			) );
			if ( current_user_can( 'edit_shop_orders' ) ) {
				
				
					$menu_items[ 'orders' ] = array(
						'parent' => $wsmab_main,
						'id'     => $woocommerce,
						'title'  => __( 'Orders', 'woocommerce' ).$processingordercount,
						'href'   => admin_url( 'edit.php?post_type=shop_order' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Orders', 'woocommerce' )
						)
					);
				
	
					/** Display order status links if theme support is added */
						$menu_items[ 'o-all-order' ] = array(
							'parent' => $orders,
							'title'  => __( $all_order_label, 'woocommerce-shop-manager-admin-bar' ),
							'href'   => admin_url( 'edit.php?post_type=shop_order' ),
							'meta'   => array(
								'target' => '',
								'title'  => __( $all_order_label, 'woocommerce-shop-manager-admin-bar' )
							)
						);
					
						
						
				foreach($all_order_status as $order_status_slug => $order_status_name){
					
					//echo '<pre>';print_r($order_status_slug);echo '</pre>';							
					$order_totals = apply_filters( 'woocommerce_reports_sales_overview_order_totals', $wpdb->get_row( "
			
					SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
					
					LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
					
					WHERE meta.meta_key = '_order_total'
					
					AND posts.post_type = 'shop_order'
					
					AND posts.post_status IN ( '".$order_status_slug."' )
					
					" ) );
					
				
	
					if($order_totals->total_orders == '0') { 
						$all_order_label = '';
					} else{
	 
						if ( !in_array($order_status_slug, (array)$admin_menu )) {
						
								$all_order_label = $order_status_name.'&nbsp;('.$order_totals->total_orders.')';
								
								 $menu_items[ $order_status_slug ] = array(
										  'parent' => $orders,
										  'title'  => __( $all_order_label, 'woocommerce-shop-manager-admin-bar' ),
										  'href'   => admin_url( 'edit.php?post_status='.$order_status_slug.'&post_type=shop_order' ),
										  'meta'   => array(
											  'target' => '',
											  'title'  => __( $all_order_label, 'woocommerce-shop-manager-admin-bar' )
										  )
								);
						}
					}
						 
				}
				
					$menu_items[ 'o-add-order' ] = array(
						'parent' => $orders,
						'title'  => __( 'Add new order', 'woocommerce' ),
						'href'   => admin_url( 'post-new.php?post_type=shop_order' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Add new order', 'woocommerce' )
						)
					);
	
			}
			
			/** Display "Products" section only for users with the capability 'edit_products' */
			if ( current_user_can( 'edit_products' ) ) {
				
					$menu_items[ 'products' ] = array(
						'parent' => $wsmab_main,
						'id'     => $woocommerce,
						'title'  => __( 'Products', 'woocommerce' ),
						'href'   => admin_url( 'edit.php?post_type=product' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Products', 'woocommerce' )
						)
					);
				
					$menu_items[ 'p-all-products' ] = array(
						'parent' => $products,
						'title'  => __( 'All Products', 'woocommerce' ),
						'href'   => admin_url( 'edit.php?post_type=product' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'All Products', 'woocommerce' )
						)
					);
				
					$menu_items[ 'p-add-product' ] = array(
						'parent' => $products,
						'title'  => __( 'Add New', 'woocommerce' ),
						'href'   => admin_url( 'post-new.php?post_type=product' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Add New', 'woocommerce' )
						)
					);
	
				/** Various 'product' taxonomies */
				if ( current_user_can( 'manage_product_terms' ) ) {
					
						$menu_items[ 'p-product-categories' ] = array(
							'parent' => $products,
							'title'  => __( 'Categories', 'woocommerce' ),
							'href'   => admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ),
							'meta'   => array(
								'target' => '',
								'title'  => __( 'Categories', 'woocommerce' )
							)
						);
					
						$menu_items[ 'p-product-tags' ] = array(
							'parent' => $products,
							'title'  => __( 'Tags', 'woocommerce' ),
							'href'   => admin_url( 'edit-tags.php?taxonomy=product_tag&post_type=product' ),
							'meta'   => array(
								'target' => '',
								'title'  => __( 'Tags', 'woocommerce' )
							)
						);
					
						$menu_items[ 'p-product-attributes' ] = array(
							'parent' => $products,
							'title'  => __( 'Attributes', 'woocommerce' ),
							'href'   => admin_url( 'edit.php?post_type=product&page=product_attributes' ),
							'meta'   => array(
								'target' => '',
								'title'  => __( 'Attributes', 'woocommerce' )
							)
						);	
	
				}  // end if
	
			}  // end if products cap check
					
					
			/** Display "Coupons" section only for users with the capability 'edit_shop_coupon' */
			if ( current_user_can( 'edit_shop_coupons' ) ) {
	
					$menu_items[ 'coupons' ] = array(
						'parent' => $wsmab_main,
						'id'     => $woocommerce,
						'title'  => __( 'Coupons', 'woocommerce' ),
						'href'   => admin_url( 'edit.php?post_type=shop_coupon' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Coupons', 'woocommerce' )
						)
					);
				
					$menu_items[ 'c-all-coupons' ] = array(
						'parent' => $coupons,
						'title'  => __( 'All coupons', 'woocommerce' ),
						'href'   => admin_url( 'edit.php?post_type=shop_coupon' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'All coupons', 'woocommerce' )
						)
					);
				
					$menu_items[ 'c-add-coupon' ] = array(
						'parent' => $coupons,
						'title'  => __( 'Add new coupon', 'woocommerce' ),
						'href'   => admin_url( 'post-new.php?post_type=shop_coupon' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Add new coupon', 'woocommerce' )
						)
					);
	
			}  // end if
	
				
			/** Reports */
				$menu_items[ 'reports' ] = array(
					'parent' => $wsmab_main,
					'id'     => $woocommerce,
					'title'  => __( 'Reports', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Reports', 'woocommerce' )
					)
				);
			
			/** Orders Reports */
				$menu_items[ 'order-reports' ] = array(
					'parent' => $reports,
					'title'  => __( 'Orders', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Orders', 'woocommerce' )
					)
				);
				
			/** sbd Orders Reports */
			$menu_items[ 'sales-by-date' ] = array(
				'parent' => $order_reports,
				'title'  => __( 'Sales by date', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_date' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Sales by date', 'woocommerce' )
				)
			);
				
			/** sbd 7 day */
			$menu_items[ 'sbd-7-day' ] = array(
				'parent' => $sales_by_date,
				'title'  => __( 'Last 7 Days', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_date&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last 7 Days', 'woocommerce' )
				)
			);
			
			/** sbd This Month */
			$menu_items[ 'sbd-this-month' ] = array(
				'parent' => $sales_by_date,
				'title'  => __( 'This month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_date&range=month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'This month', 'woocommerce' )
				)
			);
			
			/** sbd Last Month */
			$menu_items[ 'sbd-last-month' ] = array(
				'parent' => $sales_by_date,
				'title'  => __( 'Last month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_date&range=last_month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last month', 'woocommerce' )
				)
			);
			
			/** sbd Year */
			$menu_items[ 'sbd-year' ] = array(
				'parent' => $sales_by_date,
				'title'  => __( 'Year', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_date&range=year' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Year', 'woocommerce' )
				)
			);
			
			/** sbp Orders Reports */
			$menu_items[ 'sales-by-product' ] = array(
				'parent' => $order_reports,
				'title'  => __( 'Sales by product', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Sales by product', 'woocommerce' )
				)
			);
				
			/** sbp 7 day */
			$menu_items[ 'sbp-7-day' ] = array(
				'parent' => $sales_by_product,
				'title'  => __( 'Last 7 Days', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last 7 Days', 'woocommerce' )
				)
			);
			
			/** sbp This Month */
			$menu_items[ 'sbp-this-month' ] = array(
				'parent' => $sales_by_product,
				'title'  => __( 'This month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'This month', 'woocommerce' )
				)
			);
			
			/** sbp Last Month */
			$menu_items[ 'sbp-last-month' ] = array(
				'parent' => $sales_by_product,
				'title'  => __( 'Last month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=last_month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last month', 'woocommerce' )
				)
			);
			
			/** sbp Year */
			$menu_items[ 'sbp-year' ] = array(
				'parent' => $sales_by_product,
				'title'  => __( 'Year', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=year' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Year', 'woocommerce' )
				)
			);
			
			/** sbca Orders Reports */
			$menu_items[ 'sales-by-cat' ] = array(
				'parent' => $order_reports,
				'title'  => __( 'Sales by category', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_category' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Sales by category', 'woocommerce' )
				)
			); 
				
			/** sbca 7 day */
			$menu_items[ 'sbca-7-day' ] = array(
				'parent' => $sales_by_cat,
				'title'  => __( 'Last 7 Days', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_category&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last 7 Days', 'woocommerce' )
				)
			);
			
			/** sbca This Month */
			$menu_items[ 'sbca-this-month' ] = array(
				'parent' => $sales_by_cat,
				'title'  => __( 'This month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_category&range=month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'This month', 'woocommerce' )
				)
			);
			
			/** sbca Last Month */
			$menu_items[ 'sbca-last-month' ] = array(
				'parent' => $sales_by_cat,
				'title'  => __( 'Last month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_category&range=last_month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last month', 'woocommerce' )
				)
			);
			
			/** sbca Year */
			$menu_items[ 'sbca-year' ] = array(
				'parent' => $sales_by_cat,
				'title'  => __( 'Year', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_category&range=year' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Year', 'woocommerce' )
				)
			);
		
			/** cbd Orders Reports */
			$menu_items[ 'coupons-by-date' ] = array(
				'parent' => $order_reports,
				'title'  => __( 'Coupons by date', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=coupon_usage' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Coupons by date', 'woocommerce' )
				)
			);
				
			/** cbd 7 day */
			$menu_items[ 'cbd-7-day' ] = array(
				'parent' => $coupons_by_date,
				'title'  => __( 'Last 7 Days', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=coupon_usage&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last 7 Days', 'woocommerce' )
				)
			);
			
			/** cbd This Month */
			$menu_items[ 'cbd-this-month' ] = array(
				'parent' => $coupons_by_date,
				'title'  => __( 'This month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=coupon_usage&range=month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'This month', 'woocommerce' )
				)
			);
			
			/** cbd Last Month */
			$menu_items[ 'cbd-last-month' ] = array(
				'parent' => $coupons_by_date,
				'title'  => __( 'Last month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=coupon_usage&range=last_month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last month', 'woocommerce' )
				)
			);
			
			/** cbd Year */
			$menu_items[ 'cbd-year' ] = array(
				'parent' => $coupons_by_date,
				'title'  => __( 'Year', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=coupon_usage&range=year' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Year', 'woocommerce' )
				)
			);
			
			/** cd Orders Reports */
			$menu_items[ 'customer-download' ] = array(
				'parent' => $order_reports,
				'title'  => __( 'Customer download', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=downloads' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Customer download', 'woocommerce' )
				)
			);
			
			if ( is_plugin_active( 'woo-sales-by-country-reports/woocommerce-sales-by-country-report.php' ) ) {	
				/** cbco Orders Reports */
				$menu_items[ 'sales-by-country' ] = array(
					'parent' => $order_reports,
					'title'  => __( 'Sales by country', 'woo-sales-country-reports' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_country' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Sales by country', 'woo-sales-country-reports' )
					)
				);
					
					
				/** cbco 7 day */
				$menu_items[ 'cbco-7-day' ] = array(
					'parent' => $sales_by_country,
					'title'  => __( 'Last 7 Days', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_country&range=7day' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last 7 Days', 'woocommerce' )
					)
				);
				
				/** cbco This Month */
	
				$menu_items[ 'cbco-this-month' ] = array(
					'parent' => $sales_by_country,
					'title'  => __( 'This month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_country&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'This month', 'woocommerce' )
					)
				);
				
				/** cbco Last Month */
				$menu_items[ 'cbco-last-month' ] = array(
					'parent' => $sales_by_country,
					'title'  => __( 'Last month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_country&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last month', 'woocommerce' )
					)
				);
				
				/** cbco Year */
				$menu_items[ 'cbco-year' ] = array(
					'parent' => $sales_by_country,
					'title'  => __( 'Year', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_country&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Year', 'woocommerce' )
					)
				);
			}
			
			if ( is_plugin_active( 'woo-sales-report-for-wp-lister/woo-sales-report-for-wp-lister.php' ) ) {	
				/** sbch Orders Reports */
				$menu_items[ 'sales-by-channel' ] = array(
					'parent' => $order_reports,
					'title'  => __( 'Sales by channel', 'woo-sales-report-for-wp-lister' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_channel' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Sales by channel', 'woo-sales-report-for-wp-lister' )
					)
				);
					
				/** sbch 7 day */
				$menu_items[ 'sbch-7-day' ] = array(
					'parent' => $sales_by_channel,
					'title'  => __( 'Last 7 Days', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_channel&range=7day' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last 7 Days', 'woocommerce' )
					)
				);
				
				/** sbch This Month */
				$menu_items[ 'sbch-this-month' ] = array(
					'parent' => $sales_by_channel,
					'title'  => __( 'This month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_channel&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'This month', 'woocommerce' )
					)
				);
				
				/** sbch Last Month */
				$menu_items[ 'sbch-last-month' ] = array(
					'parent' => $sales_by_channel,
					'title'  => __( 'Last month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_channel&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last month', 'woocommerce' )
					)
				);
				
				/** sbch Year */
				$menu_items[ 'sbch-year' ] = array(
					'parent' => $sales_by_channel,
					'title'  => __( 'Year', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_channel&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Year', 'woocommerce' )
					)
				);
			}
	
			
			/** Customer Reports */
				$menu_items[ 'customer-reports' ] = array(
					'parent' => $reports,
					'title'  => __( 'Customers', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Customers', 'woocommerce' )
					)
				);
			
			/** cvsg Customer Reports */
			$menu_items[ 'customer-vs-guests' ] = array(
				'parent' => $customer_reports,
				'title'  => __( 'Customer vs. guests', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customers' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Customer vs. guests', 'woocommerce' )
				)
			);
					
			/** cvsg 7 day Customer Reports */
			$menu_items[ 'cvsg-7-day' ] = array(
				'parent' => $customer_vs_guests,
				'title'  => __( 'Last 7 Days', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customers&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last 7 Days', 'woocommerce' )
				)
			);
			
			/** cvsg This Month Customer Reports */
			$menu_items[ 'cvsg-this-month' ] = array(
				'parent' => $customer_vs_guests,
				'title'  => __( 'This month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customers&range=month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'This month', 'woocommerce' )
				)
			);
			
			/** cvsg Last Month Customer Reports */
			$menu_items[ 'cvsg-last-month' ] = array(
				'parent' => $customer_vs_guests,
				'title'  => __( 'Last month', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customers&range=last_month' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Last month', 'woocommerce' )
				)
			);
			
			/** cvsg Year Customer Reports */
			$menu_items[ 'cvsg-year' ] = array(
				'parent' => $customer_vs_guests,
				'title'  => __( 'Year', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customers&range=year' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Year', 'woocommerce' )
				)
			);
			
			/** cl Customer Reports */
			$menu_items[ 'customer-list' ] = array(
				'parent' => $customer_reports,
				'title'  => __( 'Customer list', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=customers&report=customer_list' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Customer list', 'woocommerce' )
				)
			);
					
			
			/** Stock Reports */
				$menu_items[ 'stock-reports' ] = array(
					'parent' => $reports,
					'title'  => __( 'Stock', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=stock' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Stock', 'woocommerce' )
					)
				);
				
			/** Low Stock Reports */
			$menu_items[ 'low-stock-reports' ] = array(
				'parent' => $stock_reports,
				'title'  => __( 'Low in Stock Reports', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=stock&report=low_in_stock' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Low in Stock Reports', 'woocommerce' )
				)
			);
			
			/** Out of Stock Reports */
			$menu_items[ 'out-stock-reports' ] = array(
				'parent' => $stock_reports,
				'title'  => __( 'Out of Stock Reports', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=stock&report=out_of_stock' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Out of Stock Reports', 'woocommerce' )
				)
			);
			
			/** Most Stocked Reports */
			$menu_items[ 'most-stock-reports' ] = array(
				'parent' => $stock_reports,
				'title'  => __( 'Most Stocked Reports', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=stock&report=most_stocked' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Most Stocked Reports', 'woocommerce' )
				)
			);
			
			if($enable_taxes){
					/** Taxes Reports */
					$menu_items[ 'taxes-reports' ] = array(
						'parent' => $reports,
						'title'  => __( 'Taxes', 'woocommerce' ),
						'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Taxes', 'woocommerce' )
						)
					);
				
				/** tbc Taxes Reports */
				$menu_items[ 'taxes-by-code' ] = array(
					'parent' => $taxes_reports,
					'title'  => __( 'Taxes by code', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_code' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Taxes by code', 'woocommerce' )
					)
				);
						
				/** tbc This Month Taxes Reports */
				$menu_items[ 'tbc-this-month' ] = array(
					'parent' => $taxes_by_code,
					'title'  => __( 'This month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_code&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'This month', 'woocommerce' )
					)
				);
				
				/** tbc Last Month Taxes Reports */
				$menu_items[ 'tbc-last-month' ] = array(
					'parent' => $taxes_by_code,
					'title'  => __( 'Last month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_code&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last month', 'woocommerce' )
					)
				);
				
				/** tbc Year Taxes Reports */
				$menu_items[ 'tbc-year' ] = array(
					'parent' => $taxes_by_code,
					'title'  => __( 'Year', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_code&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Year', 'woocommerce' )
					)
				);
				
				/** tbd Taxes Reports */
				$menu_items[ 'taxes-by-date' ] = array(
					'parent' => $taxes_reports,
					'title'  => __( 'Taxes by date', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_date' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Taxes by date', 'woocommerce' )
					)
				);
						
				/** tbd This Month Taxes Reports */
				$menu_items[ 'tbd-this-month' ] = array(
					'parent' => $taxes_by_date,
					'title'  => __( 'This month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_date&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'This month', 'woocommerce' )
					)
				);
				
				/** tbd Last Month Taxes Reports */
				$menu_items[ 'tbd-last-month' ] = array(
					'parent' => $taxes_by_date,
					'title'  => __( 'Last month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_date&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last month', 'woocommerce' )
					)
				);
				
				/** tbd Year Taxes Reports */
				$menu_items[ 'tbd-year' ] = array(
					'parent' => $taxes_by_date,
					'title'  => __( 'Year', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=taxes&report=taxes_by_date&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Year', 'woocommerce' )
					)
				);
			}
			
			$menu_items[ 'shop-settings' ] = array(
				'parent' => $wsmab_main,
				'id'     => $woocommerce,
				'title'  => __( 'WooCommerce settings', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'WooCommerce settings', 'woocommerce' )
				)
			);
				
			$menu_items[ 'general-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'General', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=general' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'General', 'woocommerce' )
				)
			);
				
			$menu_items[ 'product-shop-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Products', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=products' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Products', 'woocommerce' )
				)
			);
				
			$menu_items[ 'general-product-settings' ] = array(
				'parent' => $product_shop_settings,
				'title'  => __( 'General', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=products' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'General', 'woocommerce' )
				)
			);
		
			$menu_items[ 'general-product-settings' ] = array(
				'parent' => $product_shop_settings,
				'title'  => __( 'General', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=products' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'General', 'woocommerce' )
				)
			);
		
			$menu_items[ 'general-inventory-settings' ] = array(
				'parent' => $product_shop_settings,
				'title'  => __( 'Inventory', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Inventory', 'woocommerce' )
				)
			);
		
			$menu_items[ 'general-downloadable-settings' ] = array(
				'parent' => $product_shop_settings,
				'title'  => __( 'Downloadable products', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=products&section=downloadable' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Downloadable products', 'woocommerce' )
				)
			);
		
			$menu_items[ 'shipping-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Shipping', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Shipping', 'woocommerce' )
				)
			);
			
			$menu_items[ 'shipping-zones' ] = array(
				'parent' => $shipping_settings,
				'title'  => __( 'Shipping zones', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Shipping zones', 'woocommerce' )
				)
			);
		
			$menu_items[ 'shipping-options' ] = array(
				'parent' => $shipping_settings,
				'title'  => __( 'Shipping options', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=options' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Shipping options', 'woocommerce' )
				)
			);
		
			$menu_items[ 'shipping-classes' ] = array(
				'parent' => $shipping_settings,
				'title'  => __( 'Shipping classes', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Shipping classes', 'woocommerce' )
				)
			);
			
			if ( is_plugin_active( 'dhl-for-woocommerce/pr-dhl-woocommerce.php' ) ) {
				$menu_items[ 'dhl-ecommerce' ] = array(
					'parent' => $shipping_settings,
					'title'  => __( 'DHL eCommerce'),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=pr_dhl_ecomm' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'DHL eCommerce' )
					)
				);
			}
		
			$menu_items[ 'payment-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Payments', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Payments', 'woocommerce' )
				)
			);
				
			if ( is_plugin_active( 'woo-bit-payment-gateway/woocommerce-bit-payment-gateway.php' ) ) {
				
				$menu_items[ 'bit-payment-settings' ] = array(
					'parent' => $payment_settings,
					'title'  => __( 'Bit Payment Gateway' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bit_offline_gateway' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Bit Payment Gateway' )
					)
				);
					
			}
				
			$menu_items[ 'account-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Accounts & Privacy', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=account' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Accounts & Privacy', 'woocommerce' )
				)
			);
			
			/** Status */
			$menu_items[ 'status' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Status', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-status' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Status', 'woocommerce' )
				)
			);
			
			/** System Status Status */
			$menu_items[ 'system-status' ] = array(
				'parent' => $status,
				'title'  => __( 'System status', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-reports&tab=orders&range=7day' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'System status', 'woocommerce' )
				)
			);
			
			/** Tools Status */
			$menu_items[ 'tools-status' ] = array(
				'parent' => $status,
				'title'  => __( 'Tools', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-status&tab=tools' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Tools', 'woocommerce' )
				)
			);
			
			/** Logs Status */
			$menu_items[ 'logs-status' ] = array(
				'parent' => $status,
				'title'  => __( 'Logs', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-status&tab=logs' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Logs', 'woocommerce' )
				)
			);
			
			/** Scheduled Actions Status */
			$menu_items[ 'scheduled-actions-status' ] = array(
				'parent' => $status,
				'title'  => __( 'Scheduled Actions', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-status&tab=action-scheduler' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Scheduled Actions', 'woocommerce' )
				)
			);
		
			/** Display "Texes" section if enable*/
			if($enable_taxes){
				
				$menu_items[ 'taxes' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Taxes', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=tax' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Taxes', 'woocommerce' )
					)
				);
				
				$menu_items[ 'tax-options' ] = array(
					'parent' => $taxes,
					'title'  => __( 'Tax options', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=tax' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Tax options', 'woocommerce' )
					)
				);
				
				$menu_items[ 'standard-rates' ] = array(
					'parent' => $taxes,
					'title'  => __( 'Standard rates', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=tax&section=standard' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Standard rates', 'woocommerce' )
					)
				);
				
				$menu_items[ 'reduced-rate-rates' ] = array(
					'parent' => $taxes,
					'title'  => __( 'Reduced rate rates', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=tax&section=reduced-rate' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Reduced rate rates', 'woocommerce-shop-manager-admin-bar' )
					)
				);
				
				$menu_items[ 'zero-rate-rates' ] = array(
					'parent' => $taxes,
					'title'  => __( 'Zero rate rates', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=tax&section=zero-rate' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Zero rate rates', 'woocommerce-shop-manager-admin-bar' )
					)
				);
				
			}
			
			$menu_items[ 'email-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Emails', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Emails', 'woocommerce' )
				)
			);
				
			$menu_items[ 'new-order' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'New order', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_new_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'New order', 'woocommerce' )
				)
			);
			
			$menu_items[ 'cancelled-order' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Cancelled order', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_cancelled_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Cancelled order', 'woocommerce' )
				)
			);
			
			$menu_items[ 'failed-order' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Failed order', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_failed_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Failed order', 'woocommerce' )
				)
			);
			
			$menu_items[ 'order-on-hold' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Order on-hold ', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_on_hold_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Order on-hold ', 'woocommerce' )
				)
			);
			
			$menu_items[ 'processing-order' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Processing order', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_processing_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Processing order', 'woocommerce' )
				)
			);
			
				if ( get_option('wc_ast_status_shipped') == '1') {
					if( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' )  ) {
					$menu_items[ 'completed-order' ] = array(
						'parent' => $email_settings,
						'title'  => __( 'Shipped order ', 'woocommerce' ),
						'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_completed_order' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Shipped order ', 'woocommerce' )
						)
					);}
			} else {
				$menu_items[ 'completed-order' ] = array(
					'parent' => $email_settings,
					'title'  => __( 'Completed order ', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_completed_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Completed order ', 'woocommerce' )
					)
				);	
			}
			
			$menu_items[ 'refunded-order' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Refunded order', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_refunded_order' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Refunded order', 'woocommerce' )
				)
			);
			
			$menu_items[ 'customer-note' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Customer note', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_note' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Customer note', 'woocommerce' )
				)
			);
			
			$menu_items[ 'reset-password' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'Reset password', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_reset_password' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Reset password', 'woocommerce' )
				)
			);
			
			$menu_items[ 'new-account' ] = array(
				'parent' => $email_settings,
				'title'  => __( 'New account', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_new_account' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'New account', 'woocommerce' )
				)
			);
			
			if ( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {	
			
				$menu_items[ 'delivered-order' ] = array(
					'parent' => $email_settings,
					'title'  => __( 'Delivered order', 'woo-advanced-shipment-tracking' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_delivered_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Delivered order', 'woo-advanced-shipment-tracking' )
					)
				);
			
			}
			
			if ( is_plugin_active( 'woo-bit-payment-gateway/woocommerce-bit-payment-gateway.php' ) ) {	
			
				$menu_items[ 'bit-payment' ] = array(
					'parent' => $email_settings,
					'title'  => __( 'Bit Payment', 'wc-bit-payment-gateway' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=bit_payment_email' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Bit Payment', 'wc-bit-payment-gateway' )
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {	
			
				$menu_items[ 'subscription-emails' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Subscription Emails', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscription Emails', 'woocommerce-shop-manager-admin-bar' )
					)
				);
			
				$menu_items[ 'new-renewal-order' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'New Renewal Order', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_new_renewal_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'New Renewal Order', 'woocommerce-subscriptions' )
					)
				);
			
				$menu_items[ 'subscription-switched' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Subscription Switched', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_new_switch_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscription Switched', 'woocommerce-subscriptions' )
					)
				);
			
				$menu_items[ 'processing-renewal-order' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Processing Renewal order', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_processing_renewal_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Processing Renewal order', 'woocommerce-subscriptions' )
					)
				);
			
				$menu_items[ 'completed-renewal-order' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Completed Renewal Order', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_completed_renewal_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Completed Renewal Order', 'woocommerce-subscriptions' )
					)
				);
			
				$menu_items[ 'on-hold-renewal-order' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'On-hold Renewal Order', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_customer_on_hold_renewal_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'On-hold Renewal Order', 'woocommerce-subscriptions' )
					)
				);
			
				$menu_items[ 'on-hold-renewal-order' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'On-hold Renewal Order', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_customer_on_hold_renewal_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'On-hold Renewal Order', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'subscription-switch-complete' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Subscription Switch Complete', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_completed_switch_order' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscription Switch Complete', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'customer-renewal-invoice' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Customer Renewal Invoice', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_customer_renewal_invoice' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Customer Renewal Invoice', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'cancelled-subscription' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Cancelled Subscription', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_cancelled_subscription' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Cancelled Subscription', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'expired-subscription' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Expired Subscription', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_expired_subscription' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Expired Subscription', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'suspended-subscription' ] = array(
					'parent' => $subscription_emails,
					'title'  => __( 'Suspended Subscription', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=email&section=wcs_email_on_hold_subscription' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Suspended Subscription', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'subscription-reports' ] = array(
					'parent' => $reports,
					'title'  => __( 'Subscription', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscription', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'subscription-events-by-date' ] = array(
					'parent' => $subscription_reports,
					'title'  => __( 'Subscription Events by Date', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_events_by_date' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscription Events by Date', 'woocommerce-subscriptions' )
					)
				);
				
				/** sebd 7 day */
				$menu_items[ 'sebd-7-day' ] = array(
					'parent' => $subscription_events_by_date,
					'title'  => __( 'Last 7 Days', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_events_by_date&range=7day' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last 7 Days', 'woocommerce' )
					)
				);
				
				/** sebd This Month */
				$menu_items[ 'sebd-this-month' ] = array(
					'parent' => $subscription_events_by_date,
					'title'  => __( 'This month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_events_by_date&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'This month', 'woocommerce' )
					)
				);
				
				/** sebd Last Month */
				$menu_items[ 'sebd-last-month' ] = array(
					'parent' => $subscription_events_by_date,
					'title'  => __( 'Last month', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_events_by_date&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Last month', 'woocommerce' )
					)
				);
				
				/** sebd Year */
				$menu_items[ 'sebd-year' ] = array(
					'parent' => $subscription_events_by_date,
					'title'  => __( 'Year', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_events_by_date&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Year', 'woocommerce' )
					)
				);
				
				$menu_items[ 'upcoming-recurring-revenue' ] = array(
					'parent' => $subscription_reports,
					'title'  => __( 'Upcoming Recurring Revenue', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=upcoming_recurring_revenue' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Upcoming Recurring Revenue', 'woocommerce-subscriptions' )
					)
				);
				
				/** urcr 7 day */
				$menu_items[ 'urcr-7-day' ] = array(
					'parent' => $upcoming_recurring_revenue,
					'title'  => __( 'Next 7 Days', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=upcoming_recurring_revenue&range=7day' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Next 7 Days', 'woocommerce-subscriptions' )
					)
				);
				
				/** urcr Last Month */
				$menu_items[ 'urcr-last-month' ] = array(
					'parent' => $upcoming_recurring_revenue,
					'title'  => __( 'Next Month', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=upcoming_recurring_revenue&range=last_month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Next Month', 'woocommerce-subscriptions' )
					)
				);
				
				/** urcr This Month */
				$menu_items[ 'urcr-this-month' ] = array(
					'parent' => $upcoming_recurring_revenue,
					'title'  => __( 'Next 30 Days', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=upcoming_recurring_revenue&range=month' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Next 30 Days', 'woocommerce-subscriptions' )
					)
				);
				
				/** urcr Year */
				$menu_items[ 'urcr-year' ] = array(
					'parent' => $upcoming_recurring_revenue,
					'title'  => __( 'Next 12 Months', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=upcoming_recurring_revenue&range=year' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Next 12 Months', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'retention-rate' ] = array(
					'parent' => $subscription_reports,
					'title'  => __( 'Retention Rate', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=retention_rate' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Retention Rate', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'subscription-by-product' ] = array(
					'parent' => $subscription_reports,
					'title'  => __( 'Subscriptions by Product', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_by_product' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscriptions by Product', 'woocommerce-subscriptions' )
					)
				);
				
				$menu_items[ 'subscription-by-customer' ] = array(
					'parent' => $subscription_reports,
					'title'  => __( 'Subscriptions by Customer', 'woocommerce-subscriptions' ),
					'href'   => admin_url( 'admin.php?page=wc-reports&tab=subscriptions&report=subscription_by_customer' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscriptions by Customer', 'woocommerce-subscriptions' )
					)
				);
			
			}
			
			$menu_items[ 'advance-settings' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Advanced', 'woocommerce' ),
				'href'   => admin_url( 'admin.php?page=wc-settings&tab=advanced' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Advanced', 'woocommerce' )
				)
			);
			
			if ( is_plugin_active( 'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php' ) ) {
			
				$menu_items[ 'price-based-on-countries_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Zone Pricing', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=price-based-country' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Zone Pricing', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woo-product-country-base-restrictions/woocommerce-product-country-base-restrictions.php' ) ) {
		
				$menu_items[ 'woocommerce-product-country-base-restrictions_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Country Restrictions', 'woo-product-country-base-restrictions' ),
					'href'   => admin_url( 'admin.php?page=woocommerce-product-country-base-restrictions' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Country Restrictions', 'woo-product-country-base-restrictions' ),
						'class' => ''
					)
				);
		
			}
			
			if ( is_plugin_active( 'woocommerce-product-feeds/woocommerce-gpf.php' ) ) {
		
				$menu_items[ 'woocommerce-product-feeds_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Product Feeds', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=gpf' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Product Feeds', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'improved-variable-product-attributes/improved-variable-product-attributes.php' ) ) {
		
				$menu_items[ 'improved-options_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Improved Options', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=improved_options' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Product Feeds', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
		
				$menu_items[ 'subscriptions_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Subscriptions', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=subscriptions' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Subscriptions', 'woocommerce' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'woocommerce-email-control/ec-email-control.php' ) ) {
		
				$menu_items[ 'email_customizer_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=woocommerce_email_control' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'woocommerce_email_cuztomizer_with_drag_and_drop_builder/woo-email-customizer-page-builder.php' ) ) {
		
				$menu_items[ 'drag-drop_email_customizer_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Drag and Drop Email Builder', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=woo_email_customizer_page_builder' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Drag and Drop Email Builder', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'wp-html-mail-woocommerce/wp-html-mail-woocommerce.php' ) ) {
		
				$menu_items[ 'html_mail_customizer_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'WP HTML Mail Customizer', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'options-general.php?page=wp-html-mail&tab=woocommerce' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'WP HTML Mail Customizer', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'kadence-woocommerce-email-designer/kadence-woocommerce-email-designer.php' ) ) {
		
				$menu_items[ 'kadence_email_customizer_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Kadence Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => add_query_arg( array(
							'kt-woomail-customize' => '1',
							'url'                  => urlencode( add_query_arg( array( 'kt-woomail-preview' => '1' ), home_url( '/' ) ) ),
							'return'               => urlencode( Kadence_Woomail_Woo::get_email_settings_page_url() ),
						), admin_url( 'customize.php' ) ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Kadence Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'decorator-woocommerce-email-customizer/decorator.php' ) ) {
		
				$menu_items[ 'decorator_email_customizer_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Decorator Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => add_query_arg(array(
							'rp-decorator-customize'  => '1',
							'url'                  	  => urlencode(add_query_arg(array('rp-decorator-preview' => '1'), site_url('/'))),
							'return'                  => urlencode(RP_Decorator_WC::get_email_settings_page_url()),
							), admin_url('customize.php')),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Decorator Email Customizer', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'custom-order-numbers-for-woocommerce/custom-order-numbers-for-woocommerce.php' ) ) {
		
				$menu_items[ 'Custom_Order_Numbers_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Custom Order Numbers', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_order_numbers' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Custom Order Numbers', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
		
			}
		
			if ( is_plugin_active( 'nextend-facebook-connect/nextend-facebook-connect.php' ) ) {
		
				$menu_items[ 'nextend_social_login_shopparent' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Nextend Social Login', 'nextend-facebook-connect' ),
					'href'   => admin_url( 'options-general.php?page=nextend-social-login' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Nextend Social Login', 'nextend-facebook-connect' ),
						'class' => ''
					)
				);
		
			}
		
			if ( current_user_can( 'edit_pages' ) ) {
				
					$menu_items[ 'pages' ] = array(
						'parent' => $wsmab_main,
						'id'     => $wordpress,
						'title'  => __( 'Pages', 'woocommerce' ),
						'href'   => admin_url( 'edit.php?post_type=page' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Pages', 'woocommerce' )
						)
					);
				
				$menu_items[ 'all_pages' ] = array(
					'parent' => $pages,
					'title'  => __( 'All Pages' ),
					'href'   => admin_url( 'edit.php?post_type=page' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'All Pages' )
					)
				);
				
				$menu_items[ 'add_new_page' ] = array(
					'parent' => $pages,
					'title'  => __( 'Add New', 'woocommerce' ),
					'href'   => admin_url( 'post-new.php?post_type=page' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Add New', 'woocommerce' )
					)
				);
			
			}
			
			if ( current_user_can( 'edit_posts' ) ) {
				
					$menu_items[ 'posts' ] = array(
						'parent' => $wsmab_main,
						'id'     => $wordpress,
						'title'  => __( 'Posts'),
						'href'   => admin_url( 'edit.php' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Posts' )
						)
					);
			
				$menu_items[ 'all_posts' ] = array(
					'parent' => $posts,
					'title'  => __( 'All Posts' ),
					'href'   => admin_url( 'edit.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'All Posts' )
					)
				);
				
				$menu_items[ 'add_new_post' ] = array(
					'parent' => $posts,
					'title'  => __( 'Add New', 'woocommerce' ),
					'href'   => admin_url( 'post-new.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Add New', 'woocommerce' )
					)
				);
				
				$menu_items[ 'categories' ] = array(
					'parent' => $posts,
					'title'  => __( 'Categories', 'woocommerce' ),
					'href'   => admin_url( 'edit-tags.php?taxonomy=category' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Categories', 'woocommerce' )
					)
				);
				
				$menu_items[ 'tags' ] = array(
					'parent' => $posts,
					'title'  => __( 'Tags', 'woocommerce' ),
					'href'   => admin_url( 'edit-tags.php?taxonomy=post_tag' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Tags', 'woocommerce' )
					)
				);
			
			}
			
				$menu_items[ 'users' ] = array(
					'parent' => $wsmab_main,
					'id'     => $wordpress,
					'title'  => __( 'Users', 'woocommerce' ),
					'href'   => admin_url( 'users.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Users', 'woocommerce' )
					)
				);
			
			$menu_items[ 'all_users' ] = array(
				'parent' => $users,
				'title'  => __( 'All Users' ),
				'href'   => admin_url( 'users.php' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'All Users' )
				)
			);
			
			$result = count_users();
			foreach($result['avail_roles'] as $key=>$val) {
				if($key != 'none'){
					if(substr($key, 0, 3) == 'bbp'){
						$key = str_replace("bbp_","",$key);
					}
					$role = str_replace("_"," ",$key);
					$menu_items[ $role ] = array(
						'parent' => $all_users,
						'title'  => __( ucfirst($role) ).' ('.($val).')',
						'href'   => '',
						'meta'   => array(
							'target' => '',	
							'title'  => __( ucfirst($role) )
						)
					);
				}
            }
			
			$menu_items[ 'add_new_user' ] = array(
				'parent' => $users,
				'title'  => __( 'Add New', 'woocommerce' ),
				'href'   => admin_url( 'user-new.php' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Add New', 'woocommerce' )
				)
			);
			
			$menu_items[ 'user_profile' ] = array(
				'parent' => $users,
				'title'  => __( 'Your Profile', 'woocommerce' ),
				'href'   => admin_url( 'profile.php' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Your Profile', 'woocommerce' )
				)
			);
			
			$theme = wp_get_theme(); // gets the current theme
			if ( 'Flatsome Child' == $theme->name || 'Flatsome' == $theme->parent_theme ) {
				$menu_items[ 'ux-blocks' ] = array(
						'parent' => $wsmab_main,
						'id'     => $page_builder,
						'title'  => __( 'UX Blocks'),
						'href'   => admin_url( 'edit.php?post_type=blocks' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'UX Blocks' )
						)
					);
			
				$menu_items[ 'add-new-block' ] = array(
					'parent' => $ux_blocks,
					'title'  => __( 'Add New', 'woocommerce' ),
					'href'   => admin_url( 'post-new.php?post_type=blocks' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Add New', 'woocommerce' )
					)
				);
				
				$menu_items[ 'categories' ] = array(
					'parent' => $ux_blocks,
					'title'  => __( 'Categories', 'woocommerce' ),
					'href'   => admin_url( 'edit-tags.php?taxonomy=block_categories&post_type=blocks' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Categories', 'woocommerce' )
					)
				);
			}
			
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				
				$menu_items[ 'wpml' ] = array(
					'parent' => $wsmab_main,
					'id'     => $plugin_setting,
					'title'  => __( 'WPML', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/languages.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'WPML', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
				$menu_items[ 'languages' ] = array(
					'parent' => $wpml,
					'title'  => __( 'Languages', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/languages.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Languages', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
				$menu_items[ 'theme-localization' ] = array(
					'parent' => $wpml,
					'title'  => __( 'Theme and plugins localization', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/theme-localization.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Theme and plugins localization', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
				if ( is_plugin_active( 'wpml-translation-management/plugin.php' ) ) {
					$menu_items[ 'translation-management' ] = array(
						'parent' => $wpml,
						'title'  => __( 'Translation Management', 'wpml-translation-management' ),
						'href'   => admin_url( 'admin.php?page=wpml-translation-management/menu/main.php' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Translation Management', 'wpml-translation-management' ),
							'class' => ''
						)
					);
					
					$menu_items[ 'translations' ] = array(
						'parent' => $wpml,
						'title'  => __( 'Translations', 'wpml-translation-management' ),
						'href'   => admin_url( 'admin.php?page=wpml-translation-management/menu/translations-queue.php' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Translations', 'wpml-translation-management' ),
							'class' => ''
						)
					);
				}
				
				$menu_items[ 'menus-sync' ] = array(
					'parent' => $wpml,
					'title'  => __( 'WP Menus Sync', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/menu-sync/menus-sync.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'WP Menus Sync', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
				if ( is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
					$menu_items[ 'string-translation' ] = array(
						'parent' => $wpml,
						'title'  => __( 'String Translation', 'wpml-string-translation' ),
						'href'   => admin_url( 'admin.php?page=wpml-string-translation/menu/string-translation.php' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'String Translation', 'wpml-string-translation' ),
							'class' => ''
						)
					);
				}
				
				$menu_items[ 'taxonomy-translation' ] = array(
					'parent' => $wpml,
					'title'  => __( 'Taxonomy Translation', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/taxonomy-translation.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Taxonomy Translation', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
				if ( is_plugin_active( 'wpml-translation-management/plugin.php' ) ) {
					$menu_items[ 'packages' ] = array(
						'parent' => $wpml,
						'title'  => __( 'Packages', 'wpml-translation-management' ),
						'href'   => admin_url( 'admin.php?page=wpml-package-management' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Packages', 'wpml-translation-management' ),
							'class' => ''
						)
					);
				}
				
				if ( is_plugin_active( 'wpml-translation-management/plugin.php' ) ) {
					$menu_items[ 'settings' ] = array(
						'parent' => $wpml,
						'title'  => __( 'Settings', 'wpml-translation-management' ),
						'href'   => admin_url( 'admin.php?page=wpml-translation-management/menu/settings' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Settings', 'wpml-translation-management' ),
							'class' => ''
						)
					);
				}else{
					$menu_items[ 'translation-options' ] = array(
						'parent' => $wpml,
						'title'  => __( 'Settings', 'sitepress-multilingual-cms' ),
						'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/translation-options.php' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Settings', 'sitepress-multilingual-cms' ),
							'class' => ''
						)
					);
				}
				
				$menu_items[ 'support' ] = array(
					'parent' => $wpml,
					'title'  => __( 'Support', 'sitepress-multilingual-cms' ),
					'href'   => admin_url( 'admin.php?page=sitepress-multilingual-cms/menu/support.php' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Support', 'sitepress-multilingual-cms' ),
						'class' => ''
					)
				);
				
			}
			
				
			if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				
					$menu_items[ 'contact' ] = array(
						'parent' => $wsmab_main,
						'id'     => $plugin_setting,
						'title'  => __( 'Contact', 'woocommerce-shop-manager-admin-bar' ),
						'href'   => admin_url( 'admin.php?page=wpcf7' ),
						'meta'   => array(
							'target' => '',
							'title'  => __( 'Contact', 'woocommerce-shop-manager-admin-bar' ),
							'class' => ''
						)
					);
		
				$menu_items[ 'contact_forms' ] = array(
					'parent' => $contact,
					'title'  => __( 'Contact Forms', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wpcf7' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Contact Forms', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
				
				$menu_items[ 'add_new_form' ] = array(
					'parent' => $contact,
					'title'  => __( 'Add New', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wpcf7-new' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Add New', 'woocommerce' ),
						'class' => ''
					)
				);
				
				$menu_items[ 'integration' ] = array(
	
					'parent' => $contact,
					'title'  => __( 'Integration', 'woocommerce' ),
					'href'   => admin_url( 'admin.php?page=wpcf7-integration' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Integration', 'woocommerce' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php' ) ) {
			
				$menu_items[ 'woo-advanced-shipment-tracking' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ),
					'href'   => admin_url( 'admin.php?page=woocommerce-advanced-shipment-tracking' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Shipment Tracking', 'woo-advanced-shipment-tracking' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'wc-dynamic-pricing-and-discounts/wc-dynamic-pricing-and-discounts.php' ) ) {
			
				$menu_items[ 'wc-dynamic-pricing-and-discounts' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Pricing & Discounts', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=rp_wcdpd_settings' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Pricing & Discounts', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-cart-notices/woocommerce-cart-notices.php' ) ) {
			
				$menu_items[ 'woocommerce-cart-notices' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Cart Notices', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-cart-notices' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Cart Notices', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}	
			
			if ( is_plugin_active( 'woocommerce-customer-order-csv-export/woocommerce-customer-order-csv-export.php' ) ) {
			
				$menu_items[ 'woocommerce-customer-order-csv-export' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'CSV Export', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc_customer_order_csv_export' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'CSV Export', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-customer-order-csv-import/woocommerce-customer-order-csv-import.php' ) ) {
			
				$menu_items[ 'woocommerce-customer-order-csv-import' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'CSV Import Suite', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=csv_import_suite' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'CSV Import Suite', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-customer-order-xml-export-suite/woocommerce-customer-order-xml-export-suite.php' ) ) {
			
				$menu_items[ 'woocommerce-customer-order-xml-export-suite' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'XML Export Suite', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc_customer_order_xml_export_suite&tab=settings' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'XML Export Suite', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-product-csv-import-suite/woocommerce-product-csv-import-suite.php' ) ) {
			
				$menu_items[ 'woocommerce-product-csv-import-suite' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'CSV Import Suite', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=woocommerce_csv_import_suite' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'CSV Import Suite', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-smart-coupons/woocommerce-smart-coupons.php' ) ) {
			
				$menu_items[ 'woocommerce-smart-coupons' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Smart Coupons', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'edit.php?post_type=shop_coupon' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Smart Coupons', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
				
			}
			
			if ( is_plugin_active( 'woo-advanced-sales-report-email/woocommerce-advanced-sales-report-email.php' ) ) {
			
				$menu_items[ 'woocommerce-sales-report-email' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Sales Report Email', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=woocommerce-advanced-sales-report-email' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Sales Report Email', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'sms-for-woocommerce/sms-for-woocommerce.php' ) ) {
			
				$menu_items[ 'sms-for-woocommerce' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'SMS for WooCommerce', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=sms-for-woocommerce' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'SMS for WooCommerce', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			$menu_items[ 'woocommerce-admin-bar-option' ] = array(
				'parent' => $shop_settings,
				'title'  => __( 'Shop Manager Admin', 'woocommerce-shop-manager-admin-bar' ),
				'href'   => admin_url( 'admin.php?page=woocommerce_shop_manager_admin_option' ),
				'meta'   => array(
					'target' => '',
					'title'  => __( 'Shop Manager Admin', 'woocommerce-shop-manager-admin-bar' ),
					'class' => ''
				)
			);
			
			
			
			if ( is_plugin_active( 'woocommerce-chained-products/woocommerce-chained-products.php' ) ) {
			
				$menu_items[ 'woocommerce-chained-products' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Chained Products', 'woocommerce-shop-manager-admin-bar' ),
					'href'   => admin_url( 'admin.php?page=wc-settings&tab=products&section=wc_chained_products' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Chained Products', 'woocommerce-shop-manager-admin-bar' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'automatewoo/automatewoo.php' ) ) {
			
				$menu_items[ 'automatewoo' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'AutomateWoo', 'automatewoo' ),
					'href'   => admin_url( 'admin.php?page=automatewoo-dashboard' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'AutomateWoo', 'automatewoo' ),
						'class' => ''
					)
				);
			
			}
			
			if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
			
				$menu_items[ 'woocommerce-multilingual' ] = array(
					'parent' => $shop_settings,
					'title'  => __( 'Multilingual', 'woocommerce-multilingual' ),
					'href'   => admin_url( 'admin.php?page=wpml-wcml' ),
					'meta'   => array(
						'target' => '',
						'title'  => __( 'Multilingual', 'woocommerce-multilingual' ),
						'class' => ''
					)
				);
			
			}
			
			$menu_items[ 'powered-by' ] = array(
				'parent' => $wsmab_main,
				'title'  => __( 'Powered by zorem' ),
				'href'   => 'http://www.zorem.com/',
				'meta'   => array(
					'target' => 'blank',
					'title'  => __( 'Powered by zorem' ),
					'class' => 'zorem_powered_by'
				)
			);
			
			
		
			/** Loop through the menu items */
			foreach ( $menu_items as $id => $menu_item ) {
					
						/** Add in the item ID */
						$menu_item[ 'id' ] = $prefix . $id;
				
						/** Add meta target to each item where it's not already set, so links open in new window/tab */
						if ( ! isset( $menu_item[ 'meta' ][ 'target' ] ) ) {
				
							$menu_item[ 'meta' ][ 'target' ] = '_blank';
				
						}  // end if
				
						/** Add class to links that open up in a new window/tab */
						if ( '_blank' === $menu_item[ 'meta' ][ 'target' ] ) {
				
							if ( ! isset( $menu_item[ 'meta' ][ 'class' ] ) ) {
				
								$menu_item[ 'meta' ][ 'class' ] = '';
				
							}  // end if
				
							$menu_item[ 'meta' ][ 'class' ] .= $prefix . 'wcaba-new-tab';
				
						 }  // end if

						if( !isset($admin_menu[$id]) || ( isset($admin_menu[$id]) && $admin_menu[$id] != 'no' ) ){
							/** Add menu items */
							$wp_admin_bar->add_menu( $menu_item );
						}
					}	
					
					$wp_admin_bar->add_group( array(
						'parent' => $wsmab_main,
						'id'     => $wcgroup,
						'meta'   => array( 'class' => 'ab-sub-secondary' )
					) );
				
		
		}
		
		return $menu_items;
	
	}
	
	/*
	* add custom columns of Total Spend in user admin panel
	*/
	function new_total_spend_column( $contactmethods ) {
		
		$contactmethods['zorem_total_spend'] = 'Total Spend';
		return $contactmethods;
	
	}
	
	function new_modify_total_spend_column_table( $column ) {
	
		$display_total_spend = get_option('display_total_spend');	
		
		if($display_total_spend == ''){	return $column;	}
	
		$column['zorem_total_spend'] = 'Total Spend';
		return $column;
	
	}
	
	function new_modify_total_spend_row_table( $val, $column_name, $user_id ) {
	
		$currency = get_woocommerce_currency();
		switch ($column_name) {
			case 'zorem_total_spend' :
				return get_woocommerce_currency_symbol($currency) . wc_get_customer_total_spent( $user_id );
				break;
			default:
		}
		return $val;
	
	}
	
	
	/*
	* add custom columns of Order Count in user admin panel
	*/
	function new_order_count_column( $contactmethods ) {
	
		$contactmethods['zorem_order_count'] = 'Order Count';
		return $contactmethods;
	
	}
	
	function new_modify_order_count_column_table( $column ) {
	
		$display_order_count = get_option('display_order_count');	
	
		if($display_order_count == ''){	return $column;	}
			
		$column['zorem_order_count'] = 'Order Count';
		return $column;
	
	}
	
	function new_modify_order_count_row_table( $val, $column_name, $user_id ) {
	
		switch ($column_name) {
			case 'zorem_order_count' :
				return wc_get_customer_order_count( $user_id );
				break;
			default:
		}
		return $val;
	
	}
	
	
	function zorem_woocommerce_admin_bar_style(){	
	
		$wsmab_zorem_icon = plugins_url( 'assets/images/ZOREM_ICON_W-180.png', __FILE__  );
		$horizontal_scroll_orders_admin = get_option('horizontal_scroll_orders_admin');
		?>
		<style type="text/css">
			#wpadminbar .ab-top-menu > li.menupop.icon-woocommerce > .ab-sub-wrapper > .ab-submenu > .zorem_powered_by{background-image: url('<?php echo $wsmab_zorem_icon; ?>');background-repeat: no-repeat;background-position: 0.85em 50%;padding-left: 30px;background-size: contain;}
			#wpadminbar .ab-top-menu > li.menupop.icon-woocommerce > .ab-item{background-image: url('<?php echo $wsmab_zorem_icon; ?>');background-repeat: no-repeat;background-position: 0 50%;padding-left: 30px !important;background-size: 28px;}
			<?php if( $horizontal_scroll_orders_admin == 'yes' ) { ?>
				body.post-type-shop_order #posts-filter {overflow: scroll !important;width: 100% !important;}
			<?php } ?>
		</style>	
			
		<?php 
	
	}	 
	
	
	function change_login_page_logo() { 
	
		global $wp_meta_boxes;
		$bg_color = get_option('bg_color');
		$image_path = get_option('image_path');
		$logo_width = get_option('logo_width'); 
		$bottom_margin = get_option('bottom_margin');
		$form_bg_color = get_option('form_bg_color');
		$btn_color = get_option('btn_color');
		$font_color = get_option('font_color');
		$form_font_color = get_option('form_font_color');
		?>
		<style type="text/css">
			<?php if( !empty($bg_color) ) { ?>
			body.login{
				 background: <?php echo $bg_color; ?>;
			}
			<?php } ?>
			<?php if( !empty($form_bg_color) ) { ?>
				body.wp-core-ui #loginform{
					 background: <?php echo $form_bg_color; ?>;
				}
			<?php } ?>
			
			<?php if( !empty($form_font_color) ) { ?>
				body.wp-core-ui #loginform label {
					 color: <?php echo $form_font_color; ?>;
				}
			<?php } ?>
			
			<?php if( !empty($font_color) ) { ?>
				body.wp-core-ui #backtoblog a, 
				body.wp-core-ui #nav a, 
				#login_footer_note .footer-text {
					color: <?php echo $font_color; ?>;	
				}
			<?php } ?>
			
			<?php if( !empty($image_path) ) { ?>
				body.login div#login h1 a img {
					display: block;
					width: <?php echo $logo_width; ?>px;
					margin: 0 auto;
				}
				body.login div#login h1 a{
					height: auto !important;
					width: auto !important;	
					background: none;
				}
			<?php } ?>
			
			<?php if( !empty($btn_color) ) { ?>
				.wp-core-ui .button.button-large {
					background: <?php echo $btn_color; ?>;
					border-color: <?php echo $btn_color; ?>;
					box-shadow: 0 1px 0 <?php echo $btn_color; ?>;
					text-shadow: 0 -1px 1px <?php echo $btn_color; ?>, 1px 0 1px <?php echo $btn_color; ?>, 0 1px 1px <?php echo $btn_color; ?>, -1px 0 1px <?php echo $btn_color; ?>;
				}
				.wp-core-ui .button.button-large:hover,
				.wp-core-ui .button.button-large:focus,
				.wp-core-ui .button.button-large:active {
					background: <?php echo $btn_color; ?>;
					border-color: <?php echo $btn_color; ?>;
				}
			<?php } ?>
			
			<?php if( !empty($bottom_margin) || $bottom_margin == '0' ) { ?>
				body.wp-core-ui #loginform{
					 margin-top: 0;
				}
				body.login div#login h1 a {
					margin-bottom: <?php echo $bottom_margin; ?>px;
				}
			<?php } ?>
			
		</style>
		
		<?php 
		
	}
	
	function logo_headertitle($login_header_title) {
		$image_path = get_option('image_path');
		if( !empty($image_path) ) {
			return '<img src="'. $image_path.'"></a>';
		}
	}
	
	function change_loginlogo_url($url) {
		
		$image_path = get_option('image_path');
		if( !empty($image_path) ) {
			/* Get Home Url of main-site */
			$logo_url = home_url();
		
			return $logo_url;
		} else {
			return 'https://wordpress.org/';	
		}
	
	}
	
	function sma_login_page_footer() {
		
		global $wp_meta_boxes;
		$login_footer_text = get_option('login_footer_text');
		?>
		<div id="login_footer_note">
			<p class="footer-text"><?php echo $login_footer_text; ?></p>
		</div>
		<style type="text/css">
			#login_footer_note .footer-text {
				text-align: center;
				margin: 30px auto;
			}
		</style>
		<?php 
	}

}

/**
 * Returns an instance of zorem_woocommerce_advanced_shipment_tracking.
 *
 * @since 1.6.5
 * @version 1.6.5
 *
 * @return zorem_woocommerce_advanced_shipment_tracking
*/
function woo_shop_manager_admin() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new woo_shop_manager_admin();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
$GLOBALS['WC_shop_manager_admin'] = woo_shop_manager_admin();



