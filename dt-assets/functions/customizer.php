<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disciple.Tools Customizer Functionality
 */
class DT_Theme_Customizer {
    public function __construct() {
        add_action( 'wp_head', [ $this, 'dt_custom_css' ] );
        add_action( 'customize_register', [ $this, 'register_customize_sections' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'dt_customizer_live_preview' ] );
    }

    public function register_customize_sections( $wp_customize ) {
        // Initialize sections
        $this->colors_section( $wp_customize );
    }

    // Colors section, settings, and controls
    public function colors_section( $wp_customize ) {
        /*
         * Sections
         */
        $wp_customize->add_section( 'dt_colors_section',
            array(
                'title' => 'Colors',
                'description' => 'Edit the colors for the Disciple.Tools theme elements.'
            )
        );

        /*
         * Settings
         */
        $wp_customize->add_setting( 'custom_logo' );

        $wp_customize->add_setting( 'dt_background_color',
            array(
                'default' => '#e2e2e2',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_navbar_color',
            array(
                'default' => '#3f729b',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_navbar_second_color',
            array(
                'default' => '#ffffff',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_navbar_text_color',
            array(
                'default' => '#ffffff',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_primary_button_color',
            array(
                'default' => '#3f729b',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_primary_button_text_color',
            array(
                'default' => '#ffffff',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_tile_background_color',
            array(
                'default' => '#fefefe',
                'transport' => 'postMessage'
            )
        );

        $wp_customize->add_setting( 'dt_tile_border_color',
            array(
                'default' => '#e6e6e6',
                'transport' => 'postMessage'
            )
        );

        /*
         * Controls
         */
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'custom-logo',
            array(
                'label' => 'Upload Logo',
                'section' => 'custom_logo_title',
                'settings' => 'custom_logo',
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background-color',
            array(
                'label' => 'Edit background color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_background_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'navbar-color',
            array(
                'label' => 'Edit navbar color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_navbar_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'navbar-second-color',
            array(
                'label' => 'Edit second navbar color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_navbar_second_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'navbar-text-color',
            array(
                'label' => 'Edit navbar text color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_navbar_text_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary-button-color',
            array(
                'label' => 'Edit primary button color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_primary_button_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary-button-text-color',
            array(
                'label' => 'Edit primary button text color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_primary_button_text_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tile-background-color',
            array(
                'label' => 'Edit tile background color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_tile_background_color'
            )
        ) );

        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'tile-border-color',
            array(
                'label' => 'Edit tile border color',
                'section' => 'dt_colors_section',
                'settings' => 'dt_tile_border_color'
            )
        ) );
    }

    public function dt_custom_css() {
        ?>
        <style id="dt-custom-css">
            .logo-link {
                background-image: url( <?php echo esc_attr( get_theme_mod( 'custom_logo' ) ); ?> );
                background-size: contain;
                background-repeat: no-repeat;
                margin-right: 20px;
            }
            .logo { visibility: hidden; }
            body { background-color: <?php echo esc_attr( get_theme_mod( 'dt_background_color' ) ); ?>; }
            .top-bar { 
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>;
                border-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>;
            }
            .top-bar ul { background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>; }
            .title-bar { background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>; }
            #top-bar-menu .dropdown.menu a {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>;
                color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_text_color' ) ); ?>;
            }
            .dropdown.menu>li.is-active>a { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;  }
            #top-bar-menu .dropdown.menu li.active>a {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_color' ) ); ?>;
                color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_text_color' ) ); ?>;
                filter: brightness(0.75);
            }
            #top-bar-menu .dropdown.menu .is-submenu-item a:hover {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_second_color' ) ); ?>;
            }
            #top-bar-menu .top-bar-left .dropdown.menu a:hover {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_second_color' ) ); ?>;
            }
            nav.second-bar { background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_second_color' ) ); ?> }
            .list_field_picker { background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_second_color' ) ); ?> !important }
            #bulk_edit_picker { background-color: <?php echo esc_attr( get_theme_mod( 'dt_navbar_second_color' ) ); ?> !important }
            .button {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;
                color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_text_color' ) ); ?>;
            }
            .button.select-button:hover { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>75; }
            .button.clear{ color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>; }
            .button.hollow {
                border-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;
                color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;
            }
            .button:hover { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>75; }
            .list-views label:hover { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?> }
            a: hover { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;  }
            #table-content tr:hover { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>15;  }
            .typeahead__label {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>15;
                border-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>50;
            }
            .typeahead__label .typeahead__cancel-button:hover { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>25; }
            .show-details-section { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>15 !important; }
            .typeahead__cancel-button { border-left: 1px solid <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>50 !important; }
            .current-filter{
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>30;
                border-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>;
            }
            .section-header { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>; }
            .bordered-box {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_tile_background_color' ) ); ?>;
                border-color: <?php echo esc_attr( get_theme_mod( 'dt_tile_border_color' ) ); ?>;
            }
            .dropdown.menu>li>a {
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_tile_background_color' ) ); ?>;
            }
            a { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>; }
            a:hover { color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>; }
            .accordion-title{
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?> !important;
                color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_text_color' ) ); ?> !important;
                opacity: 0.75;
                border: none;
            }
            .is-active a{filter: none;}
            .accordion-content{
                background-color: <?php echo esc_attr( get_theme_mod( 'dt_tile_background_color' ) ); ?>;
                border: none;
            }
            input:checked~.switch-paddle { background-color: <?php echo esc_attr( get_theme_mod( 'dt_primary_button_color' ) ); ?>; }
            thead{ border: none; }
        </style>
        <?php
    }

    public function dt_customizer_live_preview() {
        wp_enqueue_script( 'dt_theme_customizer', get_template_directory_uri() . '/dt-assets/js/theme-customizer.js', [ 'jquery', 'customize-preview' ], true );
    }
}