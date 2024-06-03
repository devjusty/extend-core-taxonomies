<?php
/*
Plugin Name: Extend Core Taxonomies
Description: A plugin to extend core taxonomies to pages and add related posts and pages sidebar blocks.
Version: 1.0
Author: Justin Thompson
*/

// Add settings menu
function extend_core_taxonomies_add_admin_menu()
{
  add_options_page(
    'Extend Core Taxonomies Settings',
    'Extend Core Taxonomies',
    'manage_options',
    'extend-core-taxonomies',
    'extend_core_taxonomies_settings_page'
  );
}
add_action('admin_menu', 'extend_core_taxonomies_add_admin_menu');

// Display settings page
function extend_core_taxonomies_settings_page()
{
?>
  <div class="wrap">
    <h1>Extend Core Taxonomies Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('extend_core_taxonomies_options');
      do_settings_sections('extend-core-taxonomies');
      submit_button();
      ?>
    </form>
  </div>
<?php
}

// Register settings
function extend_core_taxonomies_settings_init()
{
  register_setting('extend_core_taxonomies_options', 'extend_core_taxonomies_options');

  add_settings_section(
    'extend_core_taxonomies_section',
    __('General Settings', 'text_domain'),
    null,
    'extend-core-taxonomies'
  );

  add_settings_field(
    'extend_core_taxonomies_custom_taxonomies',
    __('Enable Custom Taxonomies', 'text_domain'),
    'extend_core_taxonomies_custom_taxonomies_render',
    'extend-core-taxonomies',
    'extend_core_taxonomies_section'
  );

  add_settings_field(
    'extend_core_taxonomies_custom_post_types',
    __('Enable Custom Post Types', 'text_domain'),
    'extend_core_taxonomies_custom_post_types_render',
    'extend-core-taxonomies',
    'extend_core_taxonomies_section'
  );

  add_settings_field(
    'extend_core_taxonomies_custom_queries',
    __('Custom Query Parameters', 'text_domain'),
    'extend_core_taxonomies_custom_queries_render',
    'extend-core-taxonomies',
    'extend_core_taxonomies_section'
  );
}
add_action('admin_init', 'extend_core_taxonomies_settings_init');

// Render settings fields
function extend_core_taxonomies_custom_taxonomies_render()
{
  $options = get_option('extend_core_taxonomies_options');
?>
  <input type="checkbox" name="extend_core_taxonomies_options[custom_taxonomies]" <?php checked(isset($options['custom_taxonomies']), 1); ?> value="1">
  <label for="extend_core_taxonomies_custom_taxonomies"><?php _e('Enable support for custom taxonomies', 'text_domain'); ?></label>
<?php
}

function extend_core_taxonomies_custom_post_types_render()
{
  $options = get_option('extend_core_taxonomies_options');
?>
  <input type="checkbox" name="extend_core_taxonomies_options[custom_post_types]" <?php checked(isset($options['custom_post_types']), 1); ?> value="1">
  <label for="extend_core_taxonomies_custom_post_types"><?php _e('Enable support for custom post types', 'text_domain'); ?></label>
<?php
}
// Render custom queries field
function extend_core_taxonomies_custom_queries_render()
{
  $options = get_option('extend_core_taxonomies_options');
?>
  <textarea name="extend_core_taxonomies_options[custom_queries]" rows="5" cols="50"><?php echo isset($options['custom_queries']) ? esc_textarea($options['custom_queries']) : ''; ?></textarea>
  <p class="description"><?php _e('Enter custom query parameters in JSON format. Example: {"meta_key": "custom_field", "meta_value": "value"}', 'text_domain'); ?></p>
  <?php
}


// Register the plugin and extend taxonomies
function extend_core_taxonomies_init()
{
  $options = get_option('extend_core_taxonomies_options');

  register_taxonomy_for_object_type('category', 'page');
  register_taxonomy_for_object_type('post_tag', 'page');

  if (isset($options['custom_taxonomies']) && $options['custom_taxonomies']) {
    // Add support for custom taxonomies
    register_taxonomy('custom_category', 'page', array(
      'label' => __('Custom Categories'),
      'rewrite' => array('slug' => 'custom-category'),
      'hierarchical' => true,
    ));
  }

  if (isset($options['custom_post_types']) && $options['custom_post_types']) {
    // Add support for custom post types
    register_post_type('custom_post', array(
      'label' => __('Custom Post'),
      'public' => true,
      'rewrite' => array('slug' => 'custom-post'),
      'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
    ));
  }
}
add_action('init', 'extend_core_taxonomies_init');

// Enqueue block assets
function extend_core_taxonomies_enqueue_block_assets()
{
  wp_enqueue_script(
    'extend-core-taxonomies-blocks',
    plugins_url('js/blocks.js', __FILE__),
    array('wp-blocks', 'wp-element', 'wp-editor'),
    filemtime(plugin_dir_path(__FILE__) . 'js/blocks.js')
  );
  wp_enqueue_style(
    'extend-core-taxonomies-block-editor-styles',
    plugins_url('css/style.css', __FILE__),
    array('wp-edit-blocks'),
    filemtime(plugin_dir_path(__FILE__) . 'css/style.css')
  );
}
add_action('enqueue_block_editor_assets', 'extend_core_taxonomies_enqueue_block_assets');

