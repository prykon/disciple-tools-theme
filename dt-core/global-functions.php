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
                <?php if ( $display_field_id === 'contact_phone' ) : ?>
                    <script>
                        var country_codes = {
                            1: { "country_code":"US", "name":"United States", "grid_id":"100364199" },
                            20: { "country_code":"EG", "name":"Egypt", "grid_id":"" },
                            211: { "country_code":"SS", "name":"South Sudan", "grid_id":"" },
                            212: { "country_code":"EH", "name":"Western Sahara", "grid_id":"" },
                            213: { "country_code":"DZ", "name":"Algeria", "grid_id":"" },
                            216: { "country_code":"TN", "name":"Tunisia", "grid_id":"100351558" },
                            218: { "country_code":"LY", "name":"Libya", "grid_id":"" },
                            220: { "country_code":"GM", "name":"Gambia", "grid_id":"" },
                            221: { "country_code":"SN", "name":"Senegal", "grid_id":"" },
                            222: { "country_code":"MR", "name":"Mauritania", "grid_id":"" },
                            223: { "country_code":"ML", "name":"Mali", "grid_id":"" },
                            224: { "country_code":"GN", "name":"Guinea", "grid_id":"" },
                            225: { "country_code":"CI", "name":"Cote d’Ivoire", "grid_id":"" },
                            226: { "country_code":"BF", "name":"Burkina Faso", "grid_id":"" },
                            227: { "country_code":"NE", "name":"Niger", "grid_id":"" },
                            228: { "country_code":"TG", "name":"Togo", "grid_id":"" },
                            229: { "country_code":"BJ", "name":"Benin", "grid_id":"" },
                            230: { "country_code":"MU", "name":"Mauritius", "grid_id":"" },
                            231: { "country_code":"LR", "name":"Liberia", "grid_id":"" },
                            232: { "country_code":"SL", "name":"Sierra Leone", "grid_id":"" },
                            233: { "country_code":"GH", "name":"Ghana", "grid_id":"" },
                            234: { "country_code":"NG", "name":"Nigeria", "grid_id":"" },
                            235: { "country_code":"TD", "name":"Chad", "grid_id":"" },
                            236: { "country_code":"CF", "name":"Central African Republic", "grid_id":"" },
                            237: { "country_code":"CM", "name":"Cameroon", "grid_id":"" },
                            238: { "country_code":"CV", "name":"Cabo Verde", "grid_id":"" },
                            239: { "country_code":"ST", "name":"Sao Tome and Principe", "grid_id":"" },
                            240: { "country_code":"GQ", "name":"Equatorial Guinea", "grid_id":"" },
                            241: { "country_code":"GA", "name":"Gabon", "grid_id":"" },
                            242: { "country_code":"CG", "name":"Congo Brazzaville", "grid_id":"" },
                            243: { "country_code":"CD", "name":"Congo Kinshasa", "grid_id":"" },
                            244: { "country_code":"AO", "name":"Angola", "grid_id":"" },
                            245: { "country_code":"GW", "name":"Guinea Bissau", "grid_id":"" },
                            246: { "country_code":"IO", "name":"British Indian Ocean Territory", "grid_id":"" },
                            248: { "country_code":"SC", "name":"Seychelles", "grid_id":"" },
                            249: { "country_code":"SD", "name":"Sudan", "grid_id":"" },
                            250: { "country_code":"RW", "name":"Rwanda", "grid_id":"" },
                            251: { "country_code":"ET", "name":"Ethiopia", "grid_id":"" },
                            252: { "country_code":"SO", "name":"Somalia", "grid_id":"" },
                            253: { "country_code":"DJ", "name":"Djibouti", "grid_id":"" },
                            254: { "country_code":"KE", "name":"Kenya", "grid_id":"" },
                            255: { "country_code":"TZ", "name":"Tanzania", "grid_id":"" },
                            256: { "country_code":"UG", "name":"Uganda", "grid_id":"" },
                            257: { "country_code":"BI", "name":"Burundi", "grid_id":"" },
                            258: { "country_code":"MZ", "name":"Mozambique", "grid_id":"" },
                            260: { "country_code":"ZM", "name":"Zambia", "grid_id":"" },
                            261: { "country_code":"MG", "name":"Madagascar", "grid_id":"" },
                            262: { "country_code":"YT", "name":"Mayotte", "grid_id":"" },
                            263: { "country_code":"ZW", "name":"Zimbabwe", "grid_id":"" },
                            264: { "country_code":"NA", "name":"Namibia", "grid_id":"" },
                            265: { "country_code":"MW", "name":"Malawi", "grid_id":"" },
                            266: { "country_code":"LS", "name":"Lesotho", "grid_id":"" },
                            267: { "country_code":"BW", "name":"Botswana", "grid_id":"" },
                            268: { "country_code":"SZ", "name":"Eswatini", "grid_id":"" },
                            269: { "country_code":"KM", "name":"Comoros", "grid_id":"" },
                            27: { "country_code":"ZA", "name":"South Africa", "grid_id":"" },
                            291: { "country_code":"ER", "name":"Eritrea", "grid_id":"" },
                            297: { "country_code":"AW", "name":"Aruba", "grid_id":"" },
                            298: { "country_code":"FO", "name":"Faroe Islands", "grid_id":"" },
                            299: { "country_code":"GL", "name":"Greenland", "grid_id":"" },
                            30: { "country_code":"GR", "name":"Greece", "grid_id":"" },
                            31: { "country_code":"NL", "name":"Netherlands", "grid_id":"" },
                            32: { "country_code":"BE", "name":"Belgium", "grid_id":"" },
                            33: { "country_code":"FR", "name":"France", "grid_id":"" },
                            34: { "country_code":"ES", "name":"Spain", "grid_id":"" },
                            350: { "country_code":"GI", "name":"Gibraltar", "grid_id":"" },
                            351: { "country_code":"PT", "name":"Portugal", "grid_id":"" },
                            352: { "country_code":"LU", "name":"Luxembourg", "grid_id":"" },
                            353: { "country_code":"IE", "name":"Ireland", "grid_id":"" },
                            354: { "country_code":"IS", "name":"Iceland", "grid_id":"" },
                            355: { "country_code":"AL", "name":"Albania", "grid_id":"" },
                            356: { "country_code":"MT", "name":"Malta", "grid_id":"" },
                            357: { "country_code":"CY", "name":"Cyprus", "grid_id":"" },
                            358: { "country_code":"AX", "name":"Aland Islands", "grid_id":"" },
                            359: { "country_code":"BG", "name":"Bulgaria", "grid_id":"" },
                            36: { "country_code":"HU", "name":"Hungary", "grid_id":"" },
                            370: { "country_code":"LT", "name":"Lithuania", "grid_id":"" },
                            371: { "country_code":"LV", "name":"Latvia", "grid_id":"" },
                            372: { "country_code":"EE", "name":"Estonia", "grid_id":"" },
                            373: { "country_code":"MD", "name":"Moldova", "grid_id":"" },
                            374: { "country_code":"AM", "name":"Armenia", "grid_id":"" },
                            375: { "country_code":"BY", "name":"Belarus", "grid_id":"" },
                            376: { "country_code":"AD", "name":"Andorra", "grid_id":"" },
                            377: { "country_code":"MC", "name":"Monaco", "grid_id":"" },
                            378: { "country_code":"SM", "name":"San Marino", "grid_id":"" },
                            380: { "country_code":"UA", "name":"Ukraine", "grid_id":"" },
                            381: { "country_code":"RS", "name":"Serbia", "grid_id":"" },
                            382: { "country_code":"ME", "name":"Montenegro", "grid_id":"" },
                            383: { "country_code":"XK", "name":"Kosovo", "grid_id":"" },
                            385: { "country_code":"HR", "name":"Croatia", "grid_id":"" },
                            386: { "country_code":"SI", "name":"Slovenia", "grid_id":"" },
                            387: { "country_code":"BA", "name":"Bosnia and Herzegovina", "grid_id":"" },
                            389: { "country_code":"MK", "name":"North Macedonia", "grid_id":"" },
                            39: { "country_code":"VA", "name":"Vatican City", "grid_id":"" },
                            40: { "country_code":"RO", "name":"Romania", "grid_id":"" },
                            41: { "country_code":"CH", "name":"Switzerland", "grid_id":"" },
                            420: { "country_code":"CZ", "name":"Czechia", "grid_id":"" },
                            421: { "country_code":"SK", "name":"Slovakia", "grid_id":"" },
                            423: { "country_code":"LI", "name":"Liechtenstein", "grid_id":"" },
                            43: { "country_code":"AT", "name":"Austria", "grid_id":"" },
                            45: { "country_code":"DK", "name":"Denmark", "grid_id":"" },
                            46: { "country_code":"SE", "name":"Sweden", "grid_id":"" },
                            47: { "country_code":"SJ", "name":"Svalbard", "grid_id":"" },
                            48: { "country_code":"PL", "name":"Poland", "grid_id":"" },
                            49: { "country_code":"DE", "name":"Germany", "grid_id":"" },
                            500: { "country_code":"FK", "name":"Falkland Islands", "grid_id":"" },
                            501: { "country_code":"BZ", "name":"Belize", "grid_id":"" },
                            502: { "country_code":"GT", "name":"Guatemala", "grid_id":"" },
                            503: { "country_code":"SV", "name":"El Salvador", "grid_id":"" },
                            504: { "country_code":"HN", "name":"Honduras", "grid_id":"" },
                            505: { "country_code":"NI", "name":"Nicaragua", "grid_id":"" },
                            506: { "country_code":"CR", "name":"Costa Rica", "grid_id":"" },
                            507: { "country_code":"PA", "name":"Panama", "grid_id":"" },
                            508: { "country_code":"PM", "name":"Saint Pierre and Miquelon", "grid_id":"" },
                            509: { "country_code":"HT", "name":"Haiti", "grid_id":"" },
                            51: { "country_code":"PE", "name":"Peru", "grid_id":"" },
                            52: { "country_code":"MX", "name":"Mexico", "grid_id":"" },
                            53: { "country_code":"CU", "name":"Cuba", "grid_id":"" },
                            54: { "country_code":"AR", "name":"Argentina", "grid_id":"100002260" },
                            55: { "country_code":"BR", "name":"Brazil", "grid_id":"" },
                            56: { "country_code":"CL", "name":"Chile", "grid_id":"" },
                            57: { "country_code":"CO", "name":"Colombia", "grid_id":"" },
                            58: { "country_code":"VE", "name":"Venezuela", "grid_id":"" },
                            591: { "country_code":"BO", "name":"Bolivia", "grid_id":"" },
                            592: { "country_code":"GY", "name":"Guyana", "grid_id":"" },
                            593: { "country_code":"EC", "name":"Ecuador", "grid_id":"" },
                            594: { "country_code":"GF", "name":"French Guiana", "grid_id":"" },
                            595: { "country_code":"PY", "name":"Paraguay", "grid_id":"" },
                            596: { "country_code":"MQ", "name":"Martinique", "grid_id":"" },
                            597: { "country_code":"SR", "name":"Suriname", "grid_id":"" },
                            598: { "country_code":"UY", "name":"Uruguay", "grid_id":"" },
                            599: { "country_code":"BQ", "name":"Bonaire", "grid_id":"" },
                            60: { "country_code":"MY", "name":"Malaysia", "grid_id":"" },
                            62: { "country_code":"ID", "name":"Indonesia", "grid_id":"" },
                            63: { "country_code":"PH", "name":"Philippines", "grid_id":"" },
                            64: { "country_code":"NZ", "name":"New Zealand", "grid_id":"" },
                            65: { "country_code":"SG", "name":"Singapore", "grid_id":"" },
                            66: { "country_code":"TH", "name":"Thailand", "grid_id":"" },
                            670: { "country_code":"TL", "name":"Timor Leste", "grid_id":"" },
                            672: { "country_code":"NF", "name":"Norfolk Island", "grid_id":"" },
                            673: { "country_code":"BN", "name":"Brunei", "grid_id":"" },
                            674: { "country_code":"NR", "name":"Nauru", "grid_id":"" },
                            675: { "country_code":"PG", "name":"Papua New Guinea", "grid_id":"" },
                            676: { "country_code":"TO", "name":"Tonga", "grid_id":"" },
                            677: { "country_code":"SB", "name":"Solomon Islands", "grid_id":"" },
                            678: { "country_code":"VU", "name":"Vanuatu", "grid_id":"" },
                            679: { "country_code":"FJ", "name":"Fiji", "grid_id":"" },
                            680: { "country_code":"PW", "name":"Palau", "grid_id":"" },
                            681: { "country_code":"WF", "name":"Wallis and Futuna", "grid_id":"" },
                            682: { "country_code":"CK", "name":"Cook Islands", "grid_id":"" },
                            683: { "country_code":"NU", "name":"Niue", "grid_id":"" },
                            685: { "country_code":"WS", "name":"Samoa", "grid_id":"" },
                            686: { "country_code":"KI", "name":"Kiribati", "grid_id":"" },
                            687: { "country_code":"NC", "name":"New Caledonia", "grid_id":"" },
                            688: { "country_code":"TV", "name":"Tuvalu", "grid_id":"" },
                            689: { "country_code":"PF", "name":"French Polynesia", "grid_id":"" },
                            690: { "country_code":"TK", "name":"Tokelau", "grid_id":"" },
                            691: { "country_code":"FM", "name":"Micronesia", "grid_id":"" },
                            692: { "country_code":"MH", "name":"Marshall Islands", "grid_id":"" },
                            7: { "country_code":"KZ", "name":"Kazakhstan", "grid_id":"" },
                            81: { "country_code":"JP", "name":"Japan", "grid_id":"" },
                            82: { "country_code":"KR", "name":"South Korea", "grid_id":"" },
                            84: { "country_code":"VN", "name":"Vietnam", "grid_id":"" },
                            850: { "country_code":"KP", "name":"North Korea", "grid_id":"" },
                            852: { "country_code":"HK", "name":"Hong Kong", "grid_id":"" },
                            853: { "country_code":"MO", "name":"Macao", "grid_id":"" },
                            855: { "country_code":"KH", "name":"Cambodia", "grid_id":"" },
                            856: { "country_code":"LA", "name":"Laos", "grid_id":"" },
                            86: { "country_code":"CN", "name":"China", "grid_id":"" },
                            880: { "country_code":"BD", "name":"Bangladesh", "grid_id":"" },
                            886: { "country_code":"TW", "name":"Taiwan", "grid_id":"" },
                            90: { "country_code":"TR", "name":"Turkey", "grid_id":"" },
                            91: { "country_code":"IN", "name":"India", "grid_id":"" },
                            92: { "country_code":"PK", "name":"Pakistan", "grid_id":"" },
                            93: { "country_code":"AF", "name":"Afghanistan", "grid_id":"" },
                            94: { "country_code":"LK", "name":"Sri Lanka", "grid_id":"" },
                            95: { "country_code":"MM", "name":"Myanmar", "grid_id":"" },
                            960: { "country_code":"MV", "name":"Maldives", "grid_id":"" },
                            961: { "country_code":"LB", "name":"Lebanon", "grid_id":"" },
                            962: { "country_code":"JO", "name":"Jordan", "grid_id":"" },
                            963: { "country_code":"SY", "name":"Syria", "grid_id":"" },
                            964: { "country_code":"IQ", "name":"Iraq", "grid_id":"" },
                            965: { "country_code":"KW", "name":"Kuwait", "grid_id":"" },
                            966: { "country_code":"SA", "name":"Saudi Arabia", "grid_id":"" },
                            967: { "country_code":"YE", "name":"Yemen", "grid_id":"" },
                            968: { "country_code":"OM", "name":"Oman", "grid_id":"" },
                            970: { "country_code":"PS", "name":"Palestine", "grid_id":"" },
                            971: { "country_code":"AE", "name":"United Arab Emirates", "grid_id":"" },
                            972: { "country_code":"IL", "name":"Israel", "grid_id":"" },
                            973: { "country_code":"BH", "name":"Bahrain", "grid_id":"" },
                            974: { "country_code":"QA", "name":"Qatar", "grid_id":"" },
                            975: { "country_code":"BT", "name":"Bhutan", "grid_id":"" },
                            976: { "country_code":"MN", "name":"Mongolia", "grid_id":"" },
                            977: { "country_code":"NP", "name":"Nepal", "grid_id":"" },
                            98: { "country_code":"IR", "name":"Iran", "grid_id":"" },
                            992: { "country_code":"TJ", "name":"Tajikistan", "grid_id":"" },
                            993: { "country_code":"TM", "name":"Turkmenistan", "grid_id":"" },
                            994: { "country_code":"AZ", "name":"Azerbaijan", "grid_id":"" },
                            995: { "country_code":"GE", "name":"Georgia", "grid_id":"" },
                            996: { "country_code":"KG", "name":"Kyrgyzstan", "grid_id":"" },
                            998: { "country_code":"UZ", "name":"Uzbekistan", "grid_id":"" },
                        };

                        function getFlagEmoji(countryCode) {
                            const codePoints = countryCode.toUpperCase().split('').map(char =>  127397 + char.charCodeAt());
                            return String.fromCodePoint(...codePoints);
                        }

                        jQuery(document).ready(function(){
                            // Location Autocomplete from Phone Number Country Code Autosuggest
                            var phone_input = jQuery( '#edit-contact_phone input' );
                            var phone_help_label = jQuery(document).find('#phone-location-label');
                            var current_flag_code = '';
                            var country_code_regex = new RegExp(/\+\d+[\s|\-].*?/);

                            phone_input.keyup( function() {
                                if ( phone_input.text().length == 0 ) {
                                    phone_help_label.text('');
                                }
                                if ( country_code_regex.test( phone_input.val() ) ) {
                                    var phone_country_code = phone_input.val().match( /\+(\d+)[\s|\-].*?/ )[1];
                                    var new_phone_input = $(this).val();
                                    new_phone_input = new_phone_input.match( /^.*?(\+.*$)/ )[1];        
                                    if ( current_flag_code !== country_codes[phone_country_code] ) {
                                        if ( country_codes[phone_country_code] ) {
                                            current_flag_code = country_codes[phone_country_code].country_code;
                                            var link_id = '<?php if ( DT_Mapbox_API::get_key() ) { esc_attr_e( 'location_autosuggest_mapbox' );
                                                           } else { esc_attr_e( 'location_autosuggest' ); } ?>';
                                            var country_flag = getFlagEmoji(current_flag_code);
                                            var country_name = country_codes[phone_country_code].name;
                                            var grid_id = country_codes[phone_country_code].grid_id;
                                            phone_help_label.html(`${country_name} phone number? ${country_flag} <a data-grid_id="${grid_id}" data-country_name="${country_name}" id="${link_id}">Set ${country_name} as contact's location.</a>`);    
                                        } else {
                                            phone_help_label.text('⚠️ Warning: Phone number appears to be in an invalid format');
                                        }
                                    }
                                }
                            });
                        })
                    </script>
                <?php endif; ?>
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
                                   />
                        </div>
                        <p class="help-text" id="phone-location-label"></p>
                        <?php if ( $field_key === 'contact_phone' ) : ?>
                        <?php endif; ?>
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

