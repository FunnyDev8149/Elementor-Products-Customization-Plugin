<?php

/**
 * Funny_Product_Data_Addons class
 *
 * @version 1.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Funny_Product_Data_Addons' ) ) {
	class Funny_Product_Data_Addons {

		public function __construct() {
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ), 101 );
			add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_data_panel' ), 99 );

			// Save 'Funny Extra Options'
			add_action( 'wp_ajax_Funny_save_product_addon_options', array( $this, 'save_extra_options' ) );
			add_action( 'wp_ajax_nopriv_Funny_save_product_addon_options', array( $this, 'save_extra_options' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1001 );
		}

		public function add_product_data_tab( $tabs ) {
			$tabs['Funny_data_addon'] = array(
				'label'    => esc_html__( 'Funny Extra Options', 'Funny' ),
				'target'   => 'Funny_data_addons',
				'priority' => 90,
			);
			return $tabs;
		}

		public function add_product_data_panel() {
			global $thepostid;

			?>
			<div id="Funny_data_addons" class="panel woocommerce_options_panel wc-metaboxes-wrapper hidden">
				<div class="wc-metaboxes">
					<div class="options-group">
						<?php
						// Exclusive
						$Funny_exclusive = get_post_meta( $thepostid, 'Funny_exclusive', true );
						?>
						<p class="form-field">
							<label><?php esc_html_e( 'Exclusive', 'Funny' ); ?></label>
							<input type="checkbox" class="checkbox" style="" name="Funny_exclusive" id="Funny_exclusive" <?php echo esc_attr( $Funny_exclusive ) == 'true' ? 'checked' : ''; ?>>
						</p>

						<?php
						// Learn more Link
						$Funny_learn_more_link = get_post_meta( $thepostid, 'Funny_learn_more_link', true );
						?>
						<p class="form-field">
							<label><?php esc_html_e( 'Learn More Link', 'Funny' ); ?></label>
							<input type="text" id="Funny_learn_more_link" name="Funny_learn_more_link" value="<?php echo esc_attr( $Funny_learn_more_link ); ?>" />
							<?php echo wc_help_tip( esc_html__( 'Add custom link for each product', 'Funny' ) ); ?>
						</p>

						<?php
						// Background Image
						$Funny_background_image_id = get_post_meta( $thepostid, 'Funny_background_image', true );
						if ( $Funny_background_image_id ) {
							$Funny_background_image = wp_get_attachment_image_src( $Funny_background_image_id, 'medium' )[0];
						} else {
							$Funny_background_image_id = '';
							$Funny_background_image    = wc_placeholder_img_src( 'medium' );
						}
						?>
						<p class="form-field">
							<label><?php esc_html_e( 'Background Image', 'Funny' ); ?></label>
							<img src="<?php echo esc_url( $Funny_background_image ); ?>" alt="<?php esc_attr_e( 'Thumbnail Preview', 'Funny' ); ?>" width="300" height="300" />
							<input class="upload_image_url" id="Funny_background_image_id" type="hidden" value="<?php echo esc_attr( $Funny_background_image_id ); ?>" />
							<span style="display: block;">
								<button class="button_upload_image button"><?php esc_html_e( 'Upload/Add image', 'Funny' ); ?></button>
								<button class="button_remove_image button"><?php esc_html_e( 'Remove image', 'Funny' ); ?></button>
							</span>
						</p>

						<?php
						// Background Image
						$Funny_offer_image_id = get_post_meta( $thepostid, 'Funny_offer_image', true );
						if ( $Funny_offer_image_id ) {
							$Funny_offer_image = wp_get_attachment_image_src( $Funny_offer_image_id, 'thumbnail' )[0];
						} else {
							$Funny_offer_image_id = '';
							$Funny_offer_image    = wc_placeholder_img_src( 'thumbnail' );
						}
						?>
						<p class="form-field">
							<label><?php esc_html_e( 'Offer Image', 'Funny' ); ?></label>
							<img src="<?php echo esc_url( $Funny_offer_image ); ?>" alt="<?php esc_attr_e( 'Thumbnail Preview', 'Funny' ); ?>" width="150" height="150" />
							<input class="upload_image_url" id="Funny_offer_image_id" type="hidden" value="<?php echo esc_attr( $Funny_offer_image_id ); ?>" />
							<span style="display: block;">
								<button class="button_upload_image button"><?php esc_html_e( 'Upload/Add image', 'Funny' ); ?></button>
								<button class="button_remove_image button"><?php esc_html_e( 'Remove image', 'Funny' ); ?></button>
							</span>
						</p>
					</div>
					<div class="toolbar clear">
						<button type="submit" class="button-primary Funny-data-addon-save"><?php esc_html_e( 'Save changes', 'Funny' ); ?></button>
					</div>
				</div>
			</div>
			<?php
		}

		public function enqueue_scripts() {
			wp_enqueue_script( 'Funny-product-addon', Funny_ADDON_URI . '/product-addon/product-addon.js', array(), 1, true );
			wp_localize_script(
				'Funny-product-addon',
				'Funny_product_addon_vars',
				array(
					'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'post_id'  => get_the_ID(),
					'nonce'    => wp_create_nonce( 'Funny-product-editor' ),
				)
			);
		}

		public function save_extra_options() {
			if ( ! check_ajax_referer( 'Funny-product-editor', 'nonce', false ) ) {
				wp_send_json_error( 'invalid_nonce' );
			}
			$post_id         = $_POST['post_id'];
			$exclusive       = isset( $_POST['exclusive'] ) ? Funny_strip_script_tags( $_POST['exclusive'] ) : '';
			$learn_more_link = isset( $_POST['learn_more_link'] ) ? Funny_strip_script_tags( $_POST['learn_more_link'] ) : '';
			$background_id   = isset( $_POST['background_id'] ) ? Funny_strip_script_tags( $_POST['background_id'] ) : '';
			$offer_id        = isset( $_POST['offer_id'] ) ? $_POST['offer_id'] : '';

			if ( $exclusive ) {
				update_post_meta( $post_id, 'Funny_exclusive', $exclusive );
			} else {
				delete_post_meta( $post_id, 'Funny_exclusive' );
			}

			if ( $learn_more_link ) {
				update_post_meta( $post_id, 'Funny_learn_more_link', $learn_more_link );
			} else {
				delete_post_meta( $post_id, 'Funny_learn_more_link' );
			}

			if ( $background_id ) {
				update_post_meta( $post_id, 'Funny_background_image', $background_id );
			} else {
				delete_post_meta( $post_id, 'Funny_background_image' );
			}

			if ( $offer_id ) {
				update_post_meta( $post_id, 'Funny_offer_image', $offer_id );
			} else {
				delete_post_meta( $post_id, 'Funny_offer_image' );
			}

			wp_send_json_success();
			die();
		}
	}
}

new Funny_Product_Data_Addons;
