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
        $this->color_themes_section( $wp_customize );
        //var_dump(get_option('theme_mods_disciple-tools-theme'));die();
    }

    public function color_themes_section( $wp_customize ) {
        $wp_customize->add_section( 'dt_color_themes', array(
            'title' => __( 'Color Themes', 'disciple_tools' ),
            'description' => __( 'Choose one of our preselecte Disciple.Tools color themes', 'disciple_tools' ),
            )
        );

        $wp_customize->add_setting( 'dt_color_theme_name',
            array(
                'default'    => 'default',
                'type'       => 'theme_mod',
                'capability' => 'edit_theme_options',
                'transport'  => 'postMessage',
            )
        );

        $wp_customize->add_control( new WP_Customize_Control(
            $wp_customize,
            'disciple_tools_theme',
            array(
            'label'      => __( 'Select Color Theme', 'disciple_tools' ),
            'description' => __( 'Using this option you can change the theme colors for your Disciple.Tools instance.', 'disciple_tools' ),
            'settings'   => 'dt_color_theme_name',
            'priority'   => 10,
            'section'    => 'dt_color_themes',
            'type'    => 'select',
            'choices' => array(
                'default' => 'Default',
                'dark_mode' => 'Dark Mode',
                'disciple_toogles' => 'Disciple.Toogles',
                'latte_art' => 'Latte Art',
                'poster_boy' => 'Poster Boy',
                'soft_drink_cup' => 'Soft Drink Cup',
                'hasta_la_vista' => 'Hasta la Vista',
                'watermelone' => 'Watermelone',
                'pumpkin_pie' => 'Pumpkin Pie',
                'joy_to_the_world' => 'Joy to the World',
                'routing_thomas' => 'Routing Thomas',
                'motel_art' => 'Motel Art',
                'viva_la_raza' => '¡Viva la Raza!',
                'africa_forever' => 'Africa Forever',
                'custom' => '(Custom)',
                )
            )
        ) );
    }

    // Colors section, settings, and controls
    public function colors_section( $wp_customize ) {
        /*
         * Section
         */
        $wp_customize->add_section( 'dt_colors_section',
            array(
                'title' => 'Colors',
                'description' => 'Edit the colors for the Disciple.Tools theme elements.<br><br><img src="' . esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' ) .'"> <b>Important:</b> In order to see your custom color theme, make sure the \'custom\' theme is selected in the Color Themes menu.'
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
    }

    public function dt_custom_css() {
        $selected_color_theme = get_theme_mod( 'dt_color_theme_name' );

        $color_themes['custom'] = [
            'dt_background_color' => get_theme_mod( 'dt_background_color' ),
            'dt_background_image' => get_theme_mod( 'dt_background_image' ),
            'dt_navbar_color' => get_theme_mod( 'dt_navbar_color' ),
            'dt_navbar_second_color' => get_theme_mod( 'dt_navbar_second_color' ),
            'dt_primary_button_color' => get_theme_mod( 'dt_primary_button_color' ),
            'dt_primary_button_text_color' => get_theme_mod( 'dt_primary_button_text_color' ),
            'dt_tile_background_color' => get_theme_mod( 'dt_tile_background_color' ),
        ];
        $color_themes['default'] = [
            'dt_background_color' => '#e2e2e2',
            'dt_navbar_color' => '#3f729b',
            'dt_navbar_second_color' => '#ffffff',
            'dt_primary_button_color' => '#3f729b',
            'dt_primary_button_text_color' => '#a8dadc',
            'dt_tile_background_color' => '#fefefe'
        ];
        $color_themes['disciple_toogles'] = [
            'dt_background_color' => '#f3f3f3',
            'dt_navbar_color' => '#1267d3',
            'dt_navbar_second_color' => '#e8f0fe',
            'dt_primary_button_color' => '#009933',
            'dt_primary_button_text_color' => '#ffffff',
            'dt_tile_background_color' => '#ffffff'
        ];
        $color_themes['dark_mode'] = [
            'dt_background_color' => '#0d1117',
            'dt_navbar_color' => '#161b22',
            'dt_navbar_second_color' => '#21262c',
            'dt_primary_button_color' => '#21262c',
            'dt_primary_button_text_color' => '#585c60',
            'dt_tile_background_color' => '#585c60'
        ];
        $color_themes['latte_art'] = [
            'dt_background_color' => '#333333',
            'dt_navbar_color' => '#bd8a49',
            'dt_navbar_second_color' => '#f9e6b1',
            'dt_primary_button_color' => '#000000',
            'dt_primary_button_text_color' => '#fefefe',
            'dt_tile_background_color' => '#f7f3e3'
        ];
        $color_themes['poster_boy'] = [
            'dt_background_color' => '#1d3557',
            'dt_navbar_color' => '#e63946',
            'dt_navbar_second_color' => '#f9e6b1',
            'dt_primary_button_color' => '#457b9d',
            'dt_primary_button_text_color' => '#a8dadc',
            'dt_tile_background_color' => '#f1faee'
        ];
        $color_themes['soft_drink_cup'] = [
            'dt_background_color' => '#ffffff',
            'dt_background_image' => 'https://99percentinvisible.org/app/uploads/2019/07/solo-jaz.jpg',
            'dt_navbar_color' => '#f72585',
            'dt_navbar_second_color' => '#05c4c6',
            'dt_primary_button_color' => '#3d0066',
            'dt_primary_button_text_color' => '#ffffff',
            'dt_tile_background_color' => '#dec9e9'
        ];
        $color_themes['hasta_la_vista'] = [
            'dt_background_color' => '#0b090a',
            'dt_background_image' => 'https://cdn.pixabay.com/photo/2015/09/10/14/14/scratched-934483_960_720.jpg',
            'dt_navbar_color' => '#660708',
            'dt_navbar_second_color' => '#e5383b',
            'dt_primary_button_color' => '#a4161a',
            'dt_primary_button_text_color' => '#d3d3d3',
            'dt_tile_background_color' => '#d3d3d3'
        ];
        $color_themes['watermelone'] = [
            'dt_background_color' => '#bc4749',
            'dt_navbar_color' => '#6a994e',
            'dt_navbar_second_color' => '#fff1e6',
            'dt_primary_button_color' => '#6a994e',
            'dt_primary_button_text_color' => '#000000',
            'dt_tile_background_color' => '#f2e8cf'
        ];
        $color_themes['pumpkin_pie'] = [
            'dt_background_color' => '#231f20',
            'dt_navbar_color' => '#bb4430',
            'dt_navbar_second_color' => '#f28f3b',
            'dt_primary_button_color' => '#658083',
            'dt_primary_button_text_color' => '#000000',
            'dt_tile_background_color' => '#efe6dd'
        ];
        $color_themes['joy_to_the_world'] = [
            'dt_background_color' => '#001c00',
            'dt_navbar_color' => '#bf0603',
            'dt_navbar_second_color' => '#d1d1d1',
            'dt_primary_button_color' => '#4a4a4a',
            'dt_primary_button_text_color' => '#ffffff',
            'dt_tile_background_color' => '#ffffff'
        ];
        $color_themes['routing_thomas'] = [
            'dt_background_color' => '#000000',
            'dt_navbar_color' => '#5519a1',
            'dt_navbar_second_color' => '#9ff100',
            'dt_primary_button_color' => '#000000',
            'dt_primary_button_text_color' => '#9ff100',
            'dt_tile_background_color' => '#adadad'
        ];
        $color_themes['motel_art'] = [
            'dt_background_color' => '#68B0AB',
            'dt_background_image' => 'https://www.wowpatterns.com/assets/files/resource_images/spring-green-leaves-background-pattern.jpg',
            'dt_navbar_color' => '#645b47',
            'dt_navbar_second_color' => '#d1d1c1',
            'dt_primary_button_color' => '#7eb89f',
            'dt_primary_button_text_color' => '#000000',
            'dt_tile_background_color' => '#ffffff'
        ];
        $color_themes['viva_la_raza'] = [
            'dt_background_color' => '#56d69f',
            'dt_background_image' => 'https://cdn.pixabay.com/photo/2016/11/15/23/14/emerald-1827808_1280.jpg',
            'dt_navbar_color' => '#ee4266',
            'dt_navbar_second_color' => '#ffd23f',
            'dt_primary_button_color' => '#540d6e',
            'dt_primary_button_text_color' => '#3bceac',
            'dt_tile_background_color' => '#eae2b7'
        ];
        $color_themes['africa_forever'] = [
            'dt_background_color' => '#463756',
            'dt_navbar_color' => '#C54125',
            'dt_navbar_second_color' => '#E36E0D',
            'dt_primary_button_color' => '#514C48',
            'dt_primary_button_text_color' => '#F3D28F',
            'dt_tile_background_color' => '#F3D28F'
        ];
        ?>
        <style id="dt-custom-css">
            .logo-link {
                background-image: url( <?php echo esc_attr( $color_themes[$selected_color_theme]['custom_logo'] ); ?> );
                background-size: contain;
                background-repeat: no-repeat;
                margin-right: 20px;
            }
            body {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_background_color'] ); ?>;
                background-image: url('<?php echo esc_attr( $color_themes[$selected_color_theme]['dt_background_image'] ); ?>');
                background-attachment: fixed;
                background-size: cover;
            }
            .top-bar { 
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>;
                border-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>;
            }
            .top-bar ul { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>; }
            .title-bar { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>; }
            #top-bar-menu .dropdown.menu a {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>;
                color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
            }
            .dropdown.menu>li.is-active>a { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;  }
            #top-bar-menu .dropdown.menu li.active>a {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_color'] ); ?>;
                color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
                filter: brightness(0.75);
            }
            #top-bar-menu .dropdown.menu .is-submenu-item a:hover {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_second_color'] ); ?>;
            }
            #top-bar-menu .top-bar-left .dropdown.menu a:hover {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;
            }
            nav.second-bar { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_second_color'] ); ?> }
            .list_field_picker { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_second_color'] ); ?> !important }
            #bulk_edit_picker { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_navbar_second_color'] ); ?> !important }
            .button {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;
                color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_text_color'] ); ?>;
            }
            .button.select-button { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>50; }
            .button.select-button:hover { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>75; }
            .button.selected-select-button { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            .button.clear{ color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            .button.hollow {
                border-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;
                color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;
            }
            .button:hover { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>75; }
            .list-views label:hover { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?> }
            a: hover { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;  }
            #table-content tr:hover { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>15;  }
            .typeahead__label {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>15;
                border-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>50;
            }
            .typeahead__label .typeahead__cancel-button:hover { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>25; }
            .show-details-section { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>15 !important; }
            .typeahead__cancel-button { border-left: 1px solid <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>50 !important; }
            .current-filter{
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>30;
                border-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>;
            }
            .section-header { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            .bordered-box {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
                border-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>30;
            }
            .callout { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>; }
            .left-border-grey { border-left: 1px solid <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>75; }
            tbody {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
                border: 1px solid <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>30;
            }
            tbody>tr {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
                border-bottom: 1px solid <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>30;
            }
            .mentions-input-box { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>; }
            .cell.auto { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>; }
            .tabs-content { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>; }
            .dropdown.menu>li>a {
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
            }
            a { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            a:hover { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            .accordion-title{
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?> !important;
                color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_text_color'] ); ?> !important;
                opacity: 0.75;
                border: none;
            }
            .day-activities__title { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?> }
            .activity__more-link { color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?> }
            .is-active a{filter: none;}
            .accordion-content{
                background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_tile_background_color'] ); ?>;
                border: none;
            }
            input:checked~.switch-paddle { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            input.dt-switch:checked+label { background-color: <?php echo esc_attr( $color_themes[$selected_color_theme]['dt_primary_button_color'] ); ?>; }
            thead{ border: none; }
        </style>
        <?php
    }

    public function dt_customizer_live_preview() {
        wp_enqueue_script( 'dt_theme_customizer', get_template_directory_uri() . '/dt-assets/js/theme-customizer.js', [ 'jquery', 'customize-preview' ], true );
    }
}