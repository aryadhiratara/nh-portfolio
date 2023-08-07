<?php

namespace NhPortfolio;

defined('ABSPATH') or die('Access denied.');

class Core {
	
    public static function init() {
        
        // Register the custom post type
        add_action( 'init', array( __CLASS__, 'register_portfolio_post_type' ) );
        
        // Flush rewrite rules on 'save_post' hook when a new portfolio is published
        add_action( 'save_post', array( __CLASS__, 'flush_rewrite_rules_on_publish' ), 10, 2 );

        // Register the custom taxonomy
        add_action( 'init', array( __CLASS__, 'register_portfolio_taxonomy' ) );
        
        // Add custom metabox
        add_action('add_meta_boxes', array(__CLASS__, 'add_portfolio_meta_box'));
        
        // Save the metabox values
        add_action('save_post', array(__CLASS__, 'save_portfolio_meta_box'));
        
        // Add the portfolio meta html to the content of singular('portfolio')
        add_filter( 'the_content', array( __CLASS__, 'add_portfolio_metas_to_content' ), 1 );
        
        // Add the portfolio category html to the content of singular('portfolio')
        add_filter( 'the_content', array( __CLASS__, 'add_portfolio_categories_to_content' ) );
        
        // Add the portfolio category html to the excerpt of portfolio archives
        add_filter( 'the_title', array( __CLASS__, 'add_portfolio_categories_after_post_title' ), 10, 2 );
        
        // load portfolio assets (css)
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_portfolio_assets'));
        
    }

    public static function register_portfolio_post_type() {
        
        $portfolio_description = apply_filters('nh_portfolio_description', 'What I do (Portfolio)'); // create filters so user can change it later

	    register_post_type( 'portfolio', array(
	        'labels' => array(
	            'name' => 'Portfolios', // Plural name
	            'singular_name' => 'Portfolio', // Singular name
	            'add_new' => 'Add New', // Add new post label
	            'add_new_item' => 'Add New Portfolio', // Add new post editor title
	            'edit_item' => 'Edit Portfolio', // Edit new post editor title
	            'new_item' => 'New Portfolio', // New item text
	            'view_item' => 'View Portfolio', // View item text
	            'search_items' => 'Search Portfolios', // Search text
	            'not_found' => 'No portfolios found', // Not found text
	            'not_found_in_trash' => 'No portfolios found in Trash', // Not found in trash text
	            'parent_item_colon' => 'Parent Portfolio:', // Parent text
	            'menu_name' => 'Portfolios', // Sidebar menu name
	            'all_items' => 'All Portfolios', // All items sidebar menu name
	        ),
	        'public' => true, // Set the availability to public
	        'has_archive' => true, // Set the post type to have archive
	        'rewrite' => array( 'slug' => 'portfolio' ), // Custom slug
	        'show_in_rest' => true, // Enabling block editor in the post editor
	        'hierarchical' => false, // Set the post type hierarchy to false so it has behavior like the regular post
	        'description' => $portfolio_description, // get the post type description from $portfolio_description value
	        'publicly_queryable' => true, // Set the post type to be able to be queried publicly.
	        'query_var' => true, // Sets the query variable for this post type.
	        'menu_icon' => 'dashicons-portfolio', // Set the sidebar menu icon
	        'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ), // Set the post features supported by this post type.
	    ));

	}
    
    public static function flush_rewrite_rules_on_publish( $post_id, $post ) {
        if ( 'portfolio' === $post->post_type && 'publish' === $post->post_status ) {
            flush_rewrite_rules();
        }
    }

