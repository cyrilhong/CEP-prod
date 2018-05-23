<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Memberships User Messages Handler.
 *
 * This static class provides an handler for front end messages as well as an admin helper to edit and update messages.
 *
 * An important note is understanding how message codes work.
 * They're based on a consistent naming convention based on the following components:
 *
 * {content_type}_{restriction_type}_message_{products}
 *
 * The content type is for example "post_content" if a blog post, or "product" if a product.
 * The restriction type is "delayed", "restricted", etc.
 * The "_message_" suffix is appended for completeness and becomes relevant in filtering, providing semantic value.
 * The latest bit is omitted for messages that are relevant to content that is restricted by plans that have one or more products to get access.
 * If a Membership has no products that grant access then the "no_products" suffix is used.
 *
 * The {products} flag the "_message_" suffixes are normally not required when invoking a message as the `get_message_html()` method will attempt to self determine these based on passed arguments and context.
 * Likewise, this class getter methods will try to determine whether a default message, a message saved in the settings option or a custom message override has to be used.
 *
 * It is not the case however when saving a message with `set_message()` in that case the method needs to know the full message code including these suffix variables.
 * It is taken into account of that in admin methods and admin UI that set message options and individual content overrides.
 *
 * @since 1.9.0
 */
class WC_Memberships_User_Messages {


	/** @var string the option key where the array of user messages is stored */
	private static $user_messages_option_key = 'wc_memberships_messages';


	/**
	 * Set a message.
	 *
	 * It is important to remember that the $code to be passed to this method has to be a fully formed code.
	 * It will not auto-determine or adjust the message code and therefore this has to include the '_message' suffix or any '_no_products' ending flag.
	 *
	 * @since 1.9.0
	 *
	 * @param string $code message key code
	 * @param string $message message body
	 * @param null|int|\WP_Post|\WC_Product $object_id optional, if specified will try to set the message override on the given object
	 */
	public static function set_message( $code, $message, $object_id = null ) {

		if ( is_string( $message ) && in_array( $code, self::get_default_messages( false ), true ) ) {

			if ( $object_id instanceof WP_Post ) {
				$object_id = $object_id->ID;
			} elseif ( $object_id instanceof WC_Product ) {
				$object_id = $object_id->get_id();
			}

			if ( is_numeric( $object_id ) && $object_id > 0 ) {

				if ( '' === trim( $message ) ) {
					wc_memberships_delete_content_meta( $object_id, "_wc_memberships_{$code}" );
				} else {
					wc_memberships_set_content_meta( $object_id, "_wc_memberships_{$code}", $message );
				}

			} else {

				$messages = get_option( self::$user_messages_option_key, array() );

				if ( ! is_array( $messages ) ) {
					$messages = self::get_default_messages();
				}

				$messages[ $code ] = '' === $message ? self::get_default_message( $code ) : $message;

				update_option( self::$user_messages_option_key, $messages );
			}
		}
	}


	/**
	 * Delete a message (restores the default message).
	 *
	 * @since 1.9.0
	 *
	 * @param string $code the message key code
	 * @param int|null|object $object_id optional, if specified will delete the message override from the object
	 */
	public static function delete_message( $code, $object_id = null ) {

		self::set_message( $code, '', $object_id );
	}


