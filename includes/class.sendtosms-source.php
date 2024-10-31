<?php

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
class jetsms_Share_SendToSMS extends Sharing_Source {
	var $shortname = 'sendtosms';

	function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __( 'SendToSMS', 'jetpack-sendtosms' );
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	private function guess_locale_from_lang( $lang ) {
		if( strpos( $lang, 'ja' ) === 0 )
			return 'ja';

		if( strpos( $lang, 'zh' ) === 0 )
			return 'zh-hant';

		return 'en';
	}

	function get_display( $post ) {
		include_once ABSPATH . 'wp-includes/post-thumbnail-template.php';
		$locale = $this->guess_locale_from_lang( get_locale() );
		if($this->smart) {
			return sprintf(
				'<div class="sendtosms_button %s" style="height: 20px; width: 69px; overflow: hidden;"><iframe scrolling="no" frameborder="0" style="border: none; width: 1000px; height: 1000px; overflow:hidden;" src="http://www.sendtosms.com/share-button?url=%s&title=%s"></iframe></div>',
				esc_attr( $locale ),
				rawurlencode( get_permalink( $post->ID ) ),
				rawurlencode( $this->get_share_title( $post->ID ) )
			);
		}
		else {
			return sprintf(
				'<a target="sendtosms-share" class="sendtosms_button share-sendtosms sd-button %s" href="http://www.sendtosms.com/share?url=%s&title=%s" title="%s"><span>%s</span></a>',
				esc_attr( $locale ),
				rawurlencode( get_permalink( $post->ID ) ),
				esc_attr( $this->get_share_title( $post->ID ) ),
				__( 'Send link as SMS text message', 'jetpack-sendtosms' ),
				__( 'SendToSMS', 'jetpack-sendtosms' ) 
			);
		}
	}

	function display_header() {
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {

		$sendtosms_url = sprintf(
			'http://www.sendtosms.com/share?url=%s&title=%s',
			rawurlencode( get_permalink( $post->ID ) ),
			esc_attr( $this->get_share_title( $post->ID ) )
		);
		// Record stats
		parent::process_request( $post, $post_data );

		// Redirect to SendToSMS
		wp_redirect( $sendtosms_url );
		die();
	}
}
