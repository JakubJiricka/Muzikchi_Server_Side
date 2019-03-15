<?php
/**
 * Logo
 *
 * @package Betheme
 * @author Muffin group
 * @link http://muffingroup.com
 */

if( $logo_text = mfn_opts_get( 'logo-text' ) ){
	$logo_class = ' text-logo';
} else {
	$logo_class = false;
}

echo '<div class="logo'. $logo_class .'">';

	/*
	 * Options
	 * 
	 * - Link to Homepage
	 * - Wrap into H1 tag on Homepage
	 * - Wrap into H1 tag on All other pages
	 */

	$logo_height 	= intval( mfn_opts_get( 'logo-height', 60 ) );
	$logo_padding 	= intval( mfn_opts_get( 'logo-vertical-padding', 15 ) );

	$logo_options 	= mfn_opts_get( 'logo-link', false );
	$logo_before	= '';
	$logo_after		= '';
	
	// Link
	
	if( isset( $logo_options['link'] ) ){
		
		$logo_before 	= '<a id="logo" href="'. get_home_url() .'" title="'. get_bloginfo( 'name' ) .'" data-height="'. $logo_height .'" data-padding="'. $logo_padding .'">';
		$logo_after 	= '</a>';
		
	} else {
		
		$logo_before 	= '<span id="logo" data-height="'. $logo_height .'" data-padding="'. $logo_padding .'">';
		$logo_after 	= '</span>';
		
	}
	
	// H1
	
	if( is_front_page() ){
		if( is_array( $logo_options ) && isset( $logo_options['h1-home'] )){
			
			$logo_before = '<h1>'. $logo_before;
			$logo_after .= '</h1>';
			
		}
	} else {
		if( is_array( $logo_options ) && isset( $logo_options['h1-all'] )){
			
			$logo_before = '<h1>'. $logo_before;
			$logo_after .= '</h1>';
			
		}
	}
	
	
	
	
	/*
	 * Source
	 */
	
	$logo = array(
		'default'	=> array(
			'main'			=> '',
			'sticky' 		=> '',
			'mobile' 		=> '',
			'mobile-sticky' => '',
		),
		'retina'	=> array(
			'main'			=> '',
			'sticky' 		=> '',
			'mobile' 		=> '',
			'mobile-sticky' => '',
		),
	);
	
	if( $layoutID = mfn_layout_ID() ){
		
		// Custom Layout | Layout Options
		
		$logo['default']['main']			= get_post_meta( $layoutID, 'mfn-post-logo-img', true );
		$logo['default']['sticky'] 			= get_post_meta( $layoutID, 'mfn-post-sticky-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-sticky-logo-img', true ) : $logo['default']['main'];
		$logo['default']['mobile'] 			= get_post_meta( $layoutID, 'mfn-post-responsive-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-responsive-logo-img', true ) : $logo['default']['main'];
		$logo['default']['mobile-sticky']	= get_post_meta( $layoutID, 'mfn-post-responsive-sticky-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-responsive-sticky-logo-img', true ) : $logo['default']['main'];
		
		$logo['retina']['main'] 			= get_post_meta( $layoutID, 'mfn-post-retina-logo-img', true );
		$logo['retina']['sticky'] 			= get_post_meta( $layoutID, 'mfn-post-sticky-retina-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-sticky-retina-logo-img', true ) : $logo['retina']['main'];
		$logo['retina']['mobile'] 			= get_post_meta( $layoutID, 'mfn-post-responsive-retina-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-responsive-retina-logo-img', true ) : $logo['retina']['main'];
		$logo['retina']['mobile-sticky']	= get_post_meta( $layoutID, 'mfn-post-responsive-sticky-retina-logo-img', true ) ? get_post_meta( $layoutID, 'mfn-post-responsive-sticky-retina-logo-img', true ) : $logo['retina']['main'];
		
	} else {
		
		// Default | Theme Options

		$logo['default']['main'] 			= mfn_opts_get( 'logo-img', THEME_URI .'/images/logo/logo.png' );
		$logo['default']['sticky'] 			= mfn_opts_get( 'sticky-logo-img' ) ? mfn_opts_get( 'sticky-logo-img' ) : $logo['default']['main'];
		$logo['default']['mobile']  		= mfn_opts_get( 'responsive-logo-img' ) ? mfn_opts_get( 'responsive-logo-img' ) : $logo['default']['main'];
		$logo['default']['mobile-sticky']	= mfn_opts_get( 'responsive-sticky-logo-img' ) ? mfn_opts_get( 'responsive-sticky-logo-img' ) : $logo['default']['main'];
	
		$logo['retina']['main']  			= mfn_opts_get( 'retina-logo-img' );
		$logo['retina']['sticky']  			= mfn_opts_get( 'sticky-retina-logo-img' ) ? mfn_opts_get( 'sticky-retina-logo-img' ) : $logo['retina']['main'];
		$logo['retina']['mobile']  			= mfn_opts_get( 'responsive-retina-logo-img' ) ? mfn_opts_get( 'responsive-retina-logo-img' ) : $logo['retina']['main'];
		$logo['retina']['mobile-sticky'] 	= mfn_opts_get( 'responsive-sticky-retina-logo-img' ) ? mfn_opts_get( 'responsive-sticky-retina-logo-img' ) : $logo['retina']['main'];
	}

	
	// SVG width
	
	if( $width = mfn_opts_get( 'logo-width' ) ){
		$svg 	= ' svg';
		$width 	= 'width="'. $width .'"';
		
	} else {
		$svg 	= false;
		$width 	= false;
	}
	
	
	/*
	 * Print
	 */
	
	echo $logo_before;
	
	if( $logo_text ){
	
		echo $logo_text;
	
	} else {
		
		foreach( $logo['default'] as $logo_key => $logo_src ){
			echo '<img class="logo-'. $logo_key .' scale-with-grid'. $svg .'" src="'. $logo_src .'" data-retina="'. $logo['retina'][$logo_key] .'" data-height="'. mfn_get_attachment_data( $logo_src, 'height' ) .'" alt="'. mfn_get_attachment_data( $logo_src, 'alt' ) .'" '. $width .'/>';
		}
		
	}
		
	echo $logo_after;

echo '</div>';
			