	/**
	 * Get the messages defaults.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $with_labels whether to return only the message key codes (false) or also the labels (true, default)
	 * @return array associative array of codes and text
	 */
	public static function get_default_messages( $with_labels = true ) {

		$messages = array(

			// generic content (e.g. any other post type than blog posts, pages and products)
			'content_delayed_message'                                 => __( 'This content is part of your membership, but not yet! You will gain access on {date}.', 'woocommerce-memberships' ),
			'content_restricted_message'                              => __( 'To access this content, you must purchase {products}.', 'woocommerce-memberships' ),
			'content_restricted_message_no_products'                  => __( 'This content is only available to members.', 'woocommerce-memberships' ),

			// blog posts
			'post_content_delayed_message'                            => __( 'This post is part of your membership, but not yet! You will gain access on {date}.', 'woocommerce-memberships' ),
			'post_content_restricted_message'                         => __( 'To access this post, you must purchase {products}.', 'woocommerce-memberships' ),
			'post_content_restricted_message_no_products'             => __( 'This post is only available to members.', 'woocommerce-memberships' ),

			// pages
			'page_content_delayed_message'                            => __( 'This page is part of your membership, but not yet! You will gain access on {date}.', 'woocommerce-memberships' ),
			'page_content_restricted_message'                         => __( 'To access this page, you must purchase {products}.', 'woocommerce-memberships' ),
			'page_content_restricted_message_no_products'             => __( 'This page is only available to members.', 'woocommerce-memberships' ),

			// products
			'product_access_delayed_message'                          => __( 'This product is part of your membership, but not yet! It will become available on {date}.', 'woocommerce-memberships' ),
			'product_viewing_restricted_message'                      => __( 'This product can only be viewed by members. To view or purchase this product, sign up by purchasing {products}.', 'woocommerce-memberships' ),
			'product_viewing_restricted_message_no_products'          => __( 'This product can only be viewed by members.', 'woocommerce-memberships' ),
			'product_purchasing_restricted_message'                   => __( 'This product can only be purchased by members. To purchase this product, sign up by purchasing {products}.', 'woocommerce-memberships' ),
			'product_purchasing_restricted_message_no_products'       => __( 'This product can only be purchased by members.', 'woocommerce-memberships' ),

			// product categories
			'product_category_viewing_delayed_message'                => __( 'This product category is part of your membership, but not yet! You will gain access on {date}.', 'woocommerce-memberships' ),
			'product_category_viewing_restricted_message'             => __( 'This product category can only be viewed by members. To view this category, sign up by purchasing {products}.', 'woocommerce-memberships' ),
			'product_category_viewing_restricted_message_no_products' => __( 'This product category can only be viewed by members.', 'woocommerce-memberships' ),

			// product discount messages (non-restrictions)
			'product_discount_message'                                => __( 'Want a discount? Become a member by purchasing {products}!', 'woocommerce-memberships' ),
			'product_discount_message_no_products'                    => __( 'Want a discount? Become a member!', 'woocommerce-memberships' ),

			// product discount cart messages (non-restrictions)
			'cart_sole_item_discount_message'                         => __( 'This item is discounted for members. {Login} to claim it!', 'woocommerce-memberships' ),
			'cart_item_discount_message'                              => __( 'An item in your cart is discounted for members. {Login} to claim it!', 'woocommerce-memberships' ),
			'cart_items_discount_message'                             => __( 'Some items in your cart are discounted for members. {Login} to claim them!', 'woocommerce-memberships' ),

			// member login message (blank by default)
			'member_login_message'                                    => '',
		);

		/** TODO remove this filter deprecation notice by version 1.12.0 {FN 2017-08-03} */
		if ( has_filter( 'wc_memberships_valid_restriction_message_types' ) ) {
			_deprecated_function( 'the filter "wc_memberships_valid_restriction_message_types"', '1.9.0', '"wc_memberships_default_messages" filter' );
		}

		/**
		 * Filter the default user messages.
		 *
		 * The keys are used to store and organize message overrides, the text serves as default message when a override is not found.
		 *
		 * @since 1.9.0
		 *
		 * @param array $messages associative array of message codes and message texts
		 */
		$messages = (array) apply_filters( 'wc_memberships_default_messages', $messages );

		return false === $with_labels ? array_keys( $messages ) : $messages;
	}


	/**
	 * Returns message by type, defaults to content_restricted_message_no_products.
	 *
	 * @since 1.9.0
	 *
	 * @param string $code message key code
	 * @return string
	 */
	public static function get_default_message( $code ) {

		$messages = self::get_default_messages();

		return isset( $messages[ $code ] ) ? $messages[ $code ] : '';
	}


