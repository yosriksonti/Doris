<section id="sma_content4" class="sma_tab_section">
 		<div class="sma_tab_inner_container">
			<form method="post" action="options.php" id="sma_woocommerce_tab_form">
        		<?php
				$processing_order_count = get_option('processing_order_count');
				$display_order_count = get_option('display_order_count');	
				$display_total_spend = get_option('display_total_spend');
				$horizontal_scroll_orders_admin = get_option('horizontal_scroll_orders_admin');
				?>
                <table class="form-table heading-table">
                    <tbody>
                        <tr valign="top">
                            <td>
                                <h3 style=""><?php _e( 'WooCommerce Options', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
        		<table class="form-table">		
                    <?php 
                    if ( class_exists( 'WooCommerce' ) || class_exists( 'Woocommerce' ) ) { ?>
                        <tr>
                            <td class="forminp"> 
                        		<span class="mdl-list__item-secondary-action"> 
                                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="display_total_spend">
                                        <input name="display_total_spend" type="checkbox" id="display_total_spend" class="mdl-switch__input" value="yes" <?php if($display_total_spend == 'yes'){ echo 'checked'; } ?>>
                                    </label>
								</span>
                            </td>
                            <th scope="row"><?php esc_html_e( 'Add Total Spend Column to users admin', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Enable this option to add a "Total Spend" column in users admin.
', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        </tr>
                        <tr>
                            <td class="forminp"> 
                        		<span class="mdl-list__item-secondary-action"> 
                                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="display_order_count">
                                        <input name="display_order_count" type="checkbox" id="display_order_count" class="mdl-switch__input" value="yes" <?php if($display_order_count == 'yes'){ echo 'checked'; } ?>>
                                    </label>
								</span>
                            </td>
                            <th scope="row"><?php esc_html_e( 'Add Order Count Column to users admin', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Enable this option to add "Orders Count" column in users admin
', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        </tr>
                        <tr>
                            <td class="forminp"> 
                        		<span class="mdl-list__item-secondary-action"> 
                                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="processing_order_count">
                                        <input name="processing_order_count" type="checkbox" id="processing_order_count" class="mdl-switch__input"value="yes" <?php if($processing_order_count == 'yes'){ echo 'checked'; } ?>>
                                    </label>
								</span>
                            </td>
                            <th scope="row"><?php esc_html_e( 'Add order counts for order status', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Enable this option to display order counts for order status.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        </tr>
                        <tr>
                            <td class="forminp"> 
                        		<span class="mdl-list__item-secondary-action"> 
                                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="horizontal_scroll_orders_admin">
                                        <input name="horizontal_scroll_orders_admin" type="checkbox" id="horizontal_scroll_orders_admin" class="mdl-switch__input"value="yes" <?php if($horizontal_scroll_orders_admin == 'yes'){ echo 'checked'; } ?>>
                                    </label>
								</span>
                            </td>
                            <th scope="row"><?php esc_html_e( 'Horizontal scroll in orders admin', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Enable this option to add a Horizontal scroll in orders admin.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        </tr>				
                    <?php }
                    ?>		
                </table>
                <div class="submit sma-btn">								
                    <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                    <div class="spinner" style="float:none;"></div>
                    <?php wp_nonce_field( 'wc_form_action', 'wc_form_nonce_field' ); ?>
                    <input type="hidden" name="action" value="sma_wc_settings_form_update">
                </div>	
       		</form> 
		</div>
       <?php include 'sma_admin_sidebar.php';?> 
</section>