    public static function register_portfolio_taxonomy() {

	    register_taxonomy( 'portfolio-category', 'portfolio', array(
	        'labels' => array(
	            'name' => 'Categories', // Plural name
	            'singular_name' => 'Category', // Singular name
	            'add_new' => 'Add New', // Add new post label
	            'add_new_item' => 'Add New Portfolio Category', // Add new category title
	            'edit_item' => 'Edit Portfolio Category', // Edit new post editor title
	            'search_items' => 'Search Portfolio Categories', // Search text
	            'parent_item' => 'View Portfolio', // Set parent taxonomy label
	            'parent_item_colon' => 'Parent Portfolio:', // Set parent taxonomy dropdown label
	            'menu_name' => 'Categories', // Sidebar menu name
	            'all_items' => 'All Portfolios', // All items sidebar menu name
	        ),
	        'hierarchical' => true, // Set the post type hierarchy to false so it has behavior like the category of regular post.
	        'public' => true, // Set the availability to public
	        'show_ui' => true, // Whether to show the taxonomy user interface in the admin
	        'rewrite' => array( 'slug' => 'portfolio-category' ), // Custom slug
	        'show_in_rest' => true, // Enabling block editor in the post editor
	        'show_admin_column' => true, // show the category column in portfolio list
	    ));

	}

    
    public static function add_portfolio_meta_box() {
        
        add_meta_box(
            'nh_portfolio_meta_box', // Meta box ID (unique identifier)
            'Portfolio Details', // Meta box title displayed in the admin
            array(__CLASS__, 'render_portfolio_meta_box'), // Callback function to render the content of the meta box
            'portfolio', // The post type to which this meta box should be added
            'normal', // The context in which the meta box should be displayed
            'high' // The priority in which the meta box should be displayed
        );
        
    }
	
	public static function render_portfolio_meta_box($post) {
	    
	    // Retrieve the current values of the meta fields
	    $company = get_post_meta($post->ID, 'portfolio_company', true); // Get the value of 'portfolio_company' meta field for the current post.
        $position = get_post_meta($post->ID, 'portfolio_position', true); // Get the value of 'portfolio_position' meta field for the current post.
        $url = get_post_meta($post->ID, 'portfolio_url', true); // Get the value of 'portfolio_url' meta field for the current post.
        $url_label = get_post_meta($post->ID, 'portfolio_url_label', true); // Get the value of 'portfolio_url_label' meta field for the current post.
        $start_date = get_post_meta($post->ID, 'portfolio_start_date', true); // Get the value of 'portfolio_start_date' meta field for the current post.
        $end_date = get_post_meta($post->ID, 'portfolio_end_date', true); // Get the value of 'portfolio_end_date' meta field for the current post.
	
	    // Output the input fields to editor
	    ?>
	    <style>
	        .metabox-container.grid {
	            display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-column-gap: 1.5%;
	        }
	        .metabox-input {
	            width: 100%;
	            margin: 4px 0 10px;
	        }
	    </style>
	    <div class="metabox-container grid"> <!-- parent grid container -->
	        <!-- Company input container -->
    	    <div>
        	    <label for="portfolio_company">Company:</label>
    			<input type="text" id="portfolio_company" class="metabox-input" name="portfolio_company" placeholder="<?php _e( 'Company' ); ?>" value="<?php echo esc_attr($company); ?>">
    	    </div>
	        <!-- Position input container -->
    	    <div>
        	    <label for="portfolio_position">Position:</label>
    	        <input type="text" id="portfolio_position" class="metabox-input" name="portfolio_position" placeholder="<?php _e( 'Job Title / Position' ); ?>" value="<?php echo esc_attr($position); ?>">
    	    </div>
	    </div>
	    <div class="metabox-container grid"> <!-- parent grid container -->
	        <!-- URL input container -->
	        <div>
        	    <label for="portfolio_url">URL:</label>
        	    <input type="text" id="portfolio_url" class="metabox-input" name="portfolio_url" placeholder="<?php _e( 'Company URL / Portfolio Demo URL' ); ?>" value="<?php echo esc_url($url); ?>">
    	    </div>
    	    <!-- URL Label input container -->
    	    <div>
        	    <label for="portfolio_url_label">URL Label:</label>
        	    <input type="text" id="portfolio_url_label" class="metabox-input" name="portfolio_url_label" placeholder="<?php _e( 'Url Label' ); ?>" value="<?php echo esc_attr($url_label); ?>">
    	    </div>
	    </div>
	    <div class="metabox-container grid"> <!-- parent grid container -->
	        <!-- Start date input container -->
	        <div>
                <label for="portfolio_start_date">Start Date:</label>
                <input type="date" id="portfolio_start_date" class="metabox-input" name="portfolio_start_date" value="<?php echo esc_attr($start_date); ?>">
            </div>
            <!-- Start date input container -->
            <div>
                <label for="portfolio_end_date">End Date:</label>
                <input type="date" id="portfolio_end_date" class="metabox-input" name="portfolio_end_date" value="<?php echo esc_attr($end_date); ?>">
            </div>
	    </div>
	    <?php // <-- PHP opening tag
	}
	
