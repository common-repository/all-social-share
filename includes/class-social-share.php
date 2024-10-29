<?php
class ASSP_Social_Share {

    public function __construct() {
        add_action('admin_init', array($this, 'assp_settings_init'));
        add_action('admin_menu', array($this, 'assp_add_admin_menu'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    public function init() {
        add_action('the_content', array($this, 'assp_add_social_share_buttons'));
        add_action('wp_enqueue_scripts', array($this, 'assp_enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'assp_enqueue_admin_styles'));
    }

    public function assp_add_settings_link($links) {
        $url = admin_url('admin.php?page=all-social-share');
        $settings_link = '<a href="' . esc_url($url) . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function assp_add_social_share_buttons($content) {
        $options = get_option('assp_social_share_options');
        $feature_enabled = isset($options['assp_social_share_field_toggle']) && $options['assp_social_share_field_toggle'] == 1;

        $display_on_mobile = $options['assp_social_share_display_on_mobile'];

        if ($feature_enabled) {
            $post_type = get_post_type(); // Get the current post type
            if (isset($options['post_types'][$post_type]) && $options['post_types'][$post_type] == 1) {
                $social_buttons = $this->assp_generate_social_buttons();
                // Check if backend selection enables display on mobile
                $display_on_mobile = isset($options['assp_social_share_display_on_mobile']) ? $options['assp_social_share_display_on_mobile'] : false;
                // Check if it's a mobile device
                $is_mobile = wp_is_mobile();

                // Check if display is enabled on mobile and it's a mobile device, or if it's not a mobile device
                if (($display_on_mobile && $is_mobile) || !$is_mobile) {
                    // Add a class based on the selected position
                    $position_class = isset($options['assp_social_share_position']) ? ' position-' . $options['assp_social_share_position'] : '';
                    $content .= '<div class="assp_social-share-buttons_new' . $position_class . '">' . $social_buttons . '</div>';
                }
            }
        }
        return $content;
    }

    public function assp_enqueue_admin_styles() {
        wp_enqueue_style('assp-admin-toggle-button-styles', plugins_url('css/admin-styles.css', __FILE__));
    }

    private function assp_generate_social_buttons() {
        $permalink = get_permalink();
        $title = get_the_title();
        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); 
        $options = get_option('assp_social_share_advance_options');
        // Ensure $selected_val is an array
        $selected_val = isset($options['assp_social_share_display_selected_icons']) ? $options['assp_social_share_display_selected_icons'] : [];

        // Define available social buttons with their URLs and Font Awesome classes
        $social_buttons = array(
            'facebook' => array(
                'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . esc_url($permalink),
                'class' => 'fab fa-facebook-square'
            ),
            'twitter' => array(
                'url' => 'https://twitter.com/intent/tweet?url=' . esc_url($permalink) . '&text=' . esc_attr($title),
                'class' => 'fab fa-twitter'
            ),
            'linkedin' => array(
                'url' => 'https://www.linkedin.com/shareArticle?url=' . esc_url($permalink) . '&title=' . esc_attr($title),
                'class' => 'fab fa-linkedin'
            ),
            'pinterest' => array(
                'url' => 'https://pinterest.com/pin/create/button/?url=' . esc_url($permalink) . '&media=' . esc_url($thumbnail_url) . '&description=' . esc_attr($title),
                'class' => 'fab fa-pinterest'
            ),
            'whatsapp' => array(
                'url' => 'https://api.whatsapp.com/send?text=' . esc_attr($title) . ' ' . esc_url($permalink),
                'class' => 'fab fa-whatsapp'
            ),
            'email' => array(
                'url' => 'mailto:?subject=' . esc_attr($title) . '&body=' . esc_url($permalink),
                'class' => 'fas fa-envelope'
            ),
            'gmail' => array(
                'url' => 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to&su=' . esc_attr($title) . '&body=' . esc_url($permalink),
                'class' => 'fas fa-envelope-square'
            ),
            'telegram' => array(
                'url' => 'https://t.me/share/url?url=' . esc_url($permalink) . '&text=' . esc_attr($title),
                'class' => 'fab fa-telegram'
            ),
            'google_plus' => array(
                'url' => 'https://plus.google.com/share?url=' . esc_url($permalink),
                'class' => 'fab fa-google-plus'
            ),
            'print' => array(
                'url' => 'javascript:window.print()',
                'class' => 'fas fa-print'
            )
        );

        // Initialize an empty string to hold the HTML for selected buttons
        $buttons_html = '<div class="assp_social-share-buttons">';

        // Iterate through each social button and check if it's selected, then add its HTML
        foreach ($social_buttons as $button_key => $button_data) {
            if (in_array($button_key, $selected_val)) {
                $buttons_html .= '<a href="' . $button_data['url'] . '" target="_blank"><i class="' . $button_data['class'] . '"></i></a>';
            }
        }

        $buttons_html .= '</div>';
        return $buttons_html;
    }

    public function assp_enqueue_styles() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('assp-custom-js', plugins_url('js/custom.js', __FILE__), array('jquery'), '1.0.0', true);
        //wp_enqueue_style('assp-font-awesome', plugins_url('css/all.min.css', __FILE__), array(), '6.2.0');
        wp_enqueue_style( 'assp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
        wp_enqueue_style('assp-social-share-styles', plugins_url('css/social-share-styles.css', __FILE__), array('assp-font-awesome'), '1.0.0');
    }

    public function assp_add_admin_menu() {
        add_menu_page(
            __('Social Share', 'assp-social-share'), // Page title
            __('Social Share', 'assp-social-share'), // Menu title
            'manage_options', // Capability
            'assp-social-share', // Menu slug
            array($this, 'assp_display_settings_page'), // Function
            'dashicons-share', // Icon URL
            6 // Position
        );
    }

    public function assp_settings_init() {
        // Register a new setting for "my_social_share" page.
        register_setting('assp_social_share', 'assp_social_share_options');
    
        // Register a new section in the "my_social_share" page.
        add_settings_section('assp_social_share_section_developers', __('', 'assp-social-share'), array($this, 'assp_social_share_section_callback'), 'assp_social_share');

        // Register a new field in the "my_social_share_section_developers" section, inside the "my_social_share" page.
        add_settings_field(
            'assp_social_share_field_toggle', // As used in the 'id' attribute of tags.
            __('Enable Plugin?', 'assp-social-share'), // Title.
            array($this, 'assp_social_share_field_toggle_render'), // Callback function.
            'assp_social_share', // Page on which to add this field.
            'assp_social_share_section_developers' // Section in which to add this field.
        );

        add_settings_field(
            'assp_social_share_options', // As used in the 'id' attribute of tags.
            __('Where do you want to display?', 'assp-social-share'), // Title.
            array($this, 'assp_social_share_section_render'), // Callback function.
            'assp_social_share', // Page on which to add this field.
            'assp_social_share_section_developers' // Section in which to add this field.
        );

        add_settings_field(
            'assp_social_share_position', // As used in the 'id' attribute of tags.
            __('Which place do you want display?', 'assp-social-share'), // Title.
            array($this, 'assp_social_share_section_position_rendor'), // Callback function.
            'assp_social_share', // Page on which to add this field.
            'assp_social_share_section_developers' // Section in which to add this field.
        );

        add_settings_field(
            'assp_social_share_display_on_mobile', // As used in the 'id' attribute of tags.
            __('Need display on mobile?', 'assp-social-share'), // Title.
            array($this, 'assp_social_share_section_mobile_rendor'), // Callback function.
            'assp_social_share', // Page on which to add this field.
            'assp_social_share_section_developers' // Section in which to add this field.
        );

        // Register a new section in the "advanced" page.
        register_setting('assp_social_share_advance', 'assp_social_share_advance_options');

        add_settings_section('assp_social_share_section_advanced', 
            __('', 'assp-social-share'),
            array($this, 'assp_social_share_section_advanced_callback'),
            'assp_social_share_advance'
        );

        add_settings_field(
            'assp_social_share_display_selected_icons', // As used in the 'id' attribute of tags.
            __('How many options need to display?', 'assp-social-share'), // Title.
            array($this, 'assp_social_share_selected_icons_rendor'), // Callback function.
            'assp_social_share_advance', // Page on which to add this field.
            'assp_social_share_section_advanced' // Section in which to add this field.
        );
    }

    public function assp_social_share_section_advanced_callback(){   }

    public function assp_social_share_selected_icons_rendor() {
        $options = get_option('assp_social_share_advance_options');
        $selected_icons = isset($options['assp_social_share_display_selected_icons']) ? $options['assp_social_share_display_selected_icons'] : array();
        $social_media_platforms = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'whatsapp' => 'Whatsapp',
            'mail' => 'Mail',
            'gmail' => 'G-Mail',
            'telegram' => 'Telegram',
            'google-plus' => 'Google+',
            'print' => 'Print'
        );
        foreach ($social_media_platforms as $platform_key => $platform_name) {
            $checked = in_array($platform_key, $selected_icons) ? 'checked="checked"' : '';
            ?>
            <tr>
                <td><?php echo esc_html($platform_name); ?></td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" name="assp_social_share_advance_options[assp_social_share_display_selected_icons][]" value="<?php echo esc_attr($platform_key); ?>" <?php echo esc_attr($checked); ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </td>
            </tr>
            <?php 
        }        
    }

    public function assp_social_share_section_render() {
        $options = get_option('assp_social_share_options');
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            if ($post_type->name !== 'attachment') {
                $checked_value = isset($options['post_types'][$post_type->name]) ? checked($options['post_types'][$post_type->name], 1, false) : '';
                ?>
                <div class="assp_postss">
                <label class="toggle-switch">
                    <input type="checkbox" name="assp_social_share_options[post_types][<?php echo esc_attr($post_type->name); ?>]" <?php echo $checked_value; ?> value="1">
                    <span class="toggle-slider"></span>
                </label>
                <?php
                echo esc_html($post_type->labels->name);
                ?>
            </div>
            <?php
            }
        }
    }

    public function assp_social_share_section_callback() { }

    public function assp_social_share_section_position_rendor() {
        $options = get_option('assp_social_share_options');
        $position = isset($options['assp_social_share_position']) ? $options['assp_social_share_position'] : 'below';
        ?>
        <label><input type="radio" name="assp_social_share_options[assp_social_share_position]" value="left" <?php echo checked($position, 'left', false); ?>><?php echo esc_html__('Left side','assp-social-share'); ?> </label><br>
        <label><input type="radio" name="assp_social_share_options[assp_social_share_position]" value="right" <?php echo checked($position, 'right', false); ?>><?php echo esc_html__('Right side','assp-social-share'); ?></label><br>
        <label><input type="radio" name="assp_social_share_options[assp_social_share_position]" value="below" <?php echo checked($position, 'below', false); ?>><?php echo esc_html__('Below post/product','assp-social-share'); ?> </label><br>
        <?php
    }

    public function assp_social_share_field_toggle_render() {
        $options = get_option('assp_social_share_options');
        ?>
        <label class="toggle-switch">
            <input type='checkbox' name='assp_social_share_options[assp_social_share_field_toggle]' <?php checked(isset($options['assp_social_share_field_toggle']), 1); ?> value='1'>
            <span class="toggle-slider"></span>
        </label>
        <?php
    }

    public function assp_social_share_section_mobile_rendor(){
        $options = get_option('assp_social_share_options');
        ?>
        <label class="toggle-switch">
            <input type='checkbox' name='assp_social_share_options[assp_social_share_display_on_mobile]' <?php checked(isset($options['assp_social_share_display_on_mobile']), 1); ?> value='1'>
            <span class="toggle-slider"></span>
        </label>
        <?php
    }

    public function assp_display_settings_page() {
        $current_tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING);
        $current_tab = empty($current_tab) ? 'social-share' : $current_tab;  // Default tab is 'social-share'
        ?>
        <div class="wrap">
            <h2><?php echo esc_html__('My Social Share Settings','assp-social-share'); ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="?page=assp-social-share&tab=social-share" class="nav-tab <?php echo $current_tab === 'social-share' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Default Settings','assp-social-share'); ?></a>
                <a href="?page=assp-social-share&tab=admin-section" class="nav-tab <?php echo $current_tab === 'admin-section' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Advance Settings','assp-social-share'); ?></a>
            </h2>
            <?php if ($current_tab === 'social-share') : ?>
                <div id="assp-social-share" class="assp-social-share-tab-content">
                    <form action='options.php' method='post'>
                        <?php
                        settings_fields('assp_social_share');
                        do_settings_sections('assp_social_share');
                        submit_button();
                        ?>
                    </form>
                </div>
            <?php elseif ($current_tab === 'admin-section') : ?>
                <div id="assp-admin-section" class="assp-admin-tab-content">
                    <form action='options.php' method='post'>
                        <?php
                        settings_fields('assp_social_share_advance');
                        do_settings_sections('assp_social_share_advance');
                        submit_button();
                        ?>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}