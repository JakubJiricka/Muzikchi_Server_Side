<?php
/**
 * The template for displaying content in the index.php template
 *
 * @package Betheme
 * @author Muffin group
 * @link http://muffingroup.com
 */

if( ! function_exists( 'mfn_content_post' ) ){
	function mfn_content_post( $query = false, $style = false, $attr = false ){

		global $wp_query;
		$output = '';

		$translate['published'] 	= mfn_opts_get('translate') ? mfn_opts_get('translate-published','Published by') : __('Published by','betheme');
		$translate['at'] 			= mfn_opts_get('translate') ? mfn_opts_get('translate-at','at') : __('at','betheme');
		$translate['categories'] 	= mfn_opts_get('translate') ? mfn_opts_get('translate-categories','Categories') : __('Categories','betheme');
		$translate['like'] 			= mfn_opts_get('translate') ? mfn_opts_get('translate-like','Do you like it?') : __('Do you like it?','betheme');
		$translate['readmore'] 		= mfn_opts_get('translate') ? mfn_opts_get('translate-readmore','Read more') : __('Read more','betheme');

		extract( shortcode_atts( array(
			'featured_image' 	=> false,
			'filters' 				=> false,
		), $attr ) );

		if( ! $query ) $query = $wp_query;
		if( ! $style ){
			if( $_GET && key_exists('mfn-b', $_GET) ){
				$style = esc_html( $_GET['mfn-b'] ); // demo
			} else {
				$style = mfn_opts_get( 'blog-layout', 'classic' );
			}
		}

		if ( $query->have_posts() ){
			while ( $query->have_posts() ){
				$query->the_post();


				// classes

				$post_class =  array('post-item','isotope-item','clearfix');
				if( ! mfn_post_thumbnail( get_the_ID() ) ) $post_class[] = 'no-img';
				if( post_password_required() ) $post_class[] = 'no-img';

				if( in_array( $filters, array( 1, 'only-authors' ) ) ){
					$post_class[] = 'author-'. mfn_slug( get_the_author_meta( 'user_login' ) );
				}

				$post_class = implode(' ', get_post_class( $post_class ));

				// background color | Style - Masonry Tiles

				$bg_color = get_post_meta( get_the_ID(), 'mfn-post-bg', true );
				if( $bg_color && $style == 'masonry tiles' ){
					$bg_color = 'style="background-color:'. $bg_color .';"';
				}


				$output .= '<div class="'. $post_class .'" '. $bg_color .'>';


					// icon | Style == Masonry Tiles
					if( $style == 'masonry tiles' ){

						if( get_post_format() == 'video' ){

							$output .=  '<i class="post-format-icon icon-play"></i>';

						} elseif( get_post_format() == 'quote' ){

							$output .=  '<i class="post-format-icon icon-quote"></i>';

						} elseif( get_post_format() == 'link' ){

							$output .=  '<i class="post-format-icon icon-link"></i>';

						} elseif( get_post_format() == 'audio' ){	// for future use

							$output .=  '<i class="post-format-icon icon-music-line"></i>';

						} else {

							$rev_slider = get_post_meta( get_the_ID(), 'mfn-post-slider', true );
							$lay_slider = get_post_meta( get_the_ID(), 'mfn-post-slider-layer', true );

							if( $rev_slider || $lay_slider ){
								$output .=  '<i class="post-format-icon icon-code"></i>';
							}

						}

					}


					// date | Style == Timeline
					$output .= '<div class="date_label">'. get_the_date() .'</div>';


					// photo --------------------------------------------------------------------------
					if( ! post_password_required() ){

						if( $style == 'masonry tiles' ){

							// photo | Style != Masonry Tiles

							$output .= '<div class="post-photo-wrapper scale-with-grid">';
								$output .= '<div class="image_wrapper_tiles">';
									$output .= get_the_post_thumbnail( get_the_ID(), 'full', array( 'class'=>'scale-with-grid', 'itemprop'=>'image' ) );
								$output .= '</div>';
							$output .= '</div>';

						} else {

							// photo | Style == *

							// Post Image
							$post_format = mfn_post_thumbnail_type( get_the_ID() );
							if( $featured_image == 'image' ){
								$post_format = 'images_only';
							}


							$output .= '<div class="image_frame post-photo-wrapper scale-with-grid '. $post_format .'">';
								$output .= '<div class="image_wrapper">';
									$output .= mfn_post_thumbnail( get_the_ID(), 'blog', $style, $featured_image );
								$output .= '</div>';
							$output .= '</div>';

						}

					}

					// desc ---------------------------------------------------------------------------
					$output .= '<div class="post-desc-wrapper">';
						$output .= '<div class="post-desc">';

							// head -------------------------------------
							$output .= '<div class="post-head">';

								// meta -------------------------------------

								$show_meta = false;
								$list_meta = mfn_opts_get( 'blog-meta' );

								if( is_array( $list_meta ) ){
									if( isset( $list_meta['author'] ) || isset( $list_meta['date'] ) || isset( $list_meta['categories'] ) ){
										$show_meta = true;
									}
								}

								if( $show_meta ){
									$output .= '<div class="post-meta clearfix">';

										$output .= '<div class="author-date">';

											if( isset( $list_meta['author'] ) ){
												$output .= '<span class="vcard author post-author">';
													$output .= '<span class="label">'. $translate['published'] .' </span>';
													$output .= '<i class="icon-user"></i> ';
													$output .= '<span class="fn"><a href="'. get_author_posts_url( get_the_author_meta( 'ID' ) ) .'">'. get_the_author_meta( 'display_name' ) .'</a></span>';
												$output .= '</span> ';
											}

											if( isset( $list_meta['date'] ) ){
												$output .= '<span class="date">';
													if( isset( $list_meta['author'] ) ) $output .= '<span class="label">'. $translate['at'] .' </span>';
													$output .= '<i class="icon-clock"></i> ';
													$output .= '<span class="post-date updated">'. get_the_date() .'</span>';
												$output .= '</span>';
											}


											// .post-comments | Style == Masonry Tiles
											if( $style == 'masonry tiles' && comments_open() && mfn_opts_get( 'blog-comments' ) ){
												$output .= '<div class="post-links">';
													$output .= '<i class="icon-comment-empty-fa"></i> <a href="'. get_comments_link() .'" class="post-comments">'. get_comments_number() .'</a>';
												$output .= '</div>';
											}


										$output .= '</div>';

										if( isset( $list_meta['categories'] ) ){
											$output .= '<div class="category">';
												$output .= '<span class="cat-btn">'. $translate['categories'] .' <i class="icon-down-dir"></i></span>';
												$output .= '<div class="cat-wrapper">'. get_the_category_list() .'</div>';
											$output .= '</div>';
										}

									$output .= '</div>';
								}

								// .post-footer | Style == Photo
								if( $style == 'photo' ){
									$output .= '<div class="post-footer">';

										$output .= '<div class="button-love"><span class="love-text">'. $translate['like'] .'</span>'. mfn_love() .'</div>';
										$output .= '<div class="post-links">';
											if( comments_open() && mfn_opts_get( 'blog-comments' ) ){
												$output .= '<i class="icon-comment-empty-fa"></i> <a href="'. get_comments_link() .'" class="post-comments">'. get_comments_number() .'</a>';
											}
											$output .= '<i class="icon-doc-text"></i> <a href="'. get_permalink() .'" class="post-more">'. $translate['readmore'] .'</a>';
										$output .= '</div>';

									$output .= '</div>';
								}

							$output .= '</div>';

							// title -------------------------------------
							$output .= '<div class="post-title">';

								if( get_post_format() == 'quote' ){
									// quote ----------------------------
									$output .= '<blockquote><a href="'. get_permalink() .'">'. get_the_title() .'</a></blockquote>';

								} elseif( get_post_format() == 'link' ){
									// link ----------------------------
									$output .= '<i class="icon-link"></i>';
									$output .= '<div class="link-wrapper">';
										$output .= '<h4>'. get_the_title() .'</h4>';
										$link = get_post_meta(get_the_ID(), 'mfn-post-link', true);
										$output .= '<a target="_blank" href="'. $link .'">'. $link .'</a>';
									$output .= '</div>';

								} else {
									// default ----------------------------
									$output .= '<h2 class="entry-title" itemprop="headline"><a href="'. get_permalink() .'">'. get_the_title() .'</a></h2>';
								}

							$output .= '</div>';

							// content -------------------------------------
							$output .= '<div class="post-excerpt">'. get_the_excerpt() .'</div>';


							// .post-footer | Style != Photo, Masonry Tiles
							if( ! in_array( $style, array('photo','masonry tiles') ) ){
								$output .= '<div class="post-footer">';

									$output .= '<div class="button-love"><span class="love-text">'. $translate['like'] .'</span>'. mfn_love() .'</div>';
									$output .= '<div class="post-links">';
										if( comments_open() && mfn_opts_get( 'blog-comments' ) ){
											$output .= '<i class="icon-comment-empty-fa"></i> <a href="'. get_comments_link() .'" class="post-comments">'. get_comments_number() .'</a>';
										}
										$output .= '<i class="icon-doc-text"></i> <a href="'. get_permalink() .'" class="post-more">'. $translate['readmore'] .'</a>';
									$output .= '</div>';

								$output .= '</div>';
							}


						$output .= '</div>';
					$output .= '</div>';

				$output .= '</div>';

			}
		}

		return $output;
	}
}
