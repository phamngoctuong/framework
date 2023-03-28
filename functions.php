<?php
// Lionel
define('PW_SAMPLE_PLUGIN_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "hd-quiz". DIRECTORY_SEPARATOR);
define('PW_SAMPLE_PLUGIN_DIR_L', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "hd-quiz-save-results-light". DIRECTORY_SEPARATOR);
define('PW_SAMPLE_PLUGIN_URL', plugins_url("hd-quiz/"));
if (!class_exists('Gamajo_Template_Loader')) {
  require PW_SAMPLE_PLUGIN_DIR . 'class-gamajo-template-loader.php';
}
require PW_SAMPLE_PLUGIN_DIR . 'class-pw-template-loader.php';
function hdq_add_shortcode_edit($atts) {
  // Attributes
  extract(
    shortcode_atts(
      array(
        'quiz' => '',
      ),
      $atts
    )
  );
  // Code
  ob_start();
  include PW_SAMPLE_PLUGIN_DIR . 'includes/template-edit.php';
  return ob_get_clean();
}
add_shortcode('HDquizs', 'hdq_add_shortcode_edit', 30);
remove_action('wp_ajax_hdq_load_question', 'hdq_load_question');
add_action('wp_ajax_hdq_load_question', 'hdq_load_question_edit');
function hdq_load_question_edit() {
  if (hdq_user_permission()) {
    $hdq_nonce = sanitize_text_field($_POST['nonce']);
    if (wp_verify_nonce($hdq_nonce, 'hdq_quiz_nonce') != false) {
      // permission granted
      // send the correct file to load data from
      include PW_SAMPLE_PLUGIN_DIR . 'includes/settings/question-edit.php';
    } else {
      echo 'error: Nonce failed to validate'; // failed nonce
    }
  } else {
    echo 'error: You have insufficient user privilege'; // insufficient user privilege
  }
  die();
}
// Lionel
function ecademy_enqueue_style() {
  global $post;
  $passs = get_post_meta( $post->ID, 'passwordpost', true );
  $textalert = get_post_meta( $post->ID, 'textalert', true );
  $aw_custom_image = get_post_meta( $post->ID, 'aw_custom_image', true );
  $image = wp_get_attachment_image_src($aw_custom_image,'full');
  wp_enqueue_style("custom-css", get_stylesheet_directory_uri() . "/assets/css/custom.css", array("twenty-twenty-one-style"), 101);
  wp_enqueue_script('stickey_js', get_stylesheet_directory_uri() . '/assets/js/stickey.min.js', array('jquery'), '1.0.0', true);
  wp_localize_script( 'stickey_js', 'add_variable_stickey_js' , array( 'post_id' => $passs, 'textalert' => $textalert, 'aw_custom_image' => $image ) );
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'ecademy_enqueue_style');
add_action('admin_enqueue_scripts', 'safely_add_stylesheet_to_admin');
function safely_add_stylesheet_to_admin() {
  wp_enqueue_style('admin-css', get_stylesheet_directory_uri() . '/assets/css/admincss.css', null, '123', 'all');
  if (!did_action('wp_enqueue_media')) {
    wp_enqueue_media();
  }
  wp_enqueue_script('customadmin_js', get_stylesheet_directory_uri() . '/assets/js/customadmin.js', array('jquery'), null, false);
}
add_filter('big_image_size_threshold', '__return_false');
add_filter('intermediate_image_sizes', 'remove_default_img_sizes', 10, 1);
function remove_default_img_sizes($sizes) {
  $targets = ['medium', 'large','hd_qu_size2','post-thumbnail','medium_large', '1536x1536', '2048x2048', 'sidebar-featured', 'genesis-singular-images', 'gb-block-post-grid-landscape', 'gb-block-post-grid-square'];
  foreach ($sizes as $size_index => $size) {
    if (in_array($size, $targets)) {
      unset($sizes[$size_index]);
    }
  }
  return $sizes;
}
add_filter('wp_page_revisions_to_keep', 'callback_wp_revisions_to_keep', 10, 2);
add_filter('wp_post_revisions_to_keep', 'callback_wp_revisions_to_keep', 10, 2);
function callback_wp_revisions_to_keep($num, $post) {
  return 2;
}
function hdq_register_settings_page_edit(){
  $addon_text = "";
  $new_addon = get_option("hdq_new_addon");
  if ($new_addon != null && $new_addon != "") {
    $new_addon = array_map("sanitize_text_field", $new_addon);
    if ($new_addon[0] === "yes") {
      $addon_text = ' <span class="awaiting-mod">NEW</span>';
    }
  }
  add_submenu_page('hdq_quizzes', 'Quizzes', 'Quizzes', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback_edit');
  add_submenu_page('hdq_quizzes', 'Quiz About', 'About / Options', 'publish_posts', 'hdq_options', 'hdq_register_settings_page_callback');
  add_submenu_page('hdq_quizzes', 'Addons', 'Addons' . $addon_text, 'manage_options', 'admin.php?page=hdq_addons');
  add_submenu_page('hdq_quizzes', 'Tools', 'Tools', 'manage_options', 'admin.php?page=hdq_tools');
  add_submenu_page('hdq_quizzes', 'Trivia Packs', '<span title = "Purchase trivia packs" style = "border-top: 1px dashed #999; padding-top: 0.6em; display: grid; grid-template-columns: max-content 1fr; column-gap: 1em; align-items: center;"><span class="dashicons dashicons-cart"></span> Trivia Packs</span>', 'manage_options', 'admin.php?page=hdq_triviadb');
}
add_action( 'admin_menu', 'wpdocs_remove_menus',  99);
function wpdocs_remove_menus(){
  // remove_menu_page( 'plugins.php' );
  remove_menu_page( 'tools.php' );
  remove_menu_page( 'themes.php' );
  remove_menu_page( 'options-general.php' );
  remove_menu_page( 'under-construction' );
  remove_submenu_page( 'hdq_quizzes','admin.php?page=hdq_addons');
  remove_submenu_page( 'hdq_quizzes','hdq_options');
  remove_submenu_page( 'hdq_quizzes','admin.php?page=hdq_triviadb');
  remove_submenu_page( 'hdq_quizzes','admin.php?page=hdq_tools');
  remove_submenu_page( 'hdq_quizzes','hdq_quizzes');
}
function hdq_register_quizzes_page_callback_edit(){
  require PW_SAMPLE_PLUGIN_DIR. 'includes/hdq_quizzes_edit.php';
}
remove_action('init', 'hdq_create_settings_page');
add_action('init', 'hdq_create_settings_page_edit',11);
function hdq_create_settings_page_edit() {
  if (hdq_user_permission()) {
    function hdq_register_quizzes_page_edit() {
      add_menu_page('Quiz', 'Quiz', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback_edit', 'dashicons-clipboard', 5);
      add_menu_page('Quiz Addons', 'HDQ Addons', 'edit_posts', 'hdq_addons', 'hdq_register_addons_page_callbak', '', 99);
      add_menu_page('Quiz Tools', 'HDQ Tools', 'edit_posts', 'hdq_tools', 'hdq_register_tools_page_callbak', '', 99);
      add_menu_page('Quiz Tools - CSV Importer', 'HDQ Tools CSV', 'edit_posts', 'hdq_tools_csv_importer', 'hdq_register_tools_csv_importer_page_callback', '', 99);
      add_menu_page('Quiz Tools - Data Upgrade', 'HDQ Tools DATA', 'edit_posts', 'hdq_tools_data_upgrade', 'hdq_register_tools__data_upgrade_page_callback', '', 99);
      add_menu_page('Trivia Packs', 'Trivia Packs', 'edit_posts', 'hdq_triviadb', 'hdq_register_triviadb_page_callback', '', 99);
      remove_menu_page('hdq_addons');
      remove_menu_page('hdq_tools');
      remove_menu_page('hdq_tools_csv_importer');
      remove_menu_page('hdq_tools_data_upgrade');
      remove_menu_page('hdq_triviadb');
    }
    add_action('admin_menu', 'hdq_register_quizzes_page_edit');
    function hdq_register_settings_page_edit_inner() {
      $addon_text = "";
      $new_addon  = get_option("hdq_new_addon");
      if ($new_addon != null && $new_addon != "") {
        $new_addon = array_map("sanitize_text_field", $new_addon);
        if ($new_addon[0] === "yes") {
          $addon_text = ' <span class="awaiting-mod">NEW</span>';
        }
      }
      add_submenu_page('hdq_quizzes', 'Quizzes', 'Quizzes', 'publish_posts', 'hdq_quizzes', 'hdq_register_quizzes_page_callback_edit');
      add_submenu_page('hdq_quizzes', 'Quiz About', 'About / Options', 'publish_posts', 'hdq_options', 'hdq_register_settings_page_callback');
      add_submenu_page('hdq_quizzes', 'Addons', 'Addons' . $addon_text, 'manage_options', 'admin.php?page=hdq_addons');
      add_submenu_page('hdq_quizzes', 'Tools', 'Tools', 'manage_options', 'admin.php?page=hdq_tools');
      add_submenu_page('hdq_quizzes', 'Trivia Packs', '<span title = "Purchase trivia packs" style = "border-top: 1px dashed #999; padding-top: 0.6em; display: grid; grid-template-columns: max-content 1fr; column-gap: 1em; align-items: center;"><span class="dashicons dashicons-cart"></span> Trivia Packs</span>', 'manage_options', 'admin.php?page=hdq_triviadb');
    }
    add_action('admin_menu', 'hdq_register_settings_page_edit_inner', 11);
  }
  $hdq_version = sanitize_text_field(get_option('HDQ_PLUGIN_VERSION'));
  if ($hdq_version != "" && $hdq_version != null && $hdq_version < "1.8") {
    update_option("hdq_remove_data_upgrade_notice", "yes");
    update_option("hdq_data_upgraded", "occured");
    hdq_update_legacy_data();
  } else {
    update_option("hdq_data_upgraded", "all good");
  }
  if (HDQ_PLUGIN_VERSION != $hdq_version) {
    update_option('HDQ_PLUGIN_VERSION', HDQ_PLUGIN_VERSION);
    // start new addon cron. Runs once a day
    // wp_schedule_event(time() + 30, "daily", "hdq_check_for_updates");
    function hdq_show_upgrade_message() {
    ?>
    <div class='notice notice-success is-dismissible'>
      <p><strong>Announcing a new partnership with the Trivia Company</strong>.</p>
      <p>Quiz has partnered with the Trivia Company to provide question packs that can be purchased and imported into Quiz.</p>
      <a href="<?php echo get_admin_url(null, "?page=hdq_triviadb"); ?>" title="Learn More" class="hdq_button2" style="background-color: #66cbff; color: #000; font-weight: bold; cursor: pointer; padding: 1rem; display: inline-block; line-height: 1; border: 1px solid #222;">Learn more</a>
    </div>
    <?php
      }
      add_action('admin_notices', 'hdq_show_upgrade_message');
  }
}
function wpse_remove_edit_post_link( $link ) {
    return '';
}
add_filter('edit_post_link', 'wpse_remove_edit_post_link');
remove_action('wp_ajax_hdq_load_quiz', 'hdq_load_questions_page');
function hdq_load_questions_page_edit() {
  if (hdq_user_permission()) {
      $hdq_nonce = sanitize_text_field($_POST['nonce']);
      if (wp_verify_nonce($hdq_nonce, 'hdq_quiz_nonce') != false) {
        // permission granted
        // send the correct file to load data from
        include PW_SAMPLE_PLUGIN_DIR . 'includes/settings/questions-edit.php';
      } else {
        echo 'error: Nonce failed to validate'; // failed nonce
      }
  } else {
      echo 'error: You have insufficient user privilege'; // insufficient user privilege
  }
  die();
}
add_action('wp_ajax_hdq_load_quiz', 'hdq_load_questions_page_edit');
// hide update notifications
function remove_core_updates(){
  global $wp_version;
  return(object) array('last_checked'=> time(),'version_checked'=> $wp_version);
}
add_filter('pre_site_transient_update_core','remove_core_updates'); //hide updates for WordPress itself
add_filter('pre_site_transient_update_plugins','remove_core_updates'); //hide updates for all plugins
add_filter('pre_site_transient_update_themes','remove_core_updates'); //hide updates for all themes
add_filter( 'plugins_auto_update_enabled', '__return_false' ); //  disable automatic updates for WordPress plugins
add_filter( 'themes_auto_update_enabled', '__return_false' ); //  disable automatic updates for WordPress themes
// Remove the WordPress version from some .css/.js files
add_filter( 'style_loader_src', 'sdt_remove_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'sdt_remove_ver_css_js', 9999 );
function sdt_remove_ver_css_js( $src ) {
  if ( strpos( $src, 'ver=' ) ) $src = remove_query_arg( 'ver', $src );
  return $src;
}
function custom_get_bloginfo( $output = '', $show = '' ) {
  switch( $show ) {
    case 'version':
      $output = "Latest";
      break;
  }
  return $output;
}
add_filter( 'bloginfo', 'custom_get_bloginfo', 10, 2 );
define( 'wp_auto_update_core', false );
// Disable use XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );
// Disable X-Pingback to header
add_filter( 'wp_headers', 'disable_x_pingback' );
function disable_x_pingback( $headers ) {
    unset( $headers['X-Pingback'] );
return $headers;
}
add_filter('hdq_add_quiz_tab','fc_hdq_add_quiz_tab_callback');
function fc_hdq_add_quiz_tab_callback($params) {
  unset($params[1]);
  unset($params[3]);
  return $params;
}
function wpb_greeting_shortcode() { 
  global $countex, $quiz_timer, $totalhow, $averaged;
  $html = "";
  $html .= '<div id="hdq_meta_forms">';
    $html .= '<div id="hdq_wrapper">';
      $html .= '<div id="hdq_form_wrapper">';
        $html .= '<form>';
          $html .= '<h2 class="hdq_form_wrapper-title">'.__("Quiz Results","twentyfifteenchild").'</h2>';
          $html .= '<div id="hdq_tab_content" class="hdq_tab">';
            $data = get_option("hdq_quiz_results_l");
            $datameta = get_term_meta(3,'quiz_data');
            foreach ($datameta as $index => $value) {
              $quiz_id = $datameta[$index]['quiz_id']['value'];
              $quiz_pass_percentage = $datameta[$index]['quiz_pass_percentage']['value'];
              $quiz_timer = $datameta[$index]['quiz_timer']['value'];
            }
            $data = json_decode(html_entity_decode($data), true);
            $countex =  count($data);
            $total = 0;
            if (!empty($data)) {
                $total = count($data);
                if ($total > 1000) {
                    $total = 1000;
                }
            }
            $html .= '<table class="hdq_a_light_table">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th>'.__("Quiz Name","twentyfifteenchild").'</th>';
                        $html .= '<th>'.__("Datetime","twentyfifteenchild").'</th>';
                        $html .= '<th>'.__("Score","twentyfifteenchild").'</th>';
                        $html .= '<th>'.__("User","twentyfifteenchild").'</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                if ($data != "" && $data != null) {
                  $data = array_reverse($data);
                  $x = 0;
                  $totalhow = $data[0]["score"][1];
                  foreach ($data as $d) {
                    $x++;
                    $d["quizName"] = sanitize_text_field($d["quizName"]);
                    $d["datetime"] = sanitize_text_field($d["datetime"]);
                    $d["quizTaker"][1] = sanitize_text_field($d["quizTaker"][1]);
                    $d["score"][0] = intval($d["score"][0]);
                    $d["score"][1] = intval($d["score"][1]);
                    $d["passPercent"] = intval($d["passPercent"]);
                    $passFail = "fail";
                    $averaged += $d["score"][0] / $d["score"][1] * 100 / $countex;
                    if($averaged) $averaged = round($averaged, 2);
                    if ($d["score"][0] / $d["score"][1] * 100 >= $d["passPercent"]) {
                      $passFail = "pass";
                    } 
                    $html .= '<tr class="'.$passFail.'">';
                      $html .= '<td>'.$d["quizName"].'</td>';
                      $html .= '<td>'.$d["datetime"].'</td>';
                      $html .= '<td>'.$d["score"][0].'/'.$d["score"][1].'</td>';
                      $html .= '<td>'.$d["quizTaker"][1].'</td>';
                    $html .= '</tr>';
                    if ($x >= 1000) {
                      break;
                    }
                  }
                }
                $html .= '</tbody>';
            $html .= '</table>';
          $html .= '</div>';
        $html .= '</form>';
      $html .= '</div>';
    $html .= '</div>';
  $html .= '</div>';
  return $html;
} 
add_shortcode('greeting', 'wpb_greeting_shortcode'); 
add_shortcode('greetingfront', 'wpb_greetingfront_shortcode');
function wpb_greetingfront_shortcode() {
  global $countex, $quiz_timer, $totalhow, $averaged;
  $averaged = $averaged . " %";
  $html = "";
  $html .= '<ul class="iq-test__param">';
    $html .= '<li>';
    $html .= '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $html .= '<path d="M15.75 9C15.75 5.27208 12.7279 2.25 9 2.25C5.27208 2.25 2.25 5.27208 2.25 9C2.25 12.7279 5.27208 15.75 9 15.75C12.7279 15.75 15.75 12.7279 15.75 9Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M9 9V6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M6 6L9 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '</svg>';
    $html .= "".__("About ","twentyfifteenchild")." $quiz_timer ".__(" minutes","twentyfifteenchild")." ";
    $html .= '</li>';
    $html .= '<li>';
    $html .= '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $html .= '<path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M6.8175 6.74994C6.99383 6.24869 7.34187 5.82602 7.79997 5.55679C8.25807 5.28756 8.79668 5.18914 9.32039 5.27897C9.8441 5.3688 10.3191 5.64108 10.6613 6.04758C11.0035 6.45409 11.1908 6.96858 11.19 7.49994C11.19 8.99994 8.94 9.74994 8.94 9.74994" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M9 12.75H9.0075" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '</svg>';
    $html .= "$totalhow " . __("questions","twentyfifteenchild");
    $html .= '</li>';
    $html .= '<li>';
    $html .= '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $html .= '<path d="M9 15V7.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M13.5 15V3" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '<path d="M4.5 15V12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '</svg>';
    $html .= '<span> '.__("Total tested","twentyfifteenchild").' '.$countex.'</span>';
    $html .= '</li>';
    $html .= '<li>';
    $html .= '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">';
    $html .= '<path d="M9 1.5L11.3175 6.195L16.5 6.9525L12.75 10.605L13.635 15.765L9 13.3275L4.365 15.765L5.25 10.605L1.5 6.9525L6.6825 6.195L9 1.5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>';
    $html .= '</svg>';
    $html .= "$averaged";
    $html .= '</li>';
    $html .= '</ul>';
  return $html;
}
remove_action('init', 'hdq_a_light_create_settings_page');
add_action('init', 'hdq_a_light_create_settings_page_ct');
function hdq_a_light_create_settings_page_ct()
{
    function hdq_a_light_register_settings_page_ct()
    {
        add_submenu_page('hdq_quizzes', 'Results', 'Results', 'publish_posts', 'hdq_results', 'hdq_a_light_register_quizzes_page_callback_ct');
    }
    add_action('admin_menu', 'hdq_a_light_register_settings_page_ct', 11);
}
function hdq_a_light_register_quizzes_page_callback_ct() {
  require PW_SAMPLE_PLUGIN_DIR_L . 'includes/results-edit.php';
}
// Start Add the custom columns to the post post type:
//Register Meta box
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'wpdocs-id', __( 'Password', 'twentyfifteenchild' ), 'wpdocs_field_password', array('post','page'), 'side','core' );
} );
//Meta callback function
function wpdocs_field_password( $post ) {
  $passwordpost = get_post_meta( $post->ID, 'passwordpost', true );
  $textalert = get_post_meta( $post->ID, 'textalert', true );
  $idimage = get_post_meta($post->ID, 'aw_custom_image', true);
  $image = wp_get_attachment_image_src($idimage,'thumbnail');
  wp_nonce_field( 'wpdocs_field_password', 'wpdocs_field_password_nonce' );
  ?>
  <div id="updeimg">
    <p>Password:</p>
    <input type="text" class="widefat" id="passwordpost" name="passwordpost"  value="<?php echo esc_attr( $passwordpost ) ?>">
    <p>Text Alert:</p>
    <textarea  id="textalert" class="widefat" name="textalert" <?php echo esc_attr( $textalert ); ?>><?php echo esc_attr( $textalert ); ?></textarea>
    <p>Image Alert:</p>
    <a href="#" class="widefat aw_upload_image_button button button-secondary">
        <?php if ( $image ) : ?>
          <img src="<?php echo $image[0]; ?>" width="<?php echo $image[1]; ?>" height="<?php echo $image[2]; ?>"/>
          <?php  else: _e('Upload Image'); ?>
          <?php endif; ?>
    </a>
    <a href="#" class="widefat aw_remove_image_button button button-secondary <?php if ( $image ) {echo "active";} ?>"><?php _e('Remove Image');?></a>
    <input type="hidden" name="aw_custom_image" id="aw_custom_image" value="<?php echo $image; ?>" />
  </div>
  <style type="text/css">
    #updeimg > .aw_remove_image_button {
      display: none;
    }
    #updeimg > .aw_remove_image_button.active {
      display: inline-block;
    }
    #updeimg img {
      width: 100%;
      object-fit: cover;
    }
  </style>
  <?php
}
add_action( 'save_post', function( $post_id ) {
  $wpdocs_field_password_nonce = $_POST['wpdocs_field_password_nonce'];
  if ( isset( $_POST['passwordpost'] ) ) {
    update_post_meta( $post_id, 'passwordpost', $_POST['passwordpost'] );
  }
  if ( isset( $_POST['textalert'] ) ) {
    update_post_meta( $post_id, 'textalert', $_POST['textalert'] );
  }
  if (array_key_exists('aw_custom_image', $_POST)) {
    update_post_meta($post_id,'aw_custom_image',$_POST['aw_custom_image']);
  }
} );
// End Add the custom columns to the post post type:
function restrict_admin_with_redirect() {
  $restrictions = array(
    '/wp-admin/themes.php',
    // '/wp-admin/plugins.php'
  );
  foreach ( $restrictions as $restriction ) {
      if ( ! current_user_can( 'manage_network' ) && $_SERVER['PHP_SELF'] == $restriction ) {
        wp_redirect( admin_url() );
        exit;
      }
    }
  }
add_action( 'admin_init', 'restrict_admin_with_redirect' ); 
add_action('after_setup_theme', 'wpdocs_theme_setup');
function wpdocs_theme_setup(){
    load_theme_textdomain('twentyfifteenchild', get_stylesheet_directory() . '/languages');
}