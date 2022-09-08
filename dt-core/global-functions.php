<?php

if ( ! defined( 'DT_FUNCTIONS_READY' ) ){
    define( 'DT_FUNCTIONS_READY', true );


    /**
     * A simple function to assist with development and non-disruptive debugging.
     * -----------
     * -----------
     * REQUIREMENT:
     * WP Debug logging must be set to true in the wp-config.php file.
     * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
     * -----------
     * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
     * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
     * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
     * @ini_set( 'display_errors', 0 );
     * -----------
     * -----------
     * EXAMPLE USAGE:
     * (string)
     * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
     * -----------
     * (array)
     * $an_array_of_things = ['an', 'array', 'of', 'things'];
     * write_log($an_array_of_things);
     * -----------
     * (object)
     * $an_object = new An_Object
     * write_log($an_object);
     */
    if ( ! function_exists( 'dt_write_log' ) ) {
        /**
         * A function to assist development only.
         * This function allows you to post a string, array, or object to the WP_DEBUG log.
         * It also prints elapsed time since the last call.
         *
         * @param $log
         */
        function dt_write_log( $log ) {
            if ( true === WP_DEBUG ) {
                global $dt_write_log_microtime;
                $now = microtime( true );
                if ( $dt_write_log_microtime > 0 ) {
                    $elapsed_log = sprintf( "[elapsed:%5dms]", ( $now - $dt_write_log_microtime ) * 1000 );
                } else {
                    $elapsed_log = "[elapsed:-------]";
                }
                $dt_write_log_microtime = $now;
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( $elapsed_log . " " . print_r( $log, true ) );
                } else {
                    error_log( "$elapsed_log $log" );
                }
            }
        }
    }

    if ( !function_exists( 'dt_is_rest' ) ) {
        /**
         * Checks if the current request is a WP REST API request.
         *
         * Case #1: After WP_REST_Request initialisation
         * Case #2: Support "plain" permalink settings
         * Case #3: URL Path begins with wp-json/ (your REST prefix)
         *          Also supports WP installations in subfolders
         *
         * @returns boolean
         */
        function dt_is_rest( $namespace = null ) {
            $prefix = rest_get_url_prefix();
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST
                 || isset( $_GET['rest_route'] )
                    && strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) {
                return true;
            }
            $rest_url    = wp_parse_url( site_url( $prefix ) );
            $current_url = wp_parse_url( add_query_arg( array() ) );
            $is_rest = strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
            if ( $namespace ){
                return $is_rest && strpos( $current_url['path'], $namespace ) != false;
            } else {
                return $is_rest;
            }
        }
    }

    /**
     * The path of the url excluding the subfolder if wp is installed in a subfolder.
     * https://example.com/sub/contacts/3/?param=true
     * will return contacts/3/?param=true
     * @return string
     */
    if ( ! function_exists( 'dt_get_url_path' ) ) {
        function dt_get_url_path( $ignore_query_parameters = false ) {
            if ( isset( $_SERVER["HTTP_HOST"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= esc_url_raw( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
                //remove the domain part. Ex: https://example.com/
                $url = trim( str_replace( get_site_url(), "", $url ), '/' );
                if ( $ignore_query_parameters ){
                    return strtok( $url, '?' ); //allow get parameters
                }
                return $url;
            }
            return '';
        }
    }

    if ( ! function_exists( 'dt_get_post_type' ) ) {
        /**
         * The post type as found in the url returned by dt_get_url_path
         * https://example.com/sub/contacts/3/?param=true
         * will return 'contacts'
         * @return string
         */
        function dt_get_post_type() {
            $url_path = dt_get_url_path();
            $url_path_with_no_query_string = explode( '?', $url_path )[0];
            return explode( '/', $url_path_with_no_query_string )[0];
        }
    }

    if ( ! function_exists( 'dt_array_to_sql' ) ) {
        function dt_array_to_sql( $values ) {
            if ( empty( $values ) ) {
                return 'NULL';
            }
            foreach ( $values as &$val ) {
                if ( '\N' === $val ) {
                    $val = 'NULL';
                } else {
                    $val = "'" . esc_sql( trim( $val ) ) . "'";
                }
            }
            return implode( ',', $values );
        }
    }


    /**
     * @param $date
     * @param string $format  options are short, long, or [custom]
     *
     * @return bool|int|string
     */
    if ( ! function_exists( 'dt_format_date' ) ) {
        function dt_format_date( $date, $format = 'short' ) {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            if ( $format === 'short' ) {
                // $format = $date_format;
                // formatting it with internationally understood date, as there was a
                // struggle getting dates to show in user's selected language and not
                // in the site language.
                $format = 'Y-m-d';
            } else if ( $format === 'long' ) {
                $format = $date_format . ' ' . $time_format;
            }
            if ( is_numeric( $date ) ) {
                $formatted = date_i18n( $format, $date );
            } else {
                $formatted = mysql2date( $format, $date );
            }
            return $formatted;
        }
    }

    if ( ! function_exists( 'dt_date_start_of_year' ) ) {
        function dt_date_start_of_year() {
            $this_year = gmdate( 'Y' );
            $timestamp = strtotime( $this_year . '-01-01' );
            return $timestamp;
        }
    }
    if ( ! function_exists( 'dt_date_end_of_year' ) ) {
        function dt_date_end_of_year() {
            $this_year = (int) gmdate( 'Y' );
            return strtotime( ( $this_year + 1 ) . '-01-01' );
        }
    }
    if ( ! function_exists( 'dt_get_year_from_timestamp' ) ) {
        function dt_get_year_from_timestamp( int $time ) {
            return gmdate( "Y", $time );
        }
    }

    if ( ! function_exists( 'dt_sanitize_array_html' ) ) {
        function dt_sanitize_array_html( $array ) {
            array_walk_recursive($array, function ( &$v ) {
                $v = filter_var( trim( $v ), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
            });
            return $array;
        }
    }

    if ( ! function_exists( 'dt_recursive_sanitize_array' ) ) {
        function dt_recursive_sanitize_array( array $array ) : array {
            foreach ( $array as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = dt_recursive_sanitize_array( $value );
                }
                else {
                    $value = sanitize_text_field( wp_unslash( $value ) );
                }
            }
            return $array;
        }
    }

    /**
     * Deprecated function, use dt_get_available_languages()
     */
    if ( ! function_exists( 'dt_get_translations' ) ) {
        function dt_get_translations() {
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations(); // @todo throwing errors if wp.org connection isn't established
            return $translations;
        }
    }

    if ( ! function_exists( 'dt_get_available_languages' ) ) {
        /**
         * Return the list of available languages. Defaults to all translations in the theme.
         *
         * If an array of available language codes is given, then the function will return the language info for
         * these language codes. Useful if you want to get the language info for your plugin's translated languages
         *
         * If $all is set to true, then the function will return the unfiltered complete language information array.
         *
         * @param bool $code_as_key Do we want to return an assosciative array with the codes as the keys
         * @param bool $all Returns all possible languages in the world ( or at least those we have in our system :)
         * @param array $available_language_codes The list of language codes that have been translated ( if you want to filter the list by languages in your plugin for example)
         *
         * @return array
         */
        function dt_get_available_languages( $code_as_key = false, $all = false, $available_language_codes = [] ) {
            $translations = dt_get_global_languages_list();

            if ( true === $all ) {
                return $translations;
            }

            if ( empty( $available_language_codes ) ) {
                $available_language_codes = get_available_languages( get_template_directory() .'/dt-assets/translation' );
            }

            array_unshift( $available_language_codes, 'en_US' );
            $available_translations = [];
            $site_default_locale = get_option( 'WPLANG' );

            foreach ( $available_language_codes as $code ){
                if ( isset( $translations[$code] ) ){
                    $translations[$code]['site_default'] = $site_default_locale === $code;
                    $translations[$code]['english_name'] = $translations[$code]["label"];
                    $translations[$code]['language'] = $code;
                    if ( !$code_as_key ){
                        $available_translations[] = $translations[$code];
                    } else {
                        $available_translations[$code] = $translations[$code];
                    }
                }
            }
            return $available_translations;
        }
    }

    if ( !function_exists( 'dt_language_select' ) ){
        function dt_language_select( $user_id = null ){
            if ( $user_id === null ){
                $user_id = get_current_user_id();
            }
            $languages = dt_get_available_languages();
            $dt_user_locale = get_user_locale( $user_id );
            ?>
            <select name="locale">
                <?php foreach ( $languages as $language ){ ?>
                    <option
                        value="<?php echo esc_html( $language["language"] ); ?>" <?php selected( $dt_user_locale === $language["language"] ) ?>>
                        <?php echo esc_html( ! empty( $language["flag"] ) ? $language["flag"] . ' ' : '' ); ?> <?php echo esc_html( $language["native_name"] ); ?>
                    </option>
                <?php } ?>
            </select>
            <?php
        }
    }

    if ( !function_exists( "dt_create_field_key" ) ){
        function dt_create_field_key( $s, $with_hash = false ){
            //note we don't limit to alhpa_numeric because it would strip out all non latin based languages
            $s = str_replace( ' ', '_', $s ); // Replaces all spaces with hyphens.
            $s = sanitize_key( $s );
            if ( $with_hash === true ){
                $s .= '_' . substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
            }
            if ( empty( $s ) ){
                $s .= 'key_' . substr( md5( rand( 10000, 100000 ) ), 0, 3 );
            }
            return $s;
        }
    }
    if ( !function_exists( "dt_render_field_icon" ) ){
        function dt_render_field_icon( $field, $class = 'dt-icon', $default_to_name = false ){
            $icon_rendered = false;
            if ( isset( $field["icon"] ) && !empty( $field["icon"] ) ){
                $icon_rendered = true;
                if ( isset( $field["name"] ) ) {
                    $alt_tag = $field["name"];
                } else if ( isset( $field["label"] ) ) {
                    $alt_tag = $field["label"];
                } else {
                    $alt_tag = "";
                }
                ?>

                <img class="<?php echo esc_html( $class ); ?>" src="<?php echo esc_url( $field["icon"] ) ?>" alt="<?php echo esc_html( $alt_tag ) ?>">

                <?php
            } else if ( isset( $field['font-icon'] ) && !empty( $field['font-icon'] ) ){
                $icon_rendered = true;
                ?>

                <i class="<?php echo esc_html( $field['font-icon'] ); ?> <?php echo esc_html( $class ); ?>"></i>

                <?php
            } else if ( $default_to_name && !empty( $field["name"] ) ){
                ?>

                <strong class="snippet-field-name"><?php echo esc_html( $field['name'] ); ?></strong>

                <?php
            }
            return $icon_rendered;
        }
    }

    if ( ! function_exists( 'dt_has_permissions' ) ) {
        function dt_has_permissions( array $permissions ) : bool {
            if ( count( $permissions ) > 0 ) {
                foreach ( $permissions as $permission ){
                    if ( current_user_can( $permission ) ){
                        return true;
                    }
                }
            }
            return false;
        }
    }


    /**
     * Prints the name of the Group or User
     * Used in the loop to get a friendly name of the 'assigned_to' field of the contact
     *
     * If $return is true, then return the name instead of printing it. (Similar to
     * the $return argument in var_export.)
     *
     * @param  int  $contact_id
     * @param  bool $return
     * @return string
     */
    function dt_get_assigned_name( int $contact_id, bool $return = false ) {

        $metadata = get_post_meta( $contact_id, $key = 'assigned_to', true );

        if ( !empty( $metadata ) ) {
            $meta_array = explode( '-', $metadata ); // Separate the type and id
            $type = $meta_array[0];
            $id = $meta_array[1];

            if ( $type == 'user' ) {
                $value = get_user_by( 'id', $id );
                $rv = $value->display_name;
            } else {
                $value = get_term( $id );
                $rv = $value->name;
            }
            if ( $return ) {
                return $rv;
            } else {
                echo esc_html( $rv );
            }
        }
    }


    function is_associative_array( array $arr ){
        if ( array() === $arr ){
            return false;
        }
        return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
    }
    /**
     * Recursively merge array2 on to array1
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function dt_array_merge_recursive_distinct( array &$array1, array &$array2 ){
        $merged = $array1;
        if ( !is_associative_array( $array2 ) && !is_associative_array( $merged ) ){
            return array_unique( array_merge( $merged, $array2 ), SORT_REGULAR );
        }
        foreach ( $array2 as $key => &$value ){
            if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ){
                $merged[$key] = dt_array_merge_recursive_distinct( $merged[$key], $value );
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    function dt_field_enabled_for_record_type( $field, $post ){
        if ( !isset( $post["type"]["key"] ) ){
            return true;
        }
        // if only_for_type is not set, then the field is available on all types
        if ( !isset( $field["only_for_types"] ) ){
            return true;
        } else if ( $field["only_for_types"] === true ){
            return true;
        } else if ( is_array( $field["only_for_types"] ) && in_array( $post["type"]["key"], $field["only_for_types"], true ) ){
            //if the type is in the "only_for_types"
            return true;
        }
        return false;
    }

    function render_new_bulk_record_fields( $dt_post_type ) {
        $post_settings = DT_Posts::get_post_settings( $dt_post_type );
        $selected_type = null;

        foreach ( $post_settings["fields"] as $field_key => $field_settings ) {
            if ( ! empty( $field_settings["hidden"] ) && empty( $field_settings["custom_display"] ) ) {
                continue;
            }
            if ( isset( $field_settings["in_create_form"] ) && $field_settings["in_create_form"] === false ) {
                continue;
            }
            if ( ! isset( $field_settings["tile"] ) ) {
                continue;
            }
            $classes    = "";
            $show_field = false;
            //add types the field should show up on as classes
            if ( ! empty( $field_settings['in_create_form'] ) ) {
                if ( is_array( $field_settings['in_create_form'] ) ) {
                    foreach ( $field_settings['in_create_form'] as $type_key ) {
                        $classes .= $type_key . " ";
                        if ( $type_key === $selected_type ) {
                            $show_field = true;
                        }
                    }
                } elseif ( $field_settings['in_create_form'] === true ) {
                    $classes    = "all";
                    $show_field = true;
                }
            } else {
                $classes = "other-fields";
            }

            ?>
            <!-- hide the fields that were not selected to be displayed by default in the create form -->
            <div <?php echo esc_html( ! $show_field ? "style=display:none" : "" ); ?>
                class="form-field <?php echo esc_html( $classes ); ?>">
                <?php
                render_field_for_display( $field_key, $post_settings['fields'], [] );
                if ( isset( $field_settings["required"] ) && $field_settings["required"] === true ) { ?>
                    <p class="help-text"
                       id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
                <?php } ?>
            </div>
            <?php
        }
    }

    /**
     * Accepts types: key_select, multi_select, text, textarea, number, date, connection, location, communication_channel, tags, user_select, link
     *
     * breadcrumb: new-field-type
     *
     * @param $field_key
     * @param $fields
     * @param $post
     * @param bool $show_extra_controls // show typeahead create button
     * @param bool $show_hidden // show hidden select options
     * @param string $field_id_prefix // add a prefix to avoid fields with duplicate ids.
     */
    function render_field_for_display( $field_key, $fields, $post, $show_extra_controls = false, $show_hidden = false, $field_id_prefix = '' ){
        $disabled = 'disabled';
        if ( isset( $post['post_type'] ) && isset( $post['ID'] ) ) {
            $can_update = DT_Posts::can_update( $post['post_type'], $post['ID'] );
        } else {
            $can_update = true;
        }
        if ( $can_update || isset( $post["assigned_to"]["id"] ) && $post["assigned_to"]["id"] == get_current_user_id() ) {
            $disabled = '';
        }
        $required_tag = ( isset( $fields[$field_key]["required"] ) && $fields[$field_key]["required"] === true ) ? 'required' : '';
        $field_type = isset( $fields[$field_key]["type"] ) ? $fields[$field_key]["type"] : null;
        $is_private = isset( $fields[$field_key]["private"] ) && $fields[$field_key]["private"] === true;
        $display_field_id = $field_key;
        if ( !empty( $field_id_prefix ) ) {
            $display_field_id = $field_id_prefix . $field_key;
        }
        if ( isset( $fields[$field_key]["type"] ) && empty( $fields[$field_key]["custom_display"] ) && empty( $fields[$field_key]["hidden"] ) ) {
            /* breadrcrumb: new-field-type Add allowed field types */
            $allowed_types = apply_filters( 'dt_render_field_for_display_allowed_types', [ 'key_select', 'multi_select', 'date', 'datetime', 'text', 'textarea', 'number', 'link', 'connection', 'location', 'location_meta', 'communication_channel', 'tags', 'user_select' ] );
            if ( !in_array( $field_type, $allowed_types ) ){
                return;
            }
            if ( !dt_field_enabled_for_record_type( $fields[$field_key], $post ) ){
                return;
            }


            ?>
            <div class="section-subheader">
                <?php dt_render_field_icon( $fields[$field_key] );

                echo esc_html( $fields[$field_key]["name"] );
                ?> <span id="<?php echo esc_html( $display_field_id ); ?>-spinner" class="loading-spinner"></span>
                <?php if ( $is_private ) : ?>
                    <i class="fi-lock small" title="<?php _x( "Private Field: Only I can see it's content", 'disciple_tools' )?>"></i>
                <?php endif;
                if ( $field_type === "communication_channel" || $field_type === "link" ) : ?>
                    <button data-field-type="<?php echo esc_html( $field_type ) ?>" data-list-class="<?php echo esc_html( $display_field_id ); ?>" class="add-button" type="button" <?php echo esc_html( $disabled ); ?>>
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
                <!-- location add -->
                <?php if ( ( $field_type === "location" || "location_meta" === $field_type ) && DT_Mapbox_API::get_key() && ! empty( $post ) ) : ?>
                    <button data-list-class="<?php echo esc_html( $field_key ) ?>" class="add-button" id="new-mapbox-search" type="button" <?php echo esc_html( $disabled ); ?>>
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
            </div>
            <?php
            if ( $field_type === "key_select" ) :
                $color_select = false;
                $active_color = "";
                if ( isset( $fields[$field_key]["default_color"] ) ) {
                    $color_select = true;
                    $active_color = $fields[$field_key]["default_color"];
                    $current_key = $post[$field_key]["key"] ?? "";
                    if ( isset( $fields[$field_key]["default"][ $current_key ]["color"] ) ){
                        $active_color = $fields[$field_key]["default"][ $current_key ]["color"];
                    }
                }
                ?>
                <select class="select-field <?php echo esc_html( $color_select ? "color-select" : "" ); ?>" id="<?php echo esc_html( $display_field_id ); ?>" style="<?php echo esc_html( $color_select ? ( "background-color: " . $active_color ) : "" ); ?>" <?php echo esc_html( $required_tag ) ?> <?php echo esc_html( $disabled ); ?>>
                    <?php if ( !isset( $fields[$field_key]["default"]["none"] ) && empty( $fields[$field_key]["select_cannot_be_empty"] ) ) : ?>
                        <option value="" <?php echo esc_html( !isset( $post[$field_key] ) ?: "selected" ) ?>></option>
                    <?php endif; ?>
                    <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ):
                        if ( !$show_hidden && isset( $option_value["hidden"] ) && $option_value["hidden"] === true ){
                            continue;
                        }
                        $selected = isset( $post[$field_key]["key"] ) && $post[$field_key]["key"] === strval( $option_key ); ?>
                        <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? "selected" : "" )?>>
                            <?php echo esc_html( $option_value["label"] ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( $field_type === "tags" ) : ?>
                <div id="<?php echo esc_html( $display_field_id ); ?>" class="tags">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key );?>"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off"
                                           data-add-new-tag-text="<?php echo esc_html( __( 'Add new tag "%s"', 'disciple_tools' ) )?>"
                                           data-tag-exists-text="<?php echo esc_html( __( 'Tag "%s" is already being used', 'disciple_tools' ) )?>" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height" data-field="<?php echo esc_html( $field_key );?>" <?php echo esc_html( $disabled ); ?>>
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "multi_select" ) :
                if ( isset( $fields[$field_key]["display"] ) && $fields[$field_key]["display"] === "typeahead" ){
                    ?>
                    <div class="multi_select" id="<?php echo esc_html( $display_field_id ); ?>" >
                        <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></var>
                        <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-multi_select" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                               data-field="<?php echo esc_html( $field_key );?>"
                                               name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                               autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="small button-group" style="display: inline-block">
                        <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>
                            <?php
                            $haystack = $post[ $field_key ] ?? [];
                            if ( ! is_array( $haystack ) ) {
                                $haystack = explode( ' ', $haystack );
                            }
                            $class = ( in_array( $option_key, $haystack ) ) ?
                                "selected-select-button" : "empty-select-button"; ?>
                            <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ); ?>"
                                    class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button" <?php echo esc_html( $disabled ); ?>>
                                <?php
                                dt_render_field_icon( $option_value );
                                echo esc_html( $option_value["label"] );
                                ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            <?php elseif ( $field_type === "text" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="text" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>" <?php echo esc_html( $disabled ); ?>/>
            <?php elseif ( $field_type === "textarea" ) :?>
                <textarea id="<?php echo esc_html( $display_field_id ); ?>" <?php echo esc_html( $required_tag ) ?>
                       class="textarea dt_textarea" <?php echo esc_html( $disabled ); ?>><?php echo esc_html( $post[$field_key] ?? "" ) ?></textarea>
            <?php elseif ( $field_type === "number" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="number" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>" <?php echo esc_html( $disabled ); ?>
                       min="<?php echo esc_html( $fields[$field_key]["min_option"] ?? "" ) ?>"
                       max="<?php echo esc_html( $fields[$field_key]["max_option"] ?? "" ) ?>"
                />
            <?php elseif ( $field_type === "link" ) : ?>

                <div class="link-group">

                    <div class="add-link-<?php echo esc_html( $display_field_id ) ?>" style="display:none">
                        <div class="add-link-form" style="display: flex; align-items: center;">
                            <select class="link-type">
                                <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>

                                    <?php if ( isset( $option_value["deleted"] ) && $option_value["deleted"] === true ) {
                                        continue;
                                    } ?>

                                    <option style="display:flex; align-items: center;" value="<?php echo esc_html( $option_key ) ?>">
                                    <span style="margin: 0 5px 1rem 0;"><?php dt_render_field_icon( $option_value ) ?></span>
                                    <?php echo esc_html( $option_value['label'] ) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button
                                type="button"
                                class="button add-link-button"
                                data-field-key="<?php echo esc_attr( $field_key ) ?>"
                            >
                                <?php esc_html_e( 'Add', 'disciple-tools' ) ?>
                            </button>
                            <button type="button" id="cancel-link-button-<?php echo esc_html( $display_field_id ) ?>" class="button hollow alert">
                                x
                            </button>
                        </div>
                    </div>

                    <div class="link-list-<?php echo esc_html( $field_key ) ?>">

                        <?php
                        foreach ( $post[$field_key] ?? [] as $link_item ) {
                            if ( !isset( $link_item["type"] ) ) {
                                continue;
                            }
                            $option_type = $link_item["type"];
                            $option_value = $fields[$field_key]["default"][$option_type];
                            $meta_id = $link_item["meta_id"];
                            $meta_value = $link_item["value"];

                            render_link_field( $field_key, $option_type, $option_value, $meta_value, $display_field_id, $meta_id, $required_tag, $disabled );
                        }
                        ?>

                    </div>

                    <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>

                        <?php if ( isset( $option_value["deleted"] ) && $option_value["deleted"] === true ) {
                            continue;
                        } ?>

                        <div style="display: none" id="link-template-<?php echo esc_html( $field_key ) ?>-<?php echo esc_html( $option_key ) ?>">
                            <?php render_link_field( $field_key, $option_key, $option_value, "", $display_field_id, "", $required_tag, $disabled ) ?>
                        </div>

                    <?php endforeach; ?>

                </div>

                <?php elseif ( $field_type === "date" ) :?>
                <div class="<?php echo esc_html( $display_field_id ); ?> input-group">
                    <input id="<?php echo esc_html( $display_field_id ); ?>" class="input-group-field dt_date_picker" type="text" autocomplete="off" <?php echo esc_html( $required_tag ) ?>
                           value="<?php echo esc_html( $post[$field_key]["timestamp"] ?? '' ) ?>" <?php echo esc_html( $disabled ); ?> >
                    <div class="input-group-button">
                        <button id="<?php echo esc_html( $display_field_id ); ?>-clear-button" class="button alert clear-date-button" data-inputid="<?php echo esc_html( $display_field_id ); ?>" title="Delete Date" type="button" <?php echo esc_html( $disabled ); ?>>x</button>
                    </div>
                </div>
            <?php elseif ( $field_type === "connection" ) :?>
                <div id="<?php echo esc_attr( $display_field_id . '_connection' ) ?>" class="dt_typeahead <?php echo esc_html( $disabled ) ?>">
                    <span id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></span>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           data-post_type="<?php echo esc_html( $fields[$field_key]["post_type"] ) ?>"
                                           data-field_type="connection"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-connection-key="<?php echo esc_html( $display_field_id ); ?>" class="create-new-record typeahead__image_button input-height" <?php echo esc_html( $disabled ); ?>>
                                        <?php $icon = isset( $fields[$field_key]["create-icon"] ) ? $fields[$field_key]["create-icon"] : get_template_directory_uri() . '/dt-assets/images/add-contact.svg'; ?>
                                        <img src="<?php echo esc_html( $icon ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "location_meta" ) : ?>
                <?php if ( DT_Mapbox_API::get_key() && empty( $post ) ) : // test if Mapbox key is present ?>
                    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                        <input id="mapbox-search" type="text" class="input-group-field" name="mapbox_search" placeholder="Search Location" autocomplete="off" dir="auto" <?php echo esc_html( $disabled ); ?>/>
                        <div class="input-group-button">
                            <button id="mapbox-spinner-button" class="button hollow" style="display:none;" <?php echo esc_html( $disabled ); ?>><span class="loading-spinner active"></span></button>
                            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" style="display:none;" type="button" <?php echo esc_html( $disabled ); ?>>&times;</button>
                        </div>
                        <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
                    </div>
                    <script>
                        jQuery(document).ready(function(){
                            write_input_widget()
                        })
                    </script>
                    <script>
                        var phone_input = jQuery( '#edit-contact_phone input');
                        country_code_flag = [];
                        country_code_flag[1] = 'US';
                        country_code_flag[20] = 'EG';
                        country_code_flag[211] = 'SS';
                        country_code_flag[212] = 'EH';
                        country_code_flag[213] = 'DZ';
                        country_code_flag[216] = 'TN';
                        country_code_flag[218] = 'LY';
                        country_code_flag[220] = 'GM';
                        country_code_flag[221] = 'SN';
                        country_code_flag[222] = 'MR';
                        country_code_flag[223] = 'ML';
                        country_code_flag[224] = 'GN';
                        country_code_flag[225] = 'CI';
                        country_code_flag[226] = 'BF';
                        country_code_flag[227] = 'NE';
                        country_code_flag[228] = 'TG';
                        country_code_flag[229] = 'BJ';
                        country_code_flag[230] = 'MU';
                        country_code_flag[231] = 'LR';
                        country_code_flag[232] = 'SL';
                        country_code_flag[233] = 'GH';
                        country_code_flag[234] = 'NG';
                        country_code_flag[235] = 'TD';
                        country_code_flag[236] = 'CF';
                        country_code_flag[237] = 'CM';
                        country_code_flag[238] = 'CV';
                        country_code_flag[239] = 'ST';
                        country_code_flag[240] = 'GQ';
                        country_code_flag[241] = 'GA';
                        country_code_flag[242] = 'CG';
                        country_code_flag[243] = 'CD';
                        country_code_flag[244] = 'AO';
                        country_code_flag[245] = 'GW';
                        country_code_flag[246] = 'IO';
                        country_code_flag[247] = 'AC';
                        country_code_flag[248] = 'SC';
                        country_code_flag[249] = 'SD';
                        country_code_flag[250] = 'RW';
                        country_code_flag[251] = 'ET';
                        country_code_flag[252] = 'SO';
                        country_code_flag[253] = 'DJ';
                        country_code_flag[254] = 'KE';
                        country_code_flag[255] = 'TZ';
                        country_code_flag[256] = 'UG';
                        country_code_flag[257] = 'BI';
                        country_code_flag[258] = 'MZ';
                        country_code_flag[260] = 'ZM';
                        country_code_flag[261] = 'MG';
                        country_code_flag[262] = 'YT';
                        country_code_flag[263] = 'ZW';
                        country_code_flag[264] = 'NA';
                        country_code_flag[265] = 'MW';
                        country_code_flag[266] = 'LS';
                        country_code_flag[267] = 'BW';
                        country_code_flag[268] = 'SZ';
                        country_code_flag[269] = 'KM';
                        country_code_flag[27] = 'ZA';
                        country_code_flag[290] = 'TA';
                        country_code_flag[291] = 'ER';
                        country_code_flag[297] = 'AW';
                        country_code_flag[298] = 'FO';
                        country_code_flag[299] = 'GL';
                        country_code_flag[30] = 'GR';
                        country_code_flag[31] = 'NL';
                        country_code_flag[32] = 'BE';
                        country_code_flag[33] = 'FR';
                        country_code_flag[34] = 'ES';
                        country_code_flag[350] = 'GI';
                        country_code_flag[351] = 'PT';
                        country_code_flag[352] = 'LU';
                        country_code_flag[353] = 'IE';
                        country_code_flag[354] = 'IS';
                        country_code_flag[355] = 'AL';
                        country_code_flag[356] = 'MT';
                        country_code_flag[357] = 'CY';
                        country_code_flag[358] = 'AX';
                        country_code_flag[359] = 'BG';
                        country_code_flag[36] = 'HU';
                        country_code_flag[370] = 'LT';
                        country_code_flag[371] = 'LV';
                        country_code_flag[372] = 'EE';
                        country_code_flag[373] = 'MD';
                        country_code_flag[374] = 'AM';
                        country_code_flag[375] = 'BY';
                        country_code_flag[376] = 'AD';
                        country_code_flag[377] = 'MC';
                        country_code_flag[378] = 'SM';
                        country_code_flag[380] = 'UA';
                        country_code_flag[381] = 'RS';
                        country_code_flag[382] = 'ME';
                        country_code_flag[383] = 'XK';
                        country_code_flag[385] = 'HR';
                        country_code_flag[386] = 'SI';
                        country_code_flag[387] = 'BA';
                        country_code_flag[389] = 'MK';
                        country_code_flag[39] = 'VA';
                        country_code_flag[40] = 'RO';
                        country_code_flag[41] = 'CH';
                        country_code_flag[420] = 'CZ';
                        country_code_flag[421] = 'SK';
                        country_code_flag[423] = 'LI';
                        country_code_flag[43] = 'AT';
                        country_code_flag[45] = 'DK';
                        country_code_flag[46] = 'SE';
                        country_code_flag[47] = 'SJ';
                        country_code_flag[48] = 'PL';
                        country_code_flag[49] = 'DE';
                        country_code_flag[500] = 'FK';
                        country_code_flag[501] = 'BZ';
                        country_code_flag[502] = 'GT';
                        country_code_flag[503] = 'SV';
                        country_code_flag[504] = 'HN';
                        country_code_flag[505] = 'NI';
                        country_code_flag[506] = 'CR';
                        country_code_flag[507] = 'PA';
                        country_code_flag[508] = 'PM';
                        country_code_flag[509] = 'HT';
                        country_code_flag[51] = 'PE';
                        country_code_flag[52] = 'MX';
                        country_code_flag[53] = 'CU';
                        country_code_flag[54] = 'AR';
                        country_code_flag[55] = 'BR';
                        country_code_flag[56] = 'CL';
                        country_code_flag[57] = 'CO';
                        country_code_flag[58] = 'VE';
                        country_code_flag[591] = 'BO';
                        country_code_flag[592] = 'GY';
                        country_code_flag[593] = 'EC';
                        country_code_flag[594] = 'GF';
                        country_code_flag[595] = 'PY';
                        country_code_flag[596] = 'MQ';
                        country_code_flag[597] = 'SR';
                        country_code_flag[598] = 'UY';
                        country_code_flag[599] = 'BQ';
                        country_code_flag[60] = 'MY';
                        country_code_flag[62] = 'ID';
                        country_code_flag[63] = 'PH';
                        country_code_flag[64] = 'NZ';
                        country_code_flag[65] = 'SG';
                        country_code_flag[66] = 'TH';
                        country_code_flag[670] = 'TL';
                        country_code_flag[672] = 'NF';
                        country_code_flag[673] = 'BN';
                        country_code_flag[674] = 'NR';
                        country_code_flag[675] = 'PG';
                        country_code_flag[676] = 'TO';
                        country_code_flag[677] = 'SB';
                        country_code_flag[678] = 'VU';
                        country_code_flag[679] = 'FJ';
                        country_code_flag[680] = 'PW';
                        country_code_flag[681] = 'WF';
                        country_code_flag[682] = 'CK';
                        country_code_flag[683] = 'NU';
                        country_code_flag[685] = 'WS';
                        country_code_flag[686] = 'KI';
                        country_code_flag[687] = 'NC';
                        country_code_flag[688] = 'TV';
                        country_code_flag[689] = 'PF';
                        country_code_flag[690] = 'TK';
                        country_code_flag[691] = 'FM';
                        country_code_flag[692] = 'MH';
                        country_code_flag[7] = 'KZ';
                        country_code_flag[81] = 'JP';
                        country_code_flag[82] = 'KR';
                        country_code_flag[84] = 'VN';
                        country_code_flag[850] = 'KP';
                        country_code_flag[852] = 'HK';
                        country_code_flag[853] = 'MO';
                        country_code_flag[855] = 'KH';
                        country_code_flag[856] = 'LA';
                        country_code_flag[86] = 'CN';
                        country_code_flag[880] = 'BD';
                        country_code_flag[886] = 'TW';
                        country_code_flag[90] = 'TR';
                        country_code_flag[91] = 'IN';
                        country_code_flag[92] = 'PK';
                        country_code_flag[93] = 'AF';
                        country_code_flag[94] = 'LK';
                        country_code_flag[95] = 'MM';
                        country_code_flag[960] = 'MV';
                        country_code_flag[961] = 'LB';
                        country_code_flag[962] = 'JO';
                        country_code_flag[963] = 'SY';
                        country_code_flag[964] = 'IQ';
                        country_code_flag[965] = 'KW';
                        country_code_flag[966] = 'SA';
                        country_code_flag[967] = 'YE';
                        country_code_flag[968] = 'OM';
                        country_code_flag[970] = 'PS';
                        country_code_flag[971] = 'AE';
                        country_code_flag[972] = 'IL';
                        country_code_flag[973] = 'BH';
                        country_code_flag[974] = 'QA';
                        country_code_flag[975] = 'BT';
                        country_code_flag[976] = 'MN';
                        country_code_flag[977] = 'NP';
                        country_code_flag[98] = 'IR';
                        country_code_flag[992] = 'TJ';
                        country_code_flag[993] = 'TM';
                        country_code_flag[994] = 'AZ';
                        country_code_flag[995] = 'GE';
                        country_code_flag[996] = 'KG';
                        country_code_flag[998] = 'UZ';

                        function getFlagEmoji(countryCode) {
                        const codePoints = countryCode
                            .toUpperCase()
                            .split('')
                            .map(char =>  127397 + char.charCodeAt());
                        return String.fromCodePoint(...codePoints);
                        }

                        var current_flag_code = '';
                        var country_code_regex = new RegExp(/\+\d+[\s|\-].*?/);
                        
                        phone_input.keyup( function() {
                            if ( country_code_regex.test( phone_input.val() ) ) {
                                var phone_country_code = phone_input.val().match( /\+(\d+)[\s|\-].*?/ )[1];
                                var new_phone_input = $(this).val();
                                new_phone_input = new_phone_input.match( /^.*?(\+.*$)/ )[1];
                                
                                if ( current_flag_code !== country_code_flag[phone_country_code] ) {
                                    if ( country_code_flag[phone_country_code] ) {
                                        current_flag_code = country_code_flag[phone_country_code];
                                        var flag_emoji = getFlagEmoji(current_flag_code);
                                        $(this).val( flag_emoji + ' ' + new_phone_input );
                                    } else {
                                        $(this).val( '⚠️ ' + new_phone_input );
                                    }
                                }
                            }
                        });
                    </script>
                <?php elseif ( DT_Mapbox_API::get_key() ) : // test if Mapbox key is present ?>
                    <div id="mapbox-wrapper"></div>
                <?php endif; ?>
            <?php elseif ( $field_type === "location" ) :?>
                <div class="dt_location_grid" data-id="<?php echo esc_html( $field_key ); ?>">
                    <var id="<?php echo esc_html( $field_key ); ?>-result-container" class="result-container"></var>
                    <div id="<?php echo esc_html( $field_key ); ?>_t" name="form-<?php echo esc_html( $field_key ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           data-field_type="location"
                                           name="<?php echo esc_html( $field_key ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>/>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "communication_channel" ) : ?>
                <div id="edit-<?php echo esc_html( $field_key ) ?>" >
                    <?php foreach ( $post[$field_key] ?? [] as $field_value ) : ?>
                        <div class="input-group">
                            <input id="<?php echo esc_html( $field_value["key"] ) ?>"
                                   type="text"
                                   data-field="<?php echo esc_html( $field_key ); ?>"
                                   value="<?php echo esc_html( $field_value["value"] ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto"<?php echo esc_html( $disabled ); ?>/>
                            <div class="input-group-button">
                                <button class="button alert input-height delete-button-style channel-delete-button delete-button new-<?php echo esc_html( $field_key ); ?>" data-field="<?php echo esc_html( $field_key ); ?>" data-key="<?php echo esc_html( $field_value["key"] ); ?>" <?php echo esc_html( $disabled ); ?>>&times;</button>
                            </div>
                        </div>
                    <?php endforeach;
                    if ( empty( $post[$field_key] ) ?? [] ): ?>
                        <div class="input-group">
                            <input type="text"
                                    <?php echo esc_html( $required_tag ) ?>
                                   data-field="<?php echo esc_html( $field_key ) ?>"
                                   class="dt-communication-channel input-group-field" dir="auto" <?php echo esc_html( $disabled ); ?>
                                   <?php if ( $field_key === 'contact_phone' ) : ?>
                                    value="+"
                                    <?php endif; ?>
                                   />
                        </div>
                    <?php endif ?>
                </div>
            <?php elseif ( $field_type === "user_select" ) : ?>
                <div id="<?php echo esc_html( $field_key ); ?>" class="<?php echo esc_html( $display_field_id ); ?> dt_user_select">
                    <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container <?php echo esc_html( $display_field_id ); ?>-result-container"></var>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead">
                        <div class="typeahead__container" style="margin-bottom: 0">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height" dir="auto"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                           data-field_type="user_select"
                                           data-field="<?php echo esc_html( $field_key ); ?>"
                                           autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                                </span>
                                <span class="typeahead__button">
                                    <button type="button" class="search_<?php echo esc_html( $field_key ); ?> typeahead__image_button input-height" data-id="<?php echo esc_html( $field_key ); ?>" <?php echo esc_html( $disabled ); ?>>
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif;
        }
        do_action( 'dt_render_field_for_display_template', $post, $field_type, $field_key, $required_tag, $display_field_id );
    }

    function render_link_field( $field_key, $option_key, $option_value, $value, $display_field_id, $meta_id, $required_tag, $disabled ) {
        ?>

        <div class="link-section">
            <div class="section-subheader">
                <?php dt_render_field_icon( $option_value ) ?>
                <?php echo esc_html( $option_value["label"] ); ?>
            </div>
            <div class="input-group">
                <input
                    type="text"
                    class="link-input input-group-field"
                    value="<?php echo esc_html( $value ) ?>"
                    data-meta-id="<?php echo esc_html( $meta_id ) ?>"
                    data-field-key="<?php echo esc_html( $display_field_id ) ?>"
                    data-type="<?php echo esc_html( $option_key ) ?>"
                    <?php echo esc_html( $required_tag ) ?>
                    <?php echo esc_html( $disabled ) ?>
                >
                <div class="input-group-button">
                    <button
                        class="button alert delete-button-style input-height link-delete-button delete-button"
                        data-meta-id="<?php echo esc_html( $meta_id ) ?>"
                        data-field-key="<?php echo esc_html( $field_key ) ?>"
                    >
                        &times;
                    </button>
                </div>
            </div>
        </div>

        <?php
    }

    function dt_increment( &$var, $val ){
        if ( !isset( $var ) ){
            $var = 0;
        }
        $var += (int) $val;
    }

    function dt_get_keys_map( $array, $key = "ID" ){
        return array_map(  function ( $a ) use ( $key ) {
            if ( isset( $a[$key] ) ){
                return $a[$key];
            } else {
                return null;
            }
        }, $array );
    }

    /**
     * Test if module is enabled
     */
    if ( ! function_exists( 'dt_is_module_enabled' ) ) {
        function dt_is_module_enabled( string $module_key, $check_prereqs = false ) : bool {
            $modules = dt_get_option( "dt_post_type_modules" );
            $module_enabled = isset( $modules[$module_key]["enabled"] ) && !empty( $modules[$module_key]["enabled"] );
            if ( $module_enabled && $check_prereqs ){
                foreach ( $modules[$module_key]["prerequisites"] as $prereq ){
                    $prereq_enabled = isset( $modules[$prereq]["enabled"] ) ? $modules[$prereq]["enabled"] : false;
                    if ( !$prereq_enabled ){
                        return false;
                    }
                }
            }
            return $module_enabled;
        }
    }

    /**
     * Returns a completely unique 64 bit hashed key
     * @since 1.1
     */
    if ( ! function_exists( 'dt_create_unique_key' ) ) {
        function dt_create_unique_key() : string {
            try {
                $hash = hash( 'sha256', bin2hex( random_bytes( 256 ) ) );
            } catch ( Exception $exception ) {
                $hash = hash( 'sha256', bin2hex( rand( 0, 1234567891234567890 ) . microtime() ) );
            }
            return $hash;
        }
    }

    /**
     * Validate specified date format
     */
    if ( !function_exists( 'dt_validate_date' ) ){
        function dt_validate_date( string $date ): bool{
            $formats = [ 'Y-m-d', 'Y-m-d H:i:s', 'Y-m-d H:i:s.u', DateTimeInterface::ISO8601, DateTimeInterface::RFC3339 ];
            foreach ( $formats as $format ){
                $date_time = DateTime::createFromFormat( $format, $date );
                if ( $date_time && $date_time->format( $format ) === $date ){
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Dump and die
     */
    if ( !function_exists( 'dd' ) ) {
        function dd( ...$params ) {
            foreach ( $params as $param ) {
                var_dump( $param );
            }

            exit;
        }
    }

    /**
     * Convert a slug like 'name_or_title' to a label like 'Name or Title'
     */
    if ( !function_exists( 'dt_label_from_slug' ) ) {
        function dt_label_from_slug( $slug ) {
            $string = preg_replace( '/^' . preg_quote( 'dt_', '/' ) . '/', '', $slug );
            $string = str_replace( "_", ' ', $string );

            /* Words that should be entirely lower-case */
            $articles_conjunctions_prepositions = [
                'a',
                'an',
                'the',
                'and',
                'but',
                'or',
                'nor',
                'if',
                'then',
                'else',
                'when',
                'at',
                'by',
                'from',
                'for',
                'in',
                'off',
                'on',
                'out',
                'over',
                'to',
                'into',
                'with'
            ];
            /* Words that should be entirely upper-case (need to be lower-case in this list!) */
            $acronyms_and_such = [
                'asap',
                'unhcr',
                'wpse',
                'dt'
            ];
            /* split title string into array of words */
            $words = explode( ' ', strtolower( $string ) );
            /* iterate over words */
            foreach ( $words as $position => $word ) {
                /* re-capitalize acronyms */
                if ( in_array( $word, $acronyms_and_such ) ) {
                    $words[ $position ] = strtoupper( $word );
                    /* capitalize first letter of all other words, if... */
                } elseif (
                    /* ...first word of the title string... */
                    0 === $position ||
                    /* ...or not in above lower-case list*/
                    !in_array( $word, $articles_conjunctions_prepositions )
                ) {
                    $words[ $position ] = ucwords( $word );
                }
            }
            /* re-combine word array */
            $string = implode( ' ', $words );
            /* return title string in title case */
            return $string;
        }
    }

    /**
     * All code above here.
     */
} // end if ( ! defined( 'DT_FUNCTIONS_READY' ) )

