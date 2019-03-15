<?php
/*
  Plugin Name: Tickera - Event Category List shortcode
  Plugin URI: http://tickera.com/
  Description: List all events with a shortcode [tc_category_sc slug="category_slug"]
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2017 Tickera (http://tickera.com/)
 * IS
 */

function tc_events_by_category_query($atts) {
    ob_start();
    extract(shortcode_atts(array(
        'slug' => false,
                    ), $atts));

    $args = array(
        'post_type' => 'tc_events',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'event_category',
                'field' => 'slug',
                'terms' => $slug,
            ),
        ),
        'meta_query' => array(
            array(
                'key' => 'event_date_time',
                'value' => date('Y-m-d h:i'),
                'type' => 'DATETIME',
                'compare' => '>='
            ),
            'orderby' => 'event_date_time',
        ),
        'order' => 'ASC',
        'orderby' => 'meta_value',
        'post_status' => 'publish'
    );

    // The Query
    $the_query = new WP_Query($args);

    // The Loop
    if ($the_query->have_posts()) {
        while ($the_query->have_posts()) {
            $the_query->the_post();
            //change this part if you need to make any changes the way events display
            ?>
            <div class="tc-single-event">     
                <h2>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                </h2>
                <?php the_post_thumbnail(); ?>
            </a>


            <span class="tc_event_date_title_front"><i class="fa fa-clock-o"></i><?php echo get_post_meta(get_the_ID(), 'event_date_time', true); ?> </span> </br>
            <span class="tc_event_location_title_front"><i class="fa fa-map-marker"></i><?php echo get_post_meta(get_the_ID(), 'event_location', true); ?></span>
                    <p><?php echo the_excerpt();?></p>

                    

                    </div>

                    <?php
                }
            } else {
                // if no posts are found you can add message here
            }
            /* Restore original Post Data */
            wp_reset_postdata();
            $content = ob_get_clean();

            return $content;
        }

//tc_event_query

        add_shortcode('tc_category_sc', 'tc_events_by_category_query');
        