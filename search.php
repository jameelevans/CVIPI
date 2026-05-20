<?php
/**
 * Search results template.
 *
 * Runs a custom post query against the current search term and renders matching
 * posts as cards. This template can be replaced with the default WordPress loop
 * later if search needs pagination or multiple post types.
 *
 * @package cvipi
 */

get_header();
?>


<main>
    
    <?php
    // Capture the submitted search term and pass it into a focused posts query.
    $s=get_search_query();
    $args = array(
      'post_type'=>'post',
       'post_status'=>'publish',
        'posts_per_page'=>-1,
                    's' =>$s
                );
        // Build the search results loop.
        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) {
            _e('<section class="webinars">
                  <div class="webinars__wrapper">
                      <div class="webinars__header"><h1 class="h2__heading">Search Results for: ' .get_query_var("s"). '</h1></div>
                        <!-- Webinar wrapper  -->
                        <div class="webinars__grid">');
            while ( $the_query->have_posts() ) {
              $the_query->the_post();
              // Use the first category for the result card label/link.
              $get_cat  = get_the_category();
              $first_cat      = $get_cat[0];
              $category_name  = $first_cat->cat_name;
              $category_link  = get_category_link( $first_cat->cat_ID ); ?>
                        <a class="webinars__container" href="<?php echo esc_url( $category_link ); ?>" title="Click here to veiw all <?php echo esc_attr( $category_name ); ?>s">
                          <header>
                              <h4 class="h4__header"><?php echo the_title(); ?></h4>
                          </header>
                          <p class="webinars__description"><?php
                              if( has_excerpt() ){
                              echo strip_tags(substr( get_the_excerpt(), 0, 105 ))."...";
                              } else {
                              echo wp_trim_words(get_the_content(), 15);
                              }?> </p>
                              <div class="webinars__cta" >View Webinars</div>
                      </a> <!-- .Webinar post  -->
                    <?php }
        }else{
    ?>
            <div class="webinars__header"><h1 class="h2__heading">Search Results for: .get_query_var('s').</h1></div>
            <div class="alert alert-info">
              <p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
            </div>
    <?php } ?>
          </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
