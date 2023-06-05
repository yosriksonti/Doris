<div class="sma_admin_sidebar">
	<div class="sma_launch sma-sidebar__section sma-btn">                    	
        <h3><?php _e('Your opinion matters to us!') ?></h3>
        <p><?php _e('If you enjoy using shop manager admin for woocommerce plugin, please take a minute to review the plugin') ?><br>
        <span><?php _e('Thanks :)') ?></span>
        </p>						
        <a href="https://wordpress.org/support/plugin/woo-shop-manager-admin-bar/reviews/#new-post" class="button sma-btn button-primary btn_large" target="_blank"><span><?php _e('Share your review &gt;&gt;') ?></span><i class="icon-angle-right"></i></a>
    </div>
	<div class="sma-sidebar__section">
        <h3><?php _e( 'More plugins by zorem' ); ?></h3>
        	<?php  $plugin_list = woo_shop_manager_admin()->get_zorem_pluginlist(); ?>	
        	<ul>
				<?php foreach($plugin_list as $plugin){ 
					if( 'Shop Manager Admin for WooCommerce' != $plugin->title ) { 
					?>
                		<li><img class="plugin_thumbnail" src="<?php echo $plugin->image_url; ?>"><a class="plugin_url" href="<?php echo $plugin->url; ?>" target="_blank"><?php echo $plugin->title; ?></a></li>
                	<?php } 
				} ?>
        </ul>
	</div>
</div>