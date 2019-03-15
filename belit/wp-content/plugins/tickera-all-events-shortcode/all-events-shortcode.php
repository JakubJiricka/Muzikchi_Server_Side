<?php

/*
  Plugin Name: Tickera - All Events Shortcode
  Plugin URI: http://tickera.com/
  Description: List all events on any page or post with [all_events] shortcode. List is sorted in the ascending order starting with the first upcoming event. Also, past events are automatically removed from the list. 
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.0
  TextDomain: tc
  Domain Path: /languages/

  Copyright 2016 Tickera (http://tickera.com/)
 */

function tc_event_query() {
    ob_start();
    //query argumenets
    $args = array(
        'post_type' => 'tc_events',
        'posts_per_page' => -1,
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
                                   <h3>
                                       <a href="<?php the_permalink(); ?>">
            <?php the_title(); ?>
                                        </a>
                                   </h3>
                                        <p><?php the_excerpt(); ?></p>
                                        <p><?php echo get_post_meta(get_the_ID(), 'event_date_time', true); ?> </p>                            
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

add_shortcode('all_events', 'tc_event_query');
