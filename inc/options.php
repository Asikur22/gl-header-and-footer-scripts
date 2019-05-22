<?php
/**
 * Plugin Options page
 *
 * @package    Header and Footer Scripts
 * @author     Anand Kumar <anand@anandkumar.net>
 * @copyright  Copyright (c) 2013 - 20168, Anand Kumar
 * @link       http://digitalliberation.org/plugins/header-and-footer-scripts
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */ ?>
<div class="wrap">
	<h2><?php _e( 'Header and Footer Scripts - Options', 'glhfs' ); ?>
		<a class="add-new-h2" target="_blank" href="<?php echo esc_url( "http://digitalliberation.org/docs/header-and-footer-scripts/?utm_source=wpdash_hfs" ); ?>">
			<?php _e( 'Read Tutorial', 'glhfs' ); ?>
		</a>
	</h2>
	<hr/>
	
	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="postbox">
					<div class="inside">
						<form name="dofollow" action="options.php" method="post">
							<?php settings_fields( 'header-and-footer-scripts' ); ?>
							
							<h3 class="shfs-labels"><?php _e( 'Supported Content Types:', 'glhfs' ); ?></h3>
							<div class="glhfs-all-post-type">
								<?php $shfs_post_types = get_option( 'shfs_post_types', array( 'page' => '1' ) ); ?>
								<?php foreach ( get_post_types( array(
										'public' => true
								), 'objects' ) as $post_type ) : ?>
									<?php $post_type_name = $post_type->name; ?>
									<label for="checkbox-<?php echo $post_type_name; ?>">
										<input id="checkbox-<?php echo $post_type_name; ?>" name="shfs_post_types[<?php echo $post_type_name; ?>]" type="checkbox"
										       value="1"<?php
										if ( is_array( $shfs_post_types ) && in_array( $post_type_name, array_keys( $shfs_post_types ) ) ) {
											echo 'checked';
										}
										?>>
										<?php esc_html_e( $post_type->labels->name ); ?>
									</label>
								<?php endforeach; ?>
							</div>
							<p><?php _e( 'Select Post Type to Show Editor', 'glhfs' ); ?></p>
							<hr/>
							
							<h3 class="shfs-labels"><?php _e( 'Scripts in header:', 'glhfs' ); ?></h3>
							<textarea class="glhfs-textarea" id="insert_header" name="shfs_insert_header"><?php echo esc_html( get_option( 'shfs_insert_header' ) ); ?></textarea>
							<p><?php _e( 'Above script will be inserted into the <code>&lt;head&gt;</code> section.', 'glhfs' ); ?></p>
							<hr/>
							
							<h3 class="shfs-labels footerlabel"><?php _e( 'Scripts in footer:', 'glhfs' ); ?></h3>
							<textarea class="glhfs-textarea" id="shfs_insert_footer"
							          name="shfs_insert_footer"><?php echo esc_html( get_option( 'shfs_insert_footer' ) ); ?></textarea>
							<p><?php _e( 'Above script will be inserted just before <code>&lt;/body&gt;</code> tag using <code>wp_footer</code> hook.', 'glhfs' ); ?></p>
							
							<p class="submit">
								<input class="button button-primary" type="submit" name="Submit" value="<?php _e( 'Save settings', 'glhfs' ); ?>"/>
							</p>
						
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
