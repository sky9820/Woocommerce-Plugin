<?php
/**
 * WC Latest Products Plugin
 *
 * @package   WC_Latest_Products_Plugin
 * @author    Aakash Sharma
 * @license   GPL-2.0+
 * @link      https://github.com/GaryJones/move-floating-social-bar-in-genesis
 * @copyright 2023 Aakash Sharma
 *
 * @wordpress-plugin
 * Plugin Name:       WC Latest Products Widget Plugin
 * Plugin URI:        https://github.com/GaryJones/move-floating-social-bar-in-genesis
 * Description:       WC Latest Products Plugin
 * Version:           1.0.0
 * Author:            Aakash Sharma
 * Author URI:        https://github.com/GaryJones/move-floating-social-bar-in-genesis
 * Text Domain:       wc-latest-products
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
add_filter('the_content', 'do_shortcode');
global $woocommerce;

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {  // Check If WooCommerce is installed, then register the Widget
	
   // The widget class
	class WC_Latest_Product_Widget extends WP_Widget {

		// Main constructor
		public function __construct() {
			
			parent::__construct(
				'wc_latest_product_widget',
				__( 'WCLP Latest Products List', 'wc-latest-products' ),
				array(
					'customize_selective_refresh' => true,
				)
			);
			//$plugin_url = plugin_dir_url( __FILE__ );
			wp_enqueue_style( 'wclp_style',  plugin_dir_url( __FILE__ ) . "/css/style.css");
			
		}

		// The widget form (for the backend )
		public function form( $instance ) {

			// Set widget defaults
			$defaults = array(
				'title'    => 'Wiget Heading',
				'text'     => '4'
			);
			
			// Parse current settings with defaults
			extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

			<?php // Widget Title ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'wc-latest-products' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>

			<?php // Text Field ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Limit:', 'wc-latest-products' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" />
			</p>
	 
		<?php }

		// Update widget settings
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
			$instance['text']     = isset( $new_instance['text'] ) ? wp_strip_all_tags( $new_instance['text'] ) : '';
			return $instance;
		}

		// Display the widget
		public function widget( $args, $instance ) {

			extract( $args );

			// Check the widget options
			$title    = !empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
			$text     = !empty( $instance['text'] ) ? $instance['text'] : '4';
			

			// WordPress core before_widget hook (always include )
			echo $before_widget;

		   // Display the widget
		   echo '<div class="widget-text product box wp_widget_plugin_box '.$text.'">';

				if ( $title ) {
					echo $before_title . $title . $after_title;
				}
			
				$args = array(
					'post_type' => 'product',
					'stock' => 1,
					'posts_per_page' => $text,
					'orderby' =>'date',
					'order' => 'DESC' 
					);
				$loop = new WP_Query( $args );
				while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
				<div class="wclp-product product_<?php  echo $loop->post->ID; ?>">
					<a id="id-<?php the_id(); ?>" class="product-link" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php 
						if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Image Placeholder" width="65px" height="115px" />'; ?>
						<h3><?php the_title(); ?></h3>
						<span class="price"><?php echo $product->get_price_html(); ?></span>
					</a>
					<?php woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>
				</div><!-- /span3 -->

				<?php endwhile; wp_reset_query(); 
				
			echo '</div>';

			echo $after_widget;

		}

	}

	// Register the widget
	function wclp_register_widget() {
		register_widget( 'WC_Latest_Product_Widget' );
	}
	add_action( 'widgets_init', 'wclp_register_widget' );
	
	/**
	Adding custom shortcode for showing products
	*/
	add_shortcode('wclp_show_product', 'wclp_show_product_func');
	function wclp_show_product_func($atts) {
		//ob_start();
		$default = array(
			'title' => '',
			'limit' => '4'
		);
		$attr = shortcode_atts($default, $atts);
		$shortcodeContent = '';
		echo  '<div class="widget-text product box wp_widget_plugin_box '.$attr['limit'].'">';

			if ( $attr['title'] ) {
				echo  '<h2>'.$attr['title'].'</h2>';
			}
		
			$args = array(
				'post_type' => 'product',
				'stock' => 1,
				'posts_per_page' => $attr['limit'],
				'orderby' =>'date',
				'order' => 'DESC' 
				);
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post(); global $product; 
			echo  '<div class="wclp-product product_'.$loop->post->ID.'">';
			echo  '<a id="id-'.get_the_id().'" class="product-link" href="'.get_the_permalink().'" title="'.get_the_title().'">';
					
			if (has_post_thumbnail( $loop->post->ID )){ 
				echo  get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); 
			}else{
				echo  '<img src="'.woocommerce_placeholder_img_src().'" alt="Image Placeholder" width="65px" height="115px" />';
			}
			echo  '<h3>'.get_the_title().'</h3>';
			echo  '<span class="price">'.$product->get_price_html().'</span>';
			echo  '</a>';
			//$shortcodeContent .= '<div>'.woocommerce_template_loop_add_to_cart( $loop->post, $product ).'</div>';
			woocommerce_template_loop_add_to_cart( $loop->post, $product );
			echo  '</div>';

			endwhile; wp_reset_query(); 
			
		echo  '</div>';
		//$out = ob_get_clean();
		
		//return $shortcodeContent;
	}
	
} else {
    // Show admin notice if woocommerce is not active
	function wpb_admin_notice_warn() {
		echo '<div class="notice notice-warning is-dismissible">
		  <p>Important: WooCommerce Plugin seems not Activated. This plugin <strong>(WC Latest Products Widget Plugin)</strong> only works when WooCommerce should be Installed/Activated.</p>
		  </div>'; 
	}
	add_action( 'admin_notices', 'wpb_admin_notice_warn' );
}