	/**
	 * Get the message code shorthand by post type.
	 *
	 * This method tries to self determine the right message that should returned based on content context.
	 * It will return the message code shorthand, which is useful to `get_message_html()` to retrieve the actual message.
	 *
	 * @since 1.9.0
	 *
	 * @param null|int|\WP_Post|\WC_Product|string $post the post type, post object, post ID or product
	 * @param array $args arguments to determine the requested message type or optional view/purchase type for products
	 * @return string the message code key
	 */
	public static function get_message_code_shorthand_by_post_type( $post = null, $args = array() ) {

		if ( is_string( $post ) ) {
			$post_type = $post;
		} elseif ( is_numeric( $post ) || $post instanceof WP_Post ) {
			$post_type = get_post_type( $post );
		} elseif ( $post instanceof WC_Product ) {
			$post_type = $post->post_type;
		} else {
			global $post;
			$post_type = $post ? get_post_type( $post ) : '';
		}

		$access_type   = isset( $args['access_type'] )  ? $args['access_type']  : 'view';       // either 'view' or 'purchase'
		$message_type  = isset( $args['message_type'] ) ? $args['message_type'] : 'restricted'; // either 'restricted' or 'delayed'
		$access_status = in_array( $message_type, array( 'delayed', 'restricted' ), true ) ? $message_type : 'restricted';

		switch ( $post_type ) {
			case 'product':
			case 'product_variation':
				if ( 'view' === $access_type ) {
					$message_code = 'delayed' === $access_status ? 'product_access_delayed' : "product_viewing_{$access_status}";
				} else {
					$message_code = "product_purchasing_{$access_status}";
				}
			break;
			case 'page':
				$message_code = "page_content_{$access_status}";
			break;
			case 'post':
				$message_code = "post_content_{$access_status}";
			break;
			// other post types
			default:
				$message_code = "content_{$access_status}";
			break;
		}

		/**
		 * Filter the message code shorthand to be used to store a message.
		 *
		 * @since 1.9.0
		 *
		 * @param string $message_code the message code being used
		 * @param string $post_type the related post type
		 */
		return apply_filters( 'wc_memberships_post_type_message_code_shorthand', $message_code, $post_type );
	}


	/**
	 * Looks for message string according to context. It may contain HTML.
	 *
	 * Note: unlike `get_message_html()` this method won't determine if a message has products or no products.
	 * A fully formed message code key should be passed to this method to retrieve the appropriate message type.
	 * Normally methods should call `get_message_html()` to obtain the right HTML message, by passing a message code shorthand.
	 *
	 * @see \WC_Memberships_User_Messages::get_message_html()
	 *
	 * @since 1.9.0
	 *
	 * @param string $message_code message code key
	 * @param array $message_args optional arguments
	 * @return string
	 */
	public static function get_message( $message_code, $message_args = array() ) {
		global $post;

		$the_post         = null;
		$message_override = null;
		$message_args     = self::parse_message_args( $message_args );

		// determine the post source
		if ( ! empty( $message_args['post'] ) ) {
			$the_post = $message_args['post'];
		} elseif ( ! empty( $message_args['post_id'] ) ) {
			$the_post = get_post( $message_args['post_id'] );
		}

		// determine if there's a custom message override
		if ( $the_post = ! $the_post instanceof WP_Post ? $post : $the_post ) {
			// no_products messages aren't saved as custom messages, so look for a plain custom message first...
			if ( SV_WC_Helper::str_ends_with( $message_code, 'no_products' ) ) {
				$message_override = wc_memberships_get_content_meta( $the_post->ID, substr( "_wc_memberships_{$message_code}", 0, -12 ), true );
			}
			// ...otherwise look for a regular custom override message
			if ( empty( $message_override ) ) {
				$message_override = wc_memberships_get_content_meta( $the_post->ID, "_wc_memberships_{$message_code}", true );
			}
		}

		// gather the message content based on the determined source to use
		if ( ! empty( $message_override ) && is_string( $message_override ) ) {
			// use custom message override
			$message = $message_override;
		} elseif ( ( $messages = get_option( self::$user_messages_option_key ) ) && isset( $messages[ $message_code ] ) && '' !== trim( $messages[ $message_code ] ) ) {
			// get message from option
			$message = $messages[ $message_code ];
		} else {
			// get default message as last resort
			$message = self::get_default_message( $message_code );
		}

		/**
		 * Filter the message.
		 *
		 * @since 1.9.0
		 *
		 * @param string $message the membership message
		 * @param array $message_args array of vars used to construct the message
		 */
		$message = (string) apply_filters( "wc_memberships_{$message_code}", $message, $message_args );

		// sanity check: we need to ensure a message is always displayed when requested, if the message code matches
		return empty( $message ) ? self::get_default_message( $message_code ) : $message;
	}


