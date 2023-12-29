<?php
/*
Plugin Name: Liveblogger
Description: A simple liveblogging plugin using WordPress posts.
Version: 1.0
Author: Systemagic
Author URI: https://systemagic.co/
*/

// Activation Hook
register_activation_hook(__FILE__, 'liveblog_plugin_activate');

// Deactivation Hook
register_deactivation_hook(__FILE__, 'liveblog_plugin_deactivate');

// Activation Function
function liveblog_plugin_activate() {
    // Add activation code here
}

// Deactivation Function
function liveblog_plugin_deactivate() {
    // Add deactivation code here
}

// Add more functions and hooks below

function enqueue_custom_styles() {
    wp_enqueue_style('custom-liveblog-styles', plugins_url('/style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_styles');


function enqueue_custom_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue your custom JavaScript file
    wp_enqueue_script('custom-liveblog-js', plugins_url('liveblog.js', __FILE__), array('jquery'), '1.0', true);
    
}


add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');


// Register the Liveblog custom post type
function register_liveblog_post_type() {
    $labels = array(
        'name' => 'Liveblog Items',
        'singular_name' => 'Liveblog Item',
		'all_items' => 'All Liveblog Posts',
        'add_new' => 'New Liveblog Post',
        'add_new_item' => 'Add New Liveblog Post',
        'edit_item' => 'Edit Liveblog Post',
        'new_item' => 'New Liveblog Post',
        'view_item' => 'View Liveblog Post',
        'search_items' => 'Search Liveblog Posts',
        'not_found' => 'No Liveblog Posts found',
        'not_found_in_trash' => 'No Liveblog Posts found in Trash',
        'parent_item_colon' => 'Parent Liveblog Post:',
		'menu_name' => 'Liveblog',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true, // Display in the admin menu
        'menu_icon' => 'dashicons-megaphone', // Customize the menu icon
        'query_var' => true,
        'rewrite' => array('slug' => 'liveblog'), // URL slug for liveblogs
        'capability_type' => 'post',
        'has_archive' => true, // Enable archive page
        'hierarchical' => false,
        'menu_position' => 20, // Position in the menu
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
    );

    register_post_type('liveblog', $args);
}

add_action('init', 'register_liveblog_post_type');


// Register the "Liveblogs" custom taxonomy
function register_liveblog_taxonomy() {
    $labels = array(
        'name' => 'Liveblogs',
        'singular_name' => 'Liveblog',
        'search_items' => 'Search Liveblogs',
        'popular_items' => 'Popular Liveblogs',
        'all_items' => 'All Liveblogs',
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => 'Edit Liveblog',
        'update_item' => 'Update Liveblog',
        'add_new_item' => 'Add New Liveblog',
        'new_item_name' => 'New Liveblog Name',
        'separate_items_with_commas' => 'Separate Liveblogs with commas',
        'add_or_remove_items' => 'Add or remove Liveblogs',
        'choose_from_most_used' => 'Choose from the most used Liveblogs',
        'menu_name' => 'Liveblogs',
    );

    $args = array(
        'hierarchical' => true, // Categories
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
		'menu_position' => 1, // Position in the menu
        'rewrite' => array('slug' => 'liveblog'), // URL slug for liveblogs
    );

    register_taxonomy('liveblog', 'liveblog', $args);
}

add_action('init', 'register_liveblog_taxonomy');



// Add the settings page
function liveblog_settings_page() {
    add_submenu_page(
        'edit.php?post_type=liveblog',
        'Liveblog Settings',
        'Settings',
        'manage_options',
        'liveblog-settings',
        'liveblog_settings_form'
    );
}
add_action('admin_menu', 'liveblog_settings_page');


// Settings form
function liveblog_settings_form() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle form submission
    if (isset($_POST['html_interval'])) {
        update_option('html_interval', sanitize_text_field($_POST['html_interval']));
    }

    if (isset($_POST['custom_html'])) {
        update_option('custom_html', wp_kses_post($_POST['custom_html']));
    }

    if (isset($_POST['custom_css'])) {
        update_option('custom_css', wp_kses_post($_POST['custom_css']));
    }
    
    if (isset($_POST['refresh_rate'])) { // Handle refresh_rate input
        update_option('refresh_rate', intval($_POST['refresh_rate']));
    }

    // Get the current values
    $html_interval = get_option('html_interval');
    $custom_html = get_option('custom_html');
    $custom_css = get_option('custom_css');
    $refresh_rate = get_option('refresh_rate', 2); // Set default refresh rate

    ?>
    <div class="wrap">
        <h2>Liveblog Settings</h2>
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">HTML Interval</th>
                    <td>
                        <select name="html_interval">
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo '<option value="' . esc_attr($i) . '" ' . selected($html_interval, $i, false) . '>' . esc_html($i) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom HTML Content</th>
                    <td>
                        <textarea name="custom_html" rows="5" cols="50"><?php echo esc_textarea($custom_html); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Autorefresh rate in minutes</th>
                    <td>
                        <input type="number" name="refresh_rate" value="<?php echo esc_attr($refresh_rate); ?>">
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Custom CSS</th>
                    <td>
                        <textarea name="custom_css" rows="5" cols="50" ><?php echo esc_textarea($custom_css); ?></textarea>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


//displays the actual live blog code as shortcodes on the page
add_shortcode('display_liveblog_posts', 'display_liveblog_posts_shortcode');

function display_liveblog_posts_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts);
	
	// Get the HTML interval and custom CSS from the options
    $html_interval = get_option('html_interval', 1); // Default to 1 if not set
    $custom_css = get_option('custom_css', '');
    $custom_html = get_option('custom_html', '');
	$custom_refresh = get_option('refresh_rate', 2);


    if (!empty($atts['category'])) {
        $category = get_term_by('slug', $atts['category'], 'liveblog');
		

        if ($category) {
            $category_id = $category->term_id;
            $args = array(
                'post_type' => 'liveblog',
                'post_status' => 'publish',
                'posts_per_page' => -1, // Display all posts in the category
                'tax_query' => array(
                    array(
                        'taxonomy' => 'liveblog',
                        'field' => 'id',
                        'terms' => $category_id,
                    ),
                ),
            );

            $liveblog_posts = new WP_Query($args);

            if ($liveblog_posts->have_posts()) {
                ob_start();
                $post_count = 0;

               	echo '<div id="liveblog-container">';
				echo '<div id="liveblog-items">';
				// Apply custom CSS
                echo '<style>' . esc_html($custom_css) . '</style>';
				
                while ($liveblog_posts->have_posts()) {
                    $liveblog_posts->the_post();
					
                    // Open a div with the "liveblog-item" class
                    echo '<div class="liveblog-item">';
								
					
                    // Display the post date and time
                    echo '<div class="post-date-time">' . get_the_time() . '</div>';
					
                    // Display the title and content
                    echo '<h2>' . get_the_title() . '</h2>';
					
                    // Display the featured image
                    if (has_post_thumbnail()) {
                        echo '<div class="featured-image">' . get_the_post_thumbnail() . '</div>';
                    }
                    
					// Display the post content with preserved paragraph tags
					$content = apply_filters('the_content', get_the_content());
					echo do_shortcode(wpautop($content)); // Apply paragraph formatting

                    
                    // Close the div
                    echo '</div>';
					
                    // Increment post count
                    $post_count++;

					// Insert custom HTML every nth post
                    if ($post_count % $html_interval === 0) {
                        echo '<div class="custom-html">' . wp_kses_post($custom_html) . '</div>';
                    }
                }
				
                // Close the container div
                echo '</div>';
				echo '</div>';
				
                wp_reset_postdata();
				
                ?>
                <script>
				var customRefresh = <?php echo esc_js($custom_refresh); ?>;
				
                function loadLiveBlogItems() {
   					var currentURL = '<?php echo esc_url(get_permalink()); ?>';
    
						jQuery.ajax({
						url: currentURL,
						type: 'GET',
						success: function (data) {
							// Find the liveblog content in the loaded data
							var newLiveBlogContent = jQuery(data).find('#liveblog-items');

							// Replace the existing liveblog content with the newly loaded content
							jQuery("#liveblog-container").html(newLiveBlogContent);

							console.log("Refresh completed");
							console.log(currentURL);
						}
					});
				}


                // Call the function initially
                loadLiveBlogItems();

                // Schedule the function to run at set intervals
                var randomTime = Math.floor(Math.random() * 60000) + (customRefresh * 60000);
//                 setInterval(loadLiveBlogItems, randomTime);
//                 console.log(randomTime);
                </script>
                <?php

                return ob_get_clean();
            }
        }
    }

    return 'No liveblog posts found.';
}


// Add a new column to the liveblog categories table
function add_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_edit-liveblog_columns', 'add_shortcode_column');


// Populate the Shortcode column with the shortcode for each category
function populate_shortcode_column($content, $column_name, $term_id) {
    if ($column_name === 'shortcode') {
        // Retrieve the category (term) slug
        $category = get_term($term_id, 'liveblog');
        $shortcode = '[display_liveblog_posts category="' . $category->slug . '"]';
        return esc_html($shortcode);
    }
    return $content;
}
add_filter('manage_liveblog_custom_column', 'populate_shortcode_column', 10, 3);