	public static function save_portfolio_meta_box($post_id) {
	    
	    // Save the custom fields when the post is saved/updated
	    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	        return;
	    }
	    
	    // Example of user role check If user can't edit post, the modified metabox will not be saved
	    if (!current_user_can('edit_post', $post_id)) {
	        return;
	    }
	    
	    // Save the company field
	    if (isset($_POST['portfolio_company'])) {
	        update_post_meta($post_id, 'portfolio_company', sanitize_text_field($_POST['portfolio_company']));
	    }
	
	    // Save the position field
	    if (isset($_POST['portfolio_position'])) {
	        update_post_meta($post_id, 'portfolio_position', sanitize_text_field($_POST['portfolio_position']));
	    }
	
	    // Save the url field
	    if (isset($_POST['portfolio_url'])) {
	        update_post_meta($post_id, 'portfolio_url', esc_url_raw($_POST['portfolio_url']));
	    }
	    
	    // Save the url label field
	    if (isset($_POST['portfolio_url_label'])) {
	        update_post_meta($post_id, 'portfolio_url_label', sanitize_text_field($_POST['portfolio_url_label']));
	    }
	
	    // Save the start date field
	    if (isset($_POST['portfolio_start_date'])) {
	        update_post_meta($post_id, 'portfolio_start_date', sanitize_text_field($_POST['portfolio_start_date']));
	    }
	