	/**
	 * Returns a final restriction message in HTML format.
	 *
	 * Note: a message code in shorthand form must be passed to this method.
	 * The method self determines whether there are products or no products on the $args passed.
	 * Normally other code should call this method rather than the plain `get_message()`.
	 *
	 * @see \WC_Memberships_User_Messages::get_message()
	 *
	 * @since 1.9.0
	 *
	 * @param string $code_shorthand message type code in shorthand version (no `_message` or `_message_no_products` suffix needed)
	 * @param array $args optional array of arguments
	 * @return string HTML
	 */
	public static function get_message_html( $code_shorthand, $args = array() ) {
		global $post;

		$the_post = null;
		$args     = self::parse_message_args( $args );

		if ( ! empty( $args['post'] ) ) {
			$the_post = $args['post'];
		} elseif ( ! empty( $args['post_id'] ) ) {
			$the_post = get_post( $args['post_id'] );
		}

		$the_post = $the_post instanceof WP_Post ? $the_post : $post;

		if ( empty( $args['rule_type'] ) ) {
			$rule_type = strpos( $code_shorthand, 'discount' ) !== false ? 'purchasing_discount'  : '';
		} else {
			$rule_type = $args['rule_type'];
		}

		$products     = ! empty( $args['products'] )    ? $args['products']    : self::get_products_that_grant_access( $the_post, $rule_type );
		$access_time  = ! empty( $args['access_time'] ) ? $args['access_time'] : 0;
		$args         = array(
			'context'     => $args['context'],
			'post'        => $the_post,
			'post_id'     => $the_post ? (int) $the_post->ID : 0,
			'access_time' => $access_time,
			'products'    => $products,
			'rule_type'   => $rule_type,
			'classes'     => $args['classes'],
		);

		// find message by code key
		$message_code = empty( $products ) && ! SV_WC_Helper::str_ends_with( $code_shorthand, 'delayed' ) ? $code_shorthand . '_message_no_products' : $code_shorthand . '_message';
		$message      = do_shortcode( self::get_message( $message_code, $args ) );
		$message      = self::handle_deprecated_filters( $message, $message_code, $args );
		// handle merge tags
		$html_message = wp_kses_post( self::parse_message_merge_tags( $message, $args ) );
		// get HTML classes
		$classes      = self::get_message_classes( $message_code, $args );

		if ( 'content' === $args['context'] ) {

			// maybe prepend an excerpt if settings say so
			$excerpt = '';

			if ( $the_post && wc_memberships()->get_restrictions_instance()->showing_excerpts() ) {

				// for products, use WooCommerce template instead of WordPress standard excerpt
				if ( 'product' === get_post_type( $the_post ) ) {

					if (    in_array( $message_code, array( 'product_viewing_restricted_message', 'product_viewing_restricted_message_no_products' ), false )
					     || ( 'product_access_delayed_message' === $message_code && ( ! current_user_can( 'wc_memberships_view_delayed_product', $the_post->ID ) || ! current_user_can( 'wc_memberships_view_restricted_product', $the_post->ID ) ) ) ) {

						ob_start();

						?>
						<div class="summary entry-summary">
							<?php wc_get_template( 'single-product/title.php' ); ?>
							<?php wc_get_template( 'single-product/short-description.php' ); ?>
						</div>
						<?php

						$excerpt = ob_get_clean();
					}

				} else {

					$excerpt = get_the_excerpt( $the_post );
				}
			}

			$html_message = $excerpt . ( ! empty( $html_message ) ? '<div class="woocommerce"><div class="' . implode( ' ', $classes ) . '">'. $html_message . '</div></div>' : '' );

		} // another context means we're already using a notice, don't build further html

		/**
		 * Filter whether process shortcodes on user messages.
		 *
		 * @since 1.9.0
		 *
		 * @param bool $apply_shortcodes default true
		 * @param string $message_code the message code being parsed
		 * @param array $args optional arguments being processed
		 */
		if ( true === (bool) apply_filters( 'wc_memberships_message_process_shortcodes', true, $message_code, $args ) ) {
			$html_message = do_shortcode( $html_message );
		}

		/**
		 * Filter the message HTML.
		 *
		 * @since 1.9.0
		 *
		 * @param string $html_message HTML message
		 * @param array $args array of arguments used to build the message
		 */
		$html_message = apply_filters( "wc_memberships_{$message_code}_html", $html_message, $args );

		/**
		 * Filters the restricted content (legacy filter).
		 *
		 * This is a legacy filter from the time (before 1.9.0) when Memberships was filtering the_content instead of the post object.
		 * Ideally, though, customizations should try filtering the specific restriction message.
		 *
		 * @since 1.6.0
		 *
		 * @param string $html_message the message that restricts the content (may contain HTML)
		 * @param true $restricted whether the content is restricted: since version 1.9.1 this is always true, the filter no longer runs when the content is not restricted (kept for backwards compatibility reasons)
		 * @param string $message the message that has replaced the content or appended to an excerpt
		 * @param \WP_Post $the_post the post object being restricted
		 */
		return (string) apply_filters( 'wc_memberships_the_restricted_content', $html_message, true, $message, $the_post );
	}


