<section id="sma_content3" class="sma_tab_section">
 		<div class="sma_tab_inner_container">
			<form method="post" action="options.php" id="sma_login_tab_form">
        		<?php
				$image_path = get_option('image_path');				
				$logo_width = get_option('logo_width');
				$bottom_margin = get_option('bottom_margin');
				$bg_color = get_option('bg_color');
				$font_color = get_option('font_color');
				$form_font_color = get_option('form_font_color');
				$form_bg_color = get_option('form_bg_color');
				$btn_color = get_option('btn_color');
				$login_footer_text = get_option('login_footer_text');
				?>
                <table class="form-table heading-table">
                    <tbody>
                        <tr valign="top">
                            <td>
                                <h3 style=""><?php _e( 'Login Page Customize', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
                            </td>
                        </tr>
                    </tbody>
				</table>
        		<table class="form-table">		
                    <tr>
                       <th scope="row"><?php esc_html_e( 'Upload Logo', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'change custom logo in admin login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td>
                        	<fieldset>
                                <input type="text" name="image_path" class='image_path textfield-media' placeholder='Image' value='<?php echo $image_path?>' id="image_path"/>
                                <input type='hidden' name='image_id' class='image_id' placeholder="Image" value='' id='image_id' style=""/>
                                <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload' , 'woocommerce-shop-manager-admin-bar'); ?>" />
								 <?php if( !empty($image_path) ){ ?>
                                     <div class="thumbnail sma-thumbnail-image">				
                                        <img src="<?php echo $image_path; ?>" id="sma_thumbnail" draggable="false" alt="">
                                        <input id="remove" type="button" class="button" value="<?php _e( 'Remove' , 'woocommerce'); ?>" />
                                    </div>
                                <?php } else { ?>
									<div class="thumbnail sma-thumbnail-image" style="display:none;">			
                                        <img src="" draggable="false" id="sma_thumbnail" alt=""/>
                                        <input id="remove" type="button" class="button" value="<?php _e( 'Remove' , 'woocommerce'); ?>" />
                                    </div>
									<?php }?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Logo Width', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'set custom logo width and maximum limit of width is 320px.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="logo_width" type="number" max="320" id="logo_width" value="<?php echo $logo_width?>"class="regular-text logo-size ltr"> px
                            </fieldset>
                        </td>
                    </tr>
                     <tr>
                        <th scope="row"><?php esc_html_e( 'Logo Bottom Margin', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Set Bottom margin of custom logo.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="bottom_margin" type="number" id="bottom_margin" value="<?php echo $bottom_margin?>"class="regular-text logo-size ltr"> px
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Background color', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Change background color of login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="bg_color" type="text" id="bg_color" value="<?php echo $bg_color?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Font Color', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Change font color in login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                               <input name="font_color" type="text" id="font_color" value="<?php echo $font_color?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="new_admin_email"><?php esc_html_e( 'Login Page Footer Text', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Add custom text to the footer of the login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></label></th>
                        <td>
                            <input name="login_footer_text" type="text" id="login_footer_text" value="<?php echo $login_footer_text; ?>" class="regular-text ltr">
                        </td>
                    </tr>	
                    </table>
                    <div class="submit sma-btn">								
                        <button name="save" class="button-primary login-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                        <div class="spinner" style="float:none;"></div>
                        <?php wp_nonce_field( 'login_form_action', 'login_form_nonce_field' ); ?>
                        <input type="hidden" name="action" value="sma_login_settings_form_update">
                    </div>	
                    <table class="form-table heading-table">
                        <tbody>
                            <tr valign="top">
                                <td>
                                    <h3 style=""><?php _e( 'Login Form Customize', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Login Box Label Color', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Change label font color in login form in login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="form_font_color" type="text" id="form_font_color" value="<?php echo $form_font_color?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Login Box Button Color', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Change login buttom color in login form in login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="btn_color" type="text" id="btn_color" value="<?php echo $btn_color?>">
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Login Box Background Color', 'woocommerce-shop-manager-admin-bar' ); ?><span class="woocommerce-help-tip tipTip" title="<?php esc_html_e( 'Change background color of login form in login page.', 'woocommerce-shop-manager-admin-bar' ); ?>"></span></th>
                        <td> 
                            <fieldset>
                                <input name="form_bg_color" type="text" id="form_bg_color" value="<?php echo $form_bg_color?>">
                            </fieldset>
                        </td>
                    </tr>
                    </table>
                    <div class="submit sma-btn">								
                        <button name="save" class="button-primary login-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                        <div class="spinner" style="float:none;"></div>
                        <?php wp_nonce_field( 'login_form_action', 'login_form_nonce_field' ); ?>
                        <input type="hidden" name="action" value="sma_login_settings_form_update">
                    </div>
       		</form> 
		</div>
        <?php include 'sma_admin_sidebar.php';?>
</section>