// Register the blocks
function extend_core_taxonomies_register_blocks()
{
  register_block_type('extend-core-taxonomies/related-posts', array(
    'editor_script' => 'extend-core-taxonomies-blocks',
    'style' => 'extend-core-taxonomies-block-editor-styles',
    'render_callback' => 'extend_core_taxonomies_render_related_posts_block',
  ));

  register_block_type('extend-core-taxonomies/related-pages', array(
    'editor_script' => 'extend-core-taxonomies-blocks',
    'style' => 'extend-core-taxonomies-block-editor-styles',
    'render_callback' => 'extend_core_taxonomies_render_related_pages_block',
  ));
}
add_action('init', 'extend_core_taxonomies_register_blocks');

// Render the related posts block
function extend_core_taxonomies_render_related_posts_block()
{
  ob_start();
  the_widget('Related_Posts_Widget');
  return ob_get_clean();
}

// Render the related pages block
function extend_core_taxonomies_render_related_pages_block()
{
  ob_start();
  the_widget('Related_Pages_Widget');
  return ob_get_clean();
}

// Add related posts widget
function register_related_posts_widget()
{
  register_widget('Related_Posts_Widget');
}
add_action('widgets_init', 'register_related_posts_widget');

// Add related pages widget
function register_related_pages_widget()
{
  register_widget('Related_Pages_Widget');
}
add_action('widgets_init', 'register_related_pages_widget');

// Define the related posts widget
class Related_Posts_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct('related_posts_widget', __('Related Posts', 'text_domain'), array('description' => __('Displays related posts', 'text_domain')));
  }

  public function widget($args, $instance)
  {
    $options = get_option('extend_core_taxonomies_options');
    $custom_queries = isset($options['custom_queries']) ? json_decode($options['custom_queries'], true) : array();
    $number_of_posts = !empty($instance['number_of_posts']) ? $instance['number_of_posts'] : 5;

    $query_args = array_merge(
      array(
        'category__in' => wp_get_post_categories(get_the_ID()),
        'posts_per_page' => $number_of_posts,
        'post__not_in' => array(get_the_ID())
      ),
      $custom_queries
    );

    $related_posts = new WP_Query($query_args);

    if ($related_posts->have_posts()) {
      echo $args['before_widget'];
      echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
      echo '<ul>';
      while ($related_posts->have_posts()) {
        $related_posts->the_post();
        echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
      }
      echo '</ul>';
      echo $args['after_widget'];
    }

    wp_reset_postdata();
  }

  public function form($instance)
  {
    $number_of_posts = !empty($instance['number_of_posts']) ? $instance['number_of_posts'] : 5;
  ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('number_of_posts')); ?>"><?php esc_attr_e('Number of posts:', 'text_domain'); ?></label>
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('number_of_posts')); ?>" name="<?php echo esc_attr($this->get_field_name('number_of_posts')); ?>" type="number" value="<?php echo esc_attr($number_of_posts); ?>">
    </p>
  <?php
  }

  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['number_of_posts'] = (!empty($new_instance['number_of_posts'])) ? sanitize_text_field($new_instance['number_of_posts']) : '';
    return $instance;
  }
}

// Define the related pages widget
class Related_Pages_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct('related_pages_widget', __('Related Pages', 'text_domain'), array('description' => __('Displays related pages', 'text_domain')));
  }

  public function widget($args, $instance)
  {
    $options = get_option('extend_core_taxonomies_options');
    $custom_queries = isset($options['custom_queries']) ? json_decode($options['custom_queries'], true) : array();
    $order_by = !empty($instance['order_by']) ? $instance['order_by'] : 'title';

    $query_args = array_merge(
      array(
        'post_type' => 'page',
        'category__in' => wp_get_post_categories(get_the_ID()),
        'orderby' => $order_by,
        'order' => 'ASC'
      ),
      $custom_queries
    );

    $related_pages = new WP_Query($query_args);

    if ($related_pages->have_posts()) {
      echo $args['before_widget'];
      echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
      echo '<ul>';
      while ($related_pages->have_posts()) {
        $related_pages->the_post();
        echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
      }
      echo '</ul>';
      echo $args['after_widget'];
    }

    wp_reset_postdata();
  }

  public function form($instance)
  {
    $order_by = !empty($instance['order_by']) ? $instance['order_by'] : 'title';
  ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('order_by')); ?>"><?php esc_attr_e('Order by:', 'text_domain'); ?></label>
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order_by')); ?>" name="<?php echo esc_attr($this->get_field_name('order_by')); ?>">
        <option value="title" <?php echo ($order_by == 'title') ? 'selected' : ''; ?>>Title</option>
        <option value="date" <?php echo ($order_by == 'date') ? 'selected' : ''; ?>>Date Published</option>
        <option value="modified" <?php echo ($order_by == 'modified') ? 'selected' : ''; ?>>Most Recently Updated</option>
      </select>
    </p>
<?php
  }

  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['order_by'] = (!empty($new_instance['order_by'])) ? sanitize_text_field($new_instance['order_by']) : '';
    return $instance;
  }
}


?>