	/**
	 * Parse message arguments for processing.
	 *
	 * @since 1.9.0
	 *
	 * @param array $args
	 * @return array
	 */
	private static function parse_message_args( array $args ) {

		$args = wp_parse_args( $args, array(
			'context'     => 'content', // (string) whether the message will appear in the context of a notice or elsewhere
			'post'        => null,      // (\WP_Post) the post object, used for processing the message variables
			'post_id'     => 0,         // (int) the post ID, used when the post object is not passed directly
			'access_time' => 0,         // (int) the user access time, used mostly for delayed messages
			'products'    => array(),   // (int[]) IDs of products that grant access
			'rule_type'   => '',        // (string) the related rule type
			'classes'     => '',        // (string|array) optional additional classes used in the message HTML
		) );

		if ( ! in_array( $args['context'], array( 'content', 'notice' ), true ) ) {
			$args['context'] = 'content';
		}

		return $args;
	}


	/**
	 * Replace a message's merge tags with HTML variables.
	 *
	 * @since 1.9.0
	 *
	 * @param string $message the original message that may contain merge tags
	 * @param array $args associative array of optional arguments for strings replacement
	 * @return string HTML
	 */
	public static function parse_message_merge_tags( $message, $args = array() ) {

		$products    = isset( $args['products'] ) ? array_filter( array_map( 'absint', $args['products'] ) ) : null;
		$access_time = isset( $args['access_time'] ) ? $args['access_time'] : null;

		// replace {products}
		if ( ! empty( $products ) ) {

			$products_links = array();

			foreach ( $products as $product_id ) {
				if ( $product_link = self::get_product_link_html( $product_id ) ) {
					$products_links[] = $product_link;
				}
			}

			$products_list = ! empty( $products_links ) ? '<span class="wc-memberships-products-grant-access">' . wc_memberships_list_items( $products_links ) . '</span>' : '';
			$message       = '' !== $products_list ? str_replace( '{products}', $products_list, $message ) : $message;
		}

		// replace {date}
		if ( is_numeric( $access_time ) && $access_time > 0 ) {

			$message = str_replace( '{date}', date_i18n( wc_date_format(), (int) $access_time ), $message );
		}

		// replace {login*} tags
		if ( false !== stripos( $message, '{login' ) ) {
			// replace {login} (check also for capitalized variant)
			/* translators: Placeholders: %1$s - opening HTML <a> link tag, %2$s closing HTML </a> link tag */
			$message = str_replace( '{Login}', sprintf( _x( '%1$sLog in%2$s', 'Capitalized', 'woocommerce-memberships' ), '<a href="{login_url}">', '</a>' ), $message );
			/* translators: Placeholders: %1$s - opening HTML <a> link tag, %2$s closing HTML </a> link tag */
			$message = str_replace( '{login}', sprintf( _x( '%1$slog in%2$s', 'Lowercase',   'woocommerce-memberships' ), '<a href="{login_url}">', '</a>' ), $message );
			// replace {login_url}
			$message = str_replace( '{login_url}', self::get_restricted_content_redirect_url(), $message );
		}

		return $message;
	}


