<section id="sma_content5" class="sma_tab_section">
 		<div class="sma_tab_inner_container">
			<form method="post" action="options.php" id="sma_admin_menu_tab_form">
        		<?php
				$admin_menu = get_option('sma_hide_admin_menu' , '1');
				$menu_items = woo_shop_manager_admin()->wsmab_zorem_woocommerce_admin_bar_menu();
				?>
                <table class="form-table heading-table">
                    <tbody>
                        <tr valign="top">
                            <td>
                                <h3 style=""><?php _e( 'Admin Bar Menu Items Visibility', 'woocommerce-shop-manager-admin-bar' ); ?></h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
                  <div class="mdl-tabs__tab-bar">
                      <a href="#woocommerce-menu" class="mdl-tabs__tab cbr-sub-menu is-active"><?php esc_html_e( 'WooCommerce', 'woocommerce' ); ?></a>
                      <a href="#wordpress-menu" class="mdl-tabs__tab cbr-sub-menu"><?php esc_html_e( 'Wordpress', 'defualt' ); ?></a>
                      <?php $theme = wp_get_theme(); // gets the current theme
						if ( 'Flatsome Child' == $theme->name || 'Flatsome' == $theme->parent_theme ) { ?>
                      		<a href="#page-builder-menu" class="mdl-tabs__tab cbr-sub-menu"><?php esc_html_e( 'Page Builders', 'woocommerce-shop-manager-admin-bar' ); ?></a>
                      <?php } ?>
                      <a href="#plugins-menu" class="mdl-tabs__tab cbr-sub-menu"><?php esc_html_e( 'Plugin', 'defualt' ); ?></a>
                  </div>
                <table class="form-table mdl-tabs__panel is-active" id="woocommerce-menu">
                <?php foreach($menu_items as $key=>$value ) { ?>
                	<?php if ( $value['id'] == 'woocommerce' ) { ?>
                	<tr>                    	
                        <td class="forminp"> 
                                    <span class="mdl-list__item-secondary-action"> 
                                        <label id="<?php echo $key;?>-checkbox" class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?php echo $key;?>">
                                        	<input type="hidden" name="admin_menu[<?php echo $key;?>]" value="no">
                                        	<input name="admin_menu[<?php echo $key;?>]" type="checkbox" id="<?php echo $key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$key]) && $admin_menu[$key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?> ><?php esc_html_e( 'Show' ); ?> <?php echo $value['title'] ?> <?php esc_html_e( 'Menu' ); ?> 
                                        </label>
                                    </span>
                                    
                                   <?php foreach($menu_items as $child_key=>$child_value ) { 
								   	if ( $child_value['parent'] == 'ddw-woocommerce-'.$key) { ?>
                                    	<span class="mdl-list__item-secondary-action">
                                            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect <?php echo $key;?>-checkbox" style="margin-left: 20px !important;" for="<?php echo $child_key;?>">
                                            	<input type="hidden" name="admin_menu[<?php echo $child_key;?>]" value="no">
                                                <input type="checkbox" name="admin_menu[<?php echo $child_key;?>]" id="<?php echo $child_key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$child_key]) && $admin_menu[$child_key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?>><?php esc_html_e( $child_value['title'], 'woocommerce-shop-manager-admin-bar' ); ?>
                                            </label>
                                        </span>
									<?php }
								   }
								   ?>                                   
                            </fieldset>
                        </td>
                    </tr>
					<?php } ?>
                <?php  } ?>
                </table>
                <table class="form-table mdl-tabs__panel" id="wordpress-menu">
                <?php foreach($menu_items as $key=>$value ) { ?>
                	<?php if ( $value['id'] == 'wordpress' ) { ?>
                	<tr>                    	
                        <td class="forminp"> 
                                    <span class="mdl-list__item-secondary-action"> 
                                        <label id="<?php echo $key;?>-checkbox" class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?php echo $key;?>">
                                        	<input type="hidden" name="admin_menu[<?php echo $key;?>]" value="no">
                                        	<input name="admin_menu[<?php echo $key;?>]" type="checkbox" id="<?php echo $key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$key]) && $admin_menu[$key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?> ><?php esc_html_e( 'Show' ); ?> <?php echo $value['title'] ?> <?php esc_html_e( 'Menu' ); ?> 
                                        </label>
                                    </span>
                                    
                                   <?php foreach($menu_items as $child_key=>$child_value ) { 
								   	if ( $child_value['parent'] == 'ddw-woocommerce-'.$key) { ?>
                                    	<span class="mdl-list__item-secondary-action">
                                            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect <?php echo $key;?>-checkbox" style="margin-left: 20px !important;" for="<?php echo $child_key;?>">
                                            	<input type="hidden" name="admin_menu[<?php echo $child_key;?>]" value="no">
                                                <input type="checkbox" name="admin_menu[<?php echo $child_key;?>]" id="<?php echo $child_key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$child_key]) && $admin_menu[$child_key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?>><?php esc_html_e( $child_value['title'], 'woocommerce-shop-manager-admin-bar' ); ?>
                                            </label>
                                        </span>
									<?php }
								   }
								   ?>                                   
                            </fieldset>
                        </td>
                    </tr>
					<?php } ?>
                <?php  } ?>
                </table>
				<?php $theme = wp_get_theme(); // gets the current theme
					if ( 'Flatsome Child' == $theme->name || 'Flatsome' == $theme->parent_theme ) { ?>
               			<table class="form-table mdl-tabs__panel" id="page-builder-menu">
                <?php foreach($menu_items as $key=>$value ) { ?>
                	<?php if ( $value['id'] == 'page-builder' ) { ?>
                	<tr>                    	
                        <td class="forminp"> 
                                    <span class="mdl-list__item-secondary-action"> 
                                        <label id="<?php echo $key;?>-checkbox" class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?php echo $key;?>">
                                        	<input type="hidden" name="admin_menu[<?php echo $key;?>]" value="no">
                                        	<input name="admin_menu[<?php echo $key;?>]" type="checkbox" id="<?php echo $key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$key]) && $admin_menu[$key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?> ><?php esc_html_e( 'Show' ); ?> <?php echo $value['title'] ?> <?php esc_html_e( 'Menu' ); ?> 
                                        </label>
                                    </span>
                                    
                                   <?php foreach($menu_items as $child_key=>$child_value ) { 
								   	if ( $child_value['parent'] == 'ddw-woocommerce-'.$key) { ?>
                                    	<span class="mdl-list__item-secondary-action">
                                            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect <?php echo $key;?>-checkbox" style="margin-left: 20px !important;" for="<?php echo $child_key;?>">
                                                <input type="hidden" name="admin_menu[<?php echo $child_key;?>]" value="no">
                                                <input type="checkbox" name="admin_menu[<?php echo $child_key;?>]" id="<?php echo $child_key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$child_key]) && $admin_menu[$child_key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?>><?php esc_html_e( $child_value['title'], 'woocommerce-shop-manager-admin-bar' ); ?>
                                            </label>
                                        </span>
									<?php }
								   }
								   ?>                                   
                            </fieldset>
                        </td>
                    </tr>
					<?php } ?>
                <?php  } ?>
                </table>
                	<?php } ?>
                <table class="form-table mdl-tabs__panel" id="plugins-menu">
                <?php foreach($menu_items as $key=>$value ) { ?>
                	<?php if ( $value['id'] == 'plugin-setting' ) { ?>
                	<tr>                    	
                        <td class="forminp"> 
                                    <span class="mdl-list__item-secondary-action"> 
                                        <label id="<?php echo $key;?>-checkbox" class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?php echo $key;?>">
                                        	<input type="hidden" name="admin_menu[<?php echo $key;?>]" value="no">
                                        	<input name="admin_menu[<?php echo $key;?>]" type="checkbox" id="<?php echo $key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$key]) && $admin_menu[$key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?> ><?php esc_html_e( 'Show' ); ?> <?php echo $value['title'] ?> <?php esc_html_e( 'Menu' ); ?> 
                                        </label>
                                    </span>
                                    
                                   <?php foreach($menu_items as $child_key=>$child_value ) { 
								   	if ( $child_value['parent'] == 'ddw-woocommerce-'.$key) { ?>
                                    	<span class="mdl-list__item-secondary-action">
                                            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect <?php echo $key;?>-checkbox" style="margin-left: 20px !important;" for="<?php echo $child_key;?>">
                                            	<input type="hidden" name="admin_menu[<?php echo $child_key;?>]" value="no">
                                                <input type="checkbox" name="admin_menu[<?php echo $child_key;?>]" id="<?php echo $child_key;?>" class="mdl-switch__input" value="yes" <?php if((isset($admin_menu[$child_key]) && $admin_menu[$child_key] == 'yes')|| $admin_menu == '1'){ echo 'checked'; } ?>><?php esc_html_e( $child_value['title'], 'woocommerce-shop-manager-admin-bar' ); ?>
                                            </label>
                                        </span>
									<?php }
								   }
								   ?>                                   
                            </fieldset>
                        </td>
                    </tr>
					<?php } ?>
                <?php  } ?>
                </table>
                </div>
                <div class="submit sma-btn">								
                    <button name="save" class="button-primary menu-save-button" type="submit" value="Save changes"><?php _e( 'Save Changes', 'woocommerce-shop-manager-admin-bar' ); ?></button>
                    <div class="spinner" style="float:none;"></div>
                    <?php wp_nonce_field( 'admin_menu_form_action', 'admin_menu_form_nonce_field' ); ?>
                    <input type="hidden" name="action" value="sma_admin_menu_settings_form_update">
                </div>
       		</form>
		</div>
        <?php include 'sma_admin_sidebar.php';?>
</section>

<?php foreach ($menu_items as $key=>$value) { 
  if( $value['parent'] == 'wsmab_main' ) {
?>
<script>
/* checkbox event */
jQuery(document).on("click", "#<?php echo $key;?>-checkbox.is-upgraded input#<?php echo $key;?>", function(){
    if (jQuery(this).is(':checked') ) {
        jQuery('label.<?php echo $key;?>-checkbox.is-upgraded').addClass('is-checked');
		jQuery('label.<?php echo $key;?>-checkbox.is-upgraded input').prop('checked', true);
    } else {
        jQuery('label.<?php echo $key;?>-checkbox.is-upgraded').removeClass('is-checked');
		jQuery('label.<?php echo $key;?>-checkbox.is-upgraded input').prop('checked', false);
    }
});

</script>
<?php } } ?>