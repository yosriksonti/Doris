<section id="sma_content2" class="sma_tab_section">
 		<div class="sma_tab_inner_container">
			<form method="post" action="options.php" id="sma_dashboard_tab_form">
        		<?php
				$hide_all_panels = get_option('hide_all_panels', 'yes');	
				$remove_welcome_panel = get_option('remove_welcome_panel', 'yes');
				$remove_wp_events = get_option('remove_wp_events', 'yes');
				$remove_quick_draft = get_option('remove_quick_draft', 'yes');	
				$remove_dashboard_right_now = get_option('remove_dashboard_right_now', 'yes');
				$remove_dashboard_activity = get_option('remove_dashboard_activity', 'yes');
				$remove_woocommerce_dashboard_status = get_option('remove_woocommerce_dashboard_status', 'yes');
				$remove_woocommerce_reviews = get_option('remove_woocommerce_reviews', 'yes');
				
				global $wp_roles;
     			$roles = $wp_roles->get_names();
				
				?>
                <table class="form-table heading-table">
                    <tbody>
                        <tr valign="top">
                            <td>
                                <h3 style=""><?php _e( 'WordPress Dashboard Widgets', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
        		<table class="form-table">		
                    <tr>
                        <td> 
                            <fieldset class="hide_widgets">
                                <span class="mdl-list__item-secondary-action">
                                    <label id="hide-checkbox" class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="hide_all_panels">
                                        <input type="checkbox" name="hide_all_panels" id="hide_all_panels" class="mdl-switch__input" value="yes" <?php if($hide_all_panels == 'yes'){ echo 'checked'; } ?>><?php esc_html_e( 'Show all WordPress dashboard panels.', 'woocommerce-shop-manager-admin-bar' ); ?> 
                                    </label>
                                </span>
                                <span class="mdl-list__item-secondary-action">
                                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_welcome_panel">
                                        <input name="remove_welcome_panel" type="checkbox" id="remove_welcome_panel" class="mdl-switch__input" value="yes" <?php if($remove_welcome_panel == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'Welcome', 'woocommerce-shop-manager-admin-bar' ); ?></span>
                                    </label>
                                 </span>
                                 <span class="mdl-list__item-secondary-action">
                                     <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_wp_events">
                                            <input name="remove_wp_events" type="checkbox" id="remove_wp_events" class="mdl-switch__input" value="yes" <?php if($remove_wp_events == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'WordPress Events and News', 'woocommerce-shop-manager-admin-bar' ); ?></span>
                                     </label>
                                 </span>
                                 <span class="mdl-list__item-secondary-action">
                                     <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_quick_draft">
                                            <input name="remove_quick_draft" type="checkbox" id="remove_quick_draft" class="mdl-switch__input" value="yes" <?php if($remove_quick_draft == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'Quick Draft', 'woocommerce-shop-manager-admin-bar' ); ?></span>
                                     </label>
								</span>
                                <span class="mdl-list__item-secondary-action">
                                	<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_dashboard_right_now">
                                    <input name="remove_dashboard_right_now" type="checkbox" id="remove_dashboard_right_now" class="mdl-switch__input" value="yes" <?php if($remove_dashboard_right_now == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'At a Glance', 'woocommerce-shop-manager-admin-bar' ); ?></span>
                             		</label>
                                </span>
                                <span class="mdl-list__item-secondary-action">
                                	<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_dashboard_activity">
                                    <input name="remove_dashboard_activity" type="checkbox" id="remove_dashboard_activity" class="mdl-switch__input" value="yes" <?php if($remove_dashboard_activity == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'Activity', 'woocommerce-shop-manager-admin-bar' ); ?></span>
                             		</label>
                                </span>
                             <?php
                    			if ( class_exists( 'WooCommerce' ) || class_exists( 'Woocommerce' ) ) { ?>
                                <span class="mdl-list__item-secondary-action">
                                	<label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_woocommerce_dashboard_status">
                                        <input name="remove_woocommerce_dashboard_status" type="checkbox" id="remove_woocommerce_dashboard_status" class="mdl-switch__input" value="yes" <?php if($remove_woocommerce_dashboard_status == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'WooCommerce status', 'woocommerce' ); ?></span>
                                 	</label>
                                </span>
                                <span class="mdl-list__item-secondary-action">
                                     <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect panel-checkbox" for="remove_woocommerce_reviews">
                                            <input name="remove_woocommerce_reviews" type="checkbox" id="remove_woocommerce_reviews" class="mdl-switch__input" value="yes" <?php if($remove_woocommerce_reviews == 'yes'){ echo 'checked'; } ?>><span><?php esc_html_e( 'WooCommerce recent reviews', 'woocommerce' ); ?></span>
                                     </label>
                                 </span>
                                 <?php } ?>
                            </fieldset>
                         	
                        </td>
                    </tr>
                </table>
                 <div class="submit sma-btn">								
                    <button name="save" class="button-primary dashboard-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                    <div class="spinner" style="float:none;"></div>
                    <?php wp_nonce_field( 'dashboard_form_action', 'dashboard_form_nonce_field' ); ?>
                    <input type="hidden" name="action" value="sma_dashboard_settings_form_update">
				</div>	
       		</form>
		</div>
        <?php include 'sma_admin_sidebar.php';?>
</section>

  