	/**
	 * Handle messages deprecated filters for backwards compatibility.
	 *
	 * TODO remove this method by version 1.12.0 - do not open to public {FN 2017-07-04}
	 *
	 * @since 1.9.0
	 *
	 * @param string $message may contain HTML
	 * @param string $message_code
	 * @param array $args associative array
	 * @return string message (may contain HTML)
	 */
	private static function handle_deprecated_filters( $message, $message_code, $args ) {

		$deprecated = false;

		if ( ! empty( $args['access_time'] ) && has_filter( 'get_content_delayed_message' ) ) {

			$deprecated = 'get_content_delayed_message';

			/**
			 * Filter the delayed content message.
			 *
			 * TODO this filter will be removed by version 1.12.0 {FN 2017-07-03}
			 *
			 * @deprecated since 1.9.0
			 * @since 1.0.0
			 *
			 * @param string $message delayed content message
			 * @param array $args array of vars used to construct the message
			 */
			$message = (string) apply_filters( 'get_content_delayed_message', $message, $args['post_id'], $args['access_time'] );

		} elseif ( has_filter( 'wc_memberships_member_discount_message' ) ) {

			$deprecated = 'wc_memberships_member_discount_message';
			$post_id    = ! empty( $args['post'] ) ? $args['post']->ID : 0;

			/**
			 * Filter the product member discount message
			 *
			 * TODO this filter will be removed by version 1.12.0 {FN 2017-07-03}
			 *
			 * @deprecated since 1.9.0
			 * @since 1.0.0
			 *
			 * @param string $message the discount message
			 * @param int $product_id ID of the product that has member discounts
			 * @param int[] $products array of product IDs that grant access to this product
			 */
			$message = (string) apply_filters( 'wc_memberships_member_discount_message', $message, $post_id, $args['products'] );

		} elseif ( has_filter( 'wc_memberships_product_taxonomy_viewing_restricted_message' ) ) {

			$deprecated = 'wc_memberships_product_taxonomy_viewing_restricted_message';

			/**
			 * Filter the product term viewing restricted message.
			 *
			 * TODO this filter will be removed by version 1.12.0 {FN 2017-07-03}
			 *
			 * @deprecated since 1.9.0
			 * @since 1.4.0
			 *
			 * @param string $message the restriction message
			 * @param string $taxonomy product taxonomy
			 * @param int $term_id product taxonomy term id
			 * @param array $products array of product IDs that grant access to products taxonomy
			 */
			$message = (string) apply_filters( 'wc_memberships_member_login_message', $message );

		} elseif ( has_filter( 'wc_memberships_member_login_message' ) ) {

			$deprecated = 'wc_memberships_member_login_message';

			/**
			 * Filter the member login notice message.
			 *
			 * TODO this filter will be removed by version 1.12.0 {FN 2017-07-03}
			 *
			 * @deprecated since 1.9.0
			 * @since 1.3.8
			 *
			 * @param string $message yhe message text
			 */
			$message = (string) apply_filters( 'wc_memberships_member_login_message', $message );
		}

		if ( false !== $deprecated ) {
			_deprecated_function( "the filter {$deprecated}", '1.9.0', '"wc_memberships_' . $message_code . '_html" or "wc_memberships_' . $message_code . '" filters' );
		}

		return $message;
	}