	    // Save the end date field
	    if (isset($_POST['portfolio_end_date'])) {
	        update_post_meta($post_id, 'portfolio_end_date', sanitize_text_field($_POST['portfolio_end_date']));
	    }
	}
	
	public static function add_portfolio_metas_to_content($content) {
	    
        if (is_singular('portfolio') && in_the_loop()) {
            // Retrieve the current values of the meta fields
            $post_id = get_the_ID();
            
            $company = get_post_meta($post_id, 'portfolio_company', true);
            $position = get_post_meta($post_id, 'portfolio_position', true);
            $url = get_post_meta($post_id, 'portfolio_url', true);
            $url_label = get_post_meta($post_id, 'portfolio_url_label', true);
            $start_date = get_post_meta($post_id, 'portfolio_start_date', true);
            $end_date = get_post_meta($post_id, 'portfolio_end_date', true);
            
            // Format the dates to display in the "dd-mm-yyyy" format
            if (!empty($start_date )) {
                $formatted_start_date = date('d-m-Y', strtotime($start_date));
            } else {
                $formatted_start_date = null;
            }
            
            if (!empty($end_date )) {
                $formatted_end_date = date('d-m-Y', strtotime($end_date));
            } else {
                $formatted_end_date = 'Now';
            }
            
            $meta_html = ''; // initiate the html output
            
            if ( !empty($company) || !empty($position) || !empty($url) || !empty($start_date) ) {
            
                $meta_html .= '<div class="nh-portfolio-metas-container">'; // start meta container
                
                if (!empty($company)) {
                    $meta_html .= '<div class="company-container"><span class="prefix">Company:</span> <span class="name">' . esc_html($company) . '</span></div>';
                }
                
                if (!empty($position)) {
                    $meta_html .= '<div class="position-container"><span class="prefix">Position:</span> <span class="name">' . esc_html($position) . '</span></div>';
                }
                
                if (!empty($url)) {
                    $meta_html .= '<div class="url-container"><span class="prefix">' . esc_html($url_label) . '</span> <a class="url" href="' . esc_url($url) . '">' .esc_url($url) . '</a></div>';
                }
                
                if (!empty($start_date)) {
                    $meta_html .= '<div class="date-container">';
                    $meta_html .= '<div class="start-date"><span class="prefix">From:</span> <span class="name">' . esc_html($formatted_start_date) . '</span></div>';
                    $meta_html .= '<div class="end-date"><span class="prefix">To:</span> <span class="name">' . esc_html($formatted_end_date) . '</span></div>';
                    $meta_html .= '</div>';
                }
        
                $meta_html .= '</div>';  // end meta container
            
            }
    
            $content = $meta_html . $content;  // output $meta_html before $content
        }
    
        return $content;
    }
    
    public static function add_portfolio_categories_to_content($content) {
        
        if (is_singular('portfolio') && in_the_loop()) {
            // Get the portfolio categories for the post
            $terms = get_the_terms(get_the_ID(), 'portfolio-category');
    
            // Display the category before the content
            if (!empty($terms) && !is_wp_error($terms)) {
                
                $category_names = array();
                $category_links = array();
                
                foreach ($terms as $term) {
                    $category_names[] = $term->name;
                    $category_links[] = get_term_link($term);
                }
                
                $category_name = implode( ', ', $category_names );
                $category_link = implode( ', ', $category_links );
    
                $category_html = '<div class="nh-portfolio-category-container">';
                $category_html .= '<span class="prefix">Category: </span>';
                $category_html .= '<a class="name" href="' . esc_url($category_link) . '">' . esc_html($category_name) . '</a>';
                $category_html .= '</div>';
    
                $content = $category_html . $content;
            }
        }
    
        return $content;
        
    }
    
    public static function add_portfolio_categories_after_post_title( $title, $post_id ) {
        
        if ( is_post_type_archive( 'portfolio' ) && in_the_loop() ) {
            // Get the portfolio categories for the post
            $terms = get_the_terms( $post_id, 'portfolio-category' );
    
            // Display the category after the title
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    
                $category_names = array();
                $category_links = array();
    
                foreach ( $terms as $term ) {
                    $category_names[] = $term->name;
                    $category_links[] = get_term_link( $term );
                }
    
                $category_name = implode( ', ', $category_names );
                $category_link = implode( ', ', $category_links );
    
                $category_html = '<div class="nh-portfolio-category-container">';
                $category_html .= '<a class="name" href="' . esc_url( $category_link ) . '">' . esc_html( $category_name ) . '</a>';
                $category_html .= '</div>';
    
                $title .= $category_html;
            }
        }
    
        return $title;
    }
    
    public static function enqueue_portfolio_assets() {
        
        // register the style
        wp_register_style( NH_PORTFOLIO_SLUG.'-style', NH_PORTFOLIO_PUBLIC_ASSETS_URL . 'css/style.css', [], NH_PORTFOLIO_VERSION);
        
        // enqueue the style to portfolio post type and archive only
        if ( is_singular('portfolio') || is_post_type_archive( 'portfolio' ) ) {
            wp_enqueue_style( NH_PORTFOLIO_SLUG.'-style' );
        }
    }

    // Activation hook callback
    public function activate() {
        // Flush rewrite rules to ensure the post type and taxonomy are correctly registered on activation
        flush_rewrite_rules();
    }

    // Deactivation hook callback
    public function deactivate() {
        // Unregister the post type and taxonomy (optional, only if you want to remove them on deactivation)
        unregister_post_type( 'portfolio' );
        unregister_taxonomy( 'portfolio-category' );
        // Flush rewrite rules to ensure the post type and taxonomy are correctly deregistered on deactivation
        flush_rewrite_rules();
    }
    
}