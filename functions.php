<?php /*

  This file is part of a child theme called ABC Consultancy.
  Functions in this file will be loaded before the parent theme's functions.
  For more information, please read https://codex.wordpress.org/Child_Themes.

*/

// this code loads the parent's stylesheet (leave it in place unless you know what you're doing)

function theme_enqueue_styles() {
     wp_enqueue_style( 'owl-carousel', get_stylesheet_directory_uri(). '/css/owl.carousel.min.css' );
    wp_enqueue_script( 'owl-carousel', get_stylesheet_directory_uri() . '/js/owl.carousel.min.js', array('jquery'), '2.2.1', true);
    wp_enqueue_script( 'abc-consulatancy-custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), false, true );
    wp_enqueue_style( 'rara-business-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('rara-business-style', 'animate' ));
}
add_action('wp_enqueue_scripts', 'theme_enqueue_styles');

/*  Add your own functions below this line.
    ======================================== */ 


function abc_consultancy_customize_register_banner_section( $wp_customize ){
  	$wp_customize->remove_control( 'ed_banner_section' );

    /** Banner Options */
    $wp_customize->add_setting(
        'ed_banner_section',
        array(
            'default'           => 'static_banner',
            'sanitize_callback' => 'rara_business_sanitize_select'
        )
    );

    $wp_customize->add_control(
        new Rara_Business_Select_Control(
            $wp_customize,
            'ed_banner_section',
            array(
                'label'       => __( 'Banner Options', 'rara-business' ),
                'description' => __( 'Choose banner as static image/video.', 'rara-business' ),
                'section'     => 'header_image',
                'choices'     => array(
                    'no_banner'     => __( 'Disable Banner Section', 'rara-business' ),
                    'static_banner' => __( 'Static/Video Banner', 'rara-business' ),
                    'Slider_banner' => __( 'Slider Banner', 'rara-business' ),
                ),
                'priority' => 5 
            )            
        )
    );

     /** Select Category */
    $wp_customize->add_setting(
        'slider_cat',
        array(
            'default' => '',
            'sanitize_callback' => 'rara_business_sanitize_select',
        )
    );
    
    $wp_customize->add_control(
        'slider_cat',
        array(
            'label' => __( 'Choose Slider Category', 'preschool-and-kindergarten' ),
            'section' => 'header_image',
            'type' => 'select',
            'choices' => abc_consultancy_select_categories(),
        )
    );
}
add_action( 'customize_register', 'abc_consultancy_customize_register_banner_section', 15 );

function abc_consultancy_select_categories(){
    /* Option list of all categories */
    $args = array(
       'type'                     => 'post',
       'orderby'                  => 'name',
       'order'                    => 'ASC',
       'hide_empty'               => 1,
       'hierarchical'             => 1,
       'taxonomy'                 => 'category'
    ); 
    $categories = array();
    
    $category_lists = get_categories( $args );
    $categories[''] = __( 'Choose Category', 'preschool-and-kindergarten' );
    foreach( $category_lists as $category ){
        $categories[$category->term_id] = $category->name;
    }
    return $categories;
}

function rara_business_banner(){
    $default_options = rara_business_default_theme_options(); // Get default theme options
    
    $banner_control      = get_theme_mod( 'ed_banner_section', $default_options['ed_banner_section'] );
    $title               = get_theme_mod( 'banner_title', $default_options['banner_title'] );
    $description         = get_theme_mod( 'banner_description', $default_options['banner_description'] );
    $link_one_label      = get_theme_mod( 'banner_link_one_label', $default_options['banner_link_one_label'] );
    $link_one_url        = get_theme_mod( 'banner_link_one_url', $default_options['banner_link_one_url'] );
    $link_two_label      = get_theme_mod( 'banner_link_two_label', $default_options['banner_link_two_label'] );
    $link_two_url        = get_theme_mod( 'banner_link_two_url', $default_options['banner_link_two_url'] );
    $custom_header_image = get_header_image_tag(); // get custom header image tag
    $class               = has_header_video() ? 'video-banner' : '';

    $category = get_theme_mod( 'slider_cat', '' );
    
    if( is_front_page() && ! is_home() && ( has_header_video() ||  ! empty( $custom_header_image ) ) && 'no_banner' != $banner_control ){ 

            if( $banner_control == 'static_banner' ){ ?>
            <div id="banner-section" class="banner <?php echo esc_attr( $class ); ?>">
                <?php the_custom_header_markup(); ?>
                <div class="banner-text">
                    <div class="container">
                        <div class="text-holder">
                            <?php
                                if ( $title || $description ){
                                    if ( $title ) echo '<h2 class="title wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.3s">'. esc_html( $title ).'</h2>';
                                    if ( $description ) echo '<p class="wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.5s">'. esc_html( $description ) .'</p>';
                                }

                                if ( $link_one_label || $link_two_label ) {
                                    ?>
                                    <div class="btn-holder wow fadeInUp" data-wow-duration="1s" data-wow-delay="0.7s">
                                        <?php
                                            if ( $link_one_label ) echo  '<a href="'. esc_url( $link_one_url ) .'" class="btn-free-inquiry"><i class="fa fa-edit"></i>'. esc_html( $link_one_label ) .'</a>'; 
                                            if ( $link_two_label ) echo '<a href="'. esc_url( $link_two_url ) .'" class="btn-view-service">'. esc_html( $link_two_label ) .'</a>';
                                        ?>
                                    </div>
                                <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else { ?>
            <div id="banner-section" class="banner <?php echo esc_attr( $class ); ?>">
                <div id="banner-slider" class="banner-slider">
                    <?php 
                        $query = new WP_Query( array( 'cat' => $category ) );

                        if( $query->have_posts() ) :
                            echo '<div class="grid">';
                            while ( $query->have_posts() ) : 
                                $query->the_post(); ?>
                                    <div class="row">
                                        <h2><?php the_title(); ?></h2>
                                        <div class="image-holder">
                                            <?php if( has_post_thumbnail() ){
                                                the_post_thumbnail();
                                            }
                                            the_excerpt();

                                            echo '<a href="'. esc_url( get_the_permalink() ) .'">'. esc_html__( 'Read More', 'abc-consulatancy' ) .'</a>';
                                            ?>
                                        </div>
                                    </div>
                                <?php 
                            endwhile;
                            echo '</div>';
                        endif;
                    ?>
                    
                </div>
            </div>
        <?php
        }
    }
}


function rara_business_get_home_sections(){
    $sections = array( 
        'about'       => array( 'sidebar' => 'about' ), 
        'testimonial' => array( 'sidebar' => 'testimonial' ), 
        'stats'       => array( 'sidebar' => 'stats' ), 
        'portfolio'   => array( 'section' => 'portfolio' ), 
        'client'      => array( 'sidebar' => 'client' ) 
    );

    $enabled_section = array();
    
    foreach( $sections as $k => $v ){
        if( array_key_exists( 'sidebar', $v ) ){
            if( is_active_sidebar( $v['sidebar'] ) ) array_push( $enabled_section, $v['sidebar'] );
        }else{
            if( get_theme_mod( 'ed_' . $v['section'] . '_section', true ) ) array_push( $enabled_section, $v['section'] );
        }
    }  
    
    return apply_filters( 'rara_business_home_sections', $enabled_section );
}

function rara_business_footer_bottom(){ ?>
    <div class="footer-b">      
        <?php
            rara_business_get_footer_copyright();
            
            /* translators: 1: poweredby, 2: link, 3: span tag closed  */
            printf( esc_html__( ' %1$sPowered by %2$s%3$s', 'rara-business' ), '<span class="powered-by">', '<a href="'. esc_url( __( 'https://wordpress.org/', 'rara-business' ) ) .'" target="_blank">ABC Company</a>.', '</span>' );

            if ( function_exists( 'the_privacy_policy_link' ) ) {
                the_privacy_policy_link( '<span class="policy_link">', '</span>');
            }
        ?>      
    </div>
    <?php
}