	/**
	 * Get a formatted login url with restricted content redirect URL.
	 *
	 * Attempts to use the ID in argument to determine content to redirect to.
	 * If unspecified, will try to look for a 'r' query string set in Memberships redirection mode.
	 * If neither is available, it will just redirect to the current user account page.
	 *
	 * @since 1.9.0
	 *
	 * @param int|null $redirect_id optional: the ID of the post to redirect to
	 * @return string escaped url
	 */
	private static function get_restricted_content_redirect_url( $redirect_id = null ) {

		$redirect_to = null;
		$login_url   = wc_get_page_permalink( 'myaccount' );

		if ( null === $redirect_id ) {
			if ( isset( $_GET['r'] ) && is_numeric( $_GET['r'] ) ) {
				$redirect_id = (int) $_GET['r'];
			} else {
				$redirect_id = get_queried_object_id();
			}
		}

		if ( is_numeric( $redirect_id ) && $redirect_id > 0 ) {

			if ( get_post( $redirect_id ) ) {
				$redirect_to = is_page( $redirect_id ) ? 'page' : 'post';
			} elseif ( $term = get_term( $redirect_id ) ) {
				$redirect_to = $term instanceof WP_Term ? $term->taxonomy : null;
			}

			if ( is_string( $redirect_to ) ) {

				$login_url = add_query_arg( array(
					'wcm_redirect_to' => $redirect_to,
					'wcm_redirect_id' => $redirect_id,
				), $login_url );
			}
		}

		return esc_url( $login_url );
	}


	/**
	 * Takes a product ID and returns formatted title with link.
	 *
	 * @since 1.9.0
	 *
	 * @param int $product_id a product ID
	 * @return null|string HTML (the formatted product title)
	 */
	private static function get_product_link_html( $product_id ){

		$product_title = null;

		if ( $product = wc_get_product( (int) $product_id ) ) {

			$permalink = $product->get_permalink();
			$title     = $product->get_title();

			/* @type \WC_Product_Variation $product special handling for variations */
			if ( $product->is_type( 'variation' ) ) {

				$attributes = $product->get_variation_attributes();

				foreach ( $attributes as $attr_key => $attribute ) {
					$attributes[ $attr_key ] = ucfirst( $attribute );
				}

				$title .= ' &ndash; ' . implode( ', ', $attributes );
			}

			$product_title = sprintf( '<a href="%s">%s</a>', esc_url( $permalink ), wp_kses_post( $title ) );
		}

		return $product_title;
	}


	/**
	 * Get a list of products that grant access to a piece of content.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WP_Post|null $post_id optional post object for product or product ID
	 * @param string $rule_type the rule type for which access granting products are relevant for (e.g. to get a discount or get access)
	 * @return array
	 */
	private static function get_products_that_grant_access( $post_id = null, $rule_type = '' ) {

		$products = array();

		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? $post->ID : null;
		} elseif ( $post_id instanceof WP_Post ) {
			$post_id = $post_id->ID;
		}

