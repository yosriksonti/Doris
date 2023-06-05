<section id="sma_content1" class="sma_tab_section">
 		<div class="sma_tab_inner_container">
			<form method="post" id="sma_general_tab_form">
				<?php
                $admin_bar_backend = get_option('admin_bar_backend', 'yes');
                $dashboard_footer_text = get_option('dashboard_footer_text');	
                ?>
               <table class="form-table heading-table">
				<tbody>
					<tr valign="top">
						<td>
							<h3 style=""><?php _e( 'General Setting', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
						</td>
					</tr>
				 </tbody>
				</table>
				<table class="form-table">
                    <tr>
                        <th scope="row" class="titledesc"><label for=""><?php esc_html_e( 'Display shop manager menu in Admin toolbar
', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Disable this option to hide the shop manager menu from the WordPress admin.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></label></th>
                        <td class="forminp"> 
                        	<span class="mdl-list__item-secondary-action">
								 <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="admin_bar_backend">
                                    <input name="admin_bar_backend" type="checkbox" id="admin_bar_backend" class="mdl-switch__input" value="yes" <?php if($admin_bar_backend == 'yes'){ echo 'checked'; } ?> >
                                    <span class="slider round"></span>
                                </label>
							</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="titledesc"><label for="new_admin_email"><?php esc_html_e( 'Admin Footer Text', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Add Custom admin footer text to WordPress admin.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></label></th>
                        <td class="forminp">
                        	<fieldset>
								<input class="input-text regular-input ltr" type="text" name="dashboard_footer_text" id="dashboard_footer_text" style="" value="<?php echo $dashboard_footer_text; ?>">
							</fieldset>
                        </td>
                    </tr>			
                </table>	
                <div class="submit sma-btn">								
                    <button name="save" class="button-primary general-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                    <div class="spinner" style="float:none;"></div>
                    <?php wp_nonce_field( 'general_form_action', 'general_form_nonce_field' ); ?>
                    <input type="hidden" name="action" value="sma_general_settings_form_update">
				</div>		
            </form>
		</div>
       	<?php include 'sma_admin_sidebar.php';?>
 </section>