		if ( is_numeric( $post_id ) && $post_id > 0 ) {

			if ( 'purchasing_discount' === $rule_type ) {
				$rules = wc_memberships()->get_rules_instance()->get_product_purchasing_discount_rules( $post_id );
			} elseif ( in_array( get_post_type( $post_id ), array( 'product', 'product_variation' ), true ) ) {
				$rules = wc_memberships()->get_rules_instance()->get_product_restriction_rules( $post_id );
			} else {
				$rules = wc_memberships()->get_rules_instance()->get_post_content_restriction_rules( $post_id );
			}

			// find products that grant access
			$processed_plans = array(); // holder for membership plans that have been processed already
			$products        = array();

			foreach ( $rules as $rule ) {

				// skip further checks if:
				// - this membership plan has already been processed
				// - purchasing discount rule is inactive
				if ( in_array( $rule->get_membership_plan_id(), $processed_plans, true ) || ( 'purchasing_discount' === $rule->get_rule_type() && ! $rule->is_active() ) ) {
					continue;
				}

				$plan = wc_memberships_get_membership_plan( $rule->get_membership_plan_id() );

				if ( $plan && $plan->has_products() ) {

					foreach ( $plan->get_product_ids() as $product_id ) {

						$product = wc_get_product( $product_id );

						if ( $product instanceof WC_Product && $product->is_purchasable() && $product->is_visible() ) {

							// double-check for WC 3.0+, as a variation is always visible, but the parent could be hidden
							if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() && $product->is_type( 'variation' ) && ! SV_WC_Product_Compatibility::get_parent( $product )->is_visible() ) {
								continue;
							}

							$products[] = $product_id;
						}
					}
				}

				// mark this plan as processed, we do not need look into it any further, because we already know if it has any products that grant access or not
				$processed_plans[] = $rule->get_membership_plan_id();
			}

			unset( $processed_plans );

			/**
			 * Filter the list of products that grant access to a piece of content.
			 *
			 * @since 1.4.0
			 *
			 * @param array $products the products that grant access
			 * @param int $post_id the product ID
			 * @param null|string $rule_type the desired rule type
			 */
			$products = (array) apply_filters( 'wc_memberships_products_that_grant_access', $products, $post_id, $rule_type );
		}

		return ! empty( $products ) ? array_map( 'absint', (array) $products ) : array();
	}


	/**
	 * Returns CSS classes for the restriction message.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type message type (underscored)
	 * @param array $args optional arguments
	 * @return string[] array of CSS classes (dashed)
	 */
	private static function get_message_classes( $type, $args = array() ) {

		$classes = array( 'woocommerce-info' );

		switch ( $type ) {

			// product viewing restricted
			case 'product_viewing_restricted_message':
				$classes = array_merge( $classes, array( 'wc-memberships-restriction-message', 'wc-memberships-restricted-content-message' ) );
			break;

			// product purchasing restricted
			case 'product_purchasing_restricted_message':
				$classes = array_merge( $classes, array( 'wc-memberships-restriction-message', 'wc-memberships-product-purchasing-restricted-message' ) );
			break;

			// product viewing / purchasing delayed
			case 'product_access_delayed_message':
				$classes = array_merge( $classes, array( 'wc-memberships-restriction-message', 'wc-memberships-product-access-delayed-message' ) );
			break;

			// product purchasing discount
			case 'product_discount_message':
			case 'product_discount_message_no_products':
				$classes = array_merge( $classes, array( 'wc-memberships-member-discount-message' ) );
			break;

			// product category restricted / delayed
			case 'product_category_viewing_restricted_message':
			case 'product_category_viewing_restricted_message_no_products':
			case 'product_category_viewing_delayed_message':
				$classes = array_merge( $classes, array( 'wc-memberships-restriction-message', 'wc-memberships-product-category-viewing-restricted-message' ) );
			break;

			// content (post, page, post type...) restricted / delayed
			default:
				$classes = array_merge( $classes, array( 'wc-memberships-restriction-message', 'wc-memberships-message', 'wc-memberships-content-restricted-message' ) );
			break;
		}

		// apply any custom classes
		if ( ! empty( $args['classes'] ) ) {
			if ( is_string( $args['classes'] ) ) {
				$classes[] = $args['classes'];
			} elseif ( is_array( $args['classes'] ) ) {
				$classes = array_merge( $classes, $args['classes'] );
			}
		}

		/**
		 * Get restriction message CSS classes.
		 *
		 * @since 1.9.0
		 *
		 * @param string[] $classes CSS classes as a string array
		 * @param string $type the requested message type
		 */
		return apply_filters( 'wc_memberships_message_classes', $classes, $type );
	}


}
