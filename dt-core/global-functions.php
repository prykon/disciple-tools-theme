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
         * @author matzeeable
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
     * The the base site url with, including the subfolder if wp is installed in a subfolder.
     * @return string
     */
    if ( ! function_exists( 'dt_get_url_path' ) ) {
        function dt_get_url_path() {
            if ( isset( $_SERVER["HTTP_HOST"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
                return trim( str_replace( get_site_url(), "", $url ), '/' );
            }
            return '';
        }
    }

    if ( ! function_exists( 'dt_array_to_sql' ) ) {
        function dt_array_to_sql( $values) {
            if (empty( $values )) {
                return 'NULL';
            }
            foreach ($values as &$val) {
                if ('\N' === $val) {
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
        function dt_format_date( $date, $format = 'short') {
            $date_format = get_option( 'date_format' );
            $time_format = get_option( 'time_format' );
            if ($format === 'short') {
                $format = $date_format;
            } else if ($format === 'long') {
                $format = $date_format . ' ' . $time_format;
            }
            if (is_numeric( $date )) {
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
        function dt_get_year_from_timestamp( int $time) {
            return gmdate( "Y", $time );
        }
    }

    if ( ! function_exists( 'dt_sanitize_array_html' ) ) {
        function dt_sanitize_array_html( $array) {
            array_walk_recursive($array, function ( &$v) {
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

    if ( ! function_exists( 'dt_get_translations' ) ) {
        function dt_get_translations() {
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $translations = wp_get_available_translations();
            $translations["ar_MA"] = [
                "language" => "ar_MA",
                "native_name" => "العربية (المغرب)",
                "english_name" => "Arabic (Morocco)",
                "iso" => [ "ar" ]
            ];
            $translations["sw"] = [
                "language" => "sw",
                "native_name" => "Kiswahili",
                "english_name" => "Swahili",
                "iso" => [ "sw" ]
            ];
            $translations["sr_BA"] = [
                "language" => "sr_BA",
                "native_name" => "српски",
                "english_name" => "Serbian",
                "iso" => [ "sr" ]
            ];
            return $translations;
        }
    }

    if ( ! function_exists( 'dt_get_available_languages' ) ) {
        function dt_get_available_languages() {
            $translations = dt_get_translations();
            $available_language_codes = get_available_languages( get_template_directory() .'/dt-assets/translation' );
            $available_translations = [];

            array_push( $available_translations, array(
                'language' => 'en_US',
                'native_name' => 'English'
            ) );

            foreach ( $available_language_codes as $code ){
                if ( isset( $translations[$code] )){
                    $available_translations[] = $translations[$code];
                }
            }
            return $available_translations;
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

        if ( !empty( $metadata )) {
            $meta_array = explode( '-', $metadata ); // Separate the type and id
            $type = $meta_array[0];
            $id = $meta_array[1];

            if ($type == 'user') {
                $value = get_user_by( 'id', $id );
                $rv = $value->display_name;
            } else {
                $value = get_term( $id );
                $rv = $value->name;
            }
            if ($return) {
                return $rv;
            } else {
                echo esc_html( $rv );
            }
        }
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
        foreach ( $array2 as $key => &$value ){
            if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ){
                $merged[$key] = dt_array_merge_recursive_distinct( $merged[$key], $value );
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * Accepts types: key_select, multi_select, text, number, date, connection, location, communication_channel
     *
     * @param $field_key
     * @param $fields
     * @param $post
     * @param bool $show_extra_controls // show typeahead create button
     * @param bool $show_hidden // show hidden select options
     * @param string $field_id_prefix // add a prefix to avoid fields with duplicate ids.
     */
    function render_field_for_display( $field_key, $fields, $post, $show_extra_controls = false, $show_hidden = false, $field_id_prefix = '' ){
        if ( isset( $fields[$field_key]["type"] ) && empty( $fields[$field_key]["custom_display"] ) && empty( $fields[$field_key]["hidden"] ) ) {
            $field_type = $fields[$field_key]["type"];
            $required_tag = ( isset( $fields[$field_key]["required"] ) && $fields[$field_key]["required"] === true ) ? 'required' : '';
            $allowed_types = apply_filters( 'dt_render_field_for_display_allowed_types', [ 'key_select', 'multi_select', 'date', 'datetime', 'text', 'number', 'connection', 'location', 'location_meta', 'communication_channel' ] );
            if ( !in_array( $field_type, $allowed_types ) ){
                return;
            }
            if ( isset( $post['type']["key"], $fields[$field_key]["only_for_types"] ) ) {
                if ( !in_array( $post['type']["key"], $fields[$field_key]["only_for_types"] ) ){
                    return;
                }
            }

            $display_field_id = $field_key;
            if ( !empty( $field_id_prefix ) ) {
                $display_field_id = $field_id_prefix . $field_key;
            }

            ?>
            <div class="section-subheader">
                <?php if ( isset( $fields[$field_key]["icon"] ) ) : ?>
                    <img class="dt-icon" src="<?php echo esc_url( $fields[$field_key]["icon"] ) ?>">
                <?php endif;
                echo esc_html( $fields[$field_key]["name"] );
                ?> <span id="<?php echo esc_html( $display_field_id ); ?>-spinner" class="loading-spinner"></span>
                <?php if ( $field_type === "communication_channel" ) : ?>
                    <button data-list-class="<?php echo esc_html( $display_field_id ); ?>" class="add-button" type="button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                    </button>
                <?php endif ?>
                <!-- location add -->
                <?php if ( ( $field_type === "location" || "location_meta" === $field_type ) && DT_Mapbox_API::get_key() && ! empty( $post ) ) : ?>
                    <button data-list-class="<?php echo esc_html( $field_key ) ?>" class="add-button" id="new-mapbox-search" type="button">
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
                <select class="select-field <?php echo esc_html( $color_select ? "color-select" : "" ); ?>" id="<?php echo esc_html( $display_field_id ); ?>" style="<?php echo esc_html( $color_select ? ( "background-color: " . $active_color ) : "" ); ?>">
                    <option value="" <?php echo esc_html( !isset( $post[$field_key] ) ?: "selected" ) ?>></option>
                    <?php foreach ($fields[$field_key]["default"] as $option_key => $option_value):
                        if ( !$show_hidden && isset( $option_value["hidden"] ) && $option_value["hidden"] === true ){
                            continue;
                        }
                        $selected = isset( $post[$field_key]["key"] ) && $post[$field_key]["key"] === $option_key; ?>
                        <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? "selected" : "" )?>>
                            <?php echo esc_html( $option_value["label"] ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ( $field_type === "multi_select" ) :
                if ( isset( $fields[$field_key]["display"] ) && $fields[$field_key]["display"] === "typeahead" ){
                    ?>
                    <div class="multi_select">
                        <var id="tags-result-container" class="result-container"></var>
                        <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height"
                                               data-field="<?php echo esc_html( $display_field_id );?>"
                                               name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                               autocomplete="off">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="small button-group" style="display: inline-block">
                        <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>
                            <?php
                            $class = ( in_array( $option_key, $post[$field_key] ?? [] ) ) ?
                                "selected-select-button" : "empty-select-button"; ?>
                            <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $display_field_id ); ?>"
                                    class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                                <?php echo esc_html( $fields[$field_key]["default"][$option_key]["label"] ) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            <?php elseif ( $field_type === "text" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="text" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
            <?php elseif ( $field_type === "number" ) :?>
                <input id="<?php echo esc_html( $display_field_id ); ?>" type="number" <?php echo esc_html( $required_tag ) ?>
                       class="text-input"
                       value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
            <?php elseif ( $field_type === "date" ) :?>
                <div class="<?php echo esc_html( $display_field_id ); ?> input-group">
                    <input id="<?php echo esc_html( $display_field_id ); ?>" class="input-group-field dt_date_picker" type="text" autocomplete="off" <?php echo esc_html( $required_tag ) ?>
                           value="<?php echo esc_html( $post[$field_key]["timestamp"] ?? '' ) ?>" >
                    <div class="input-group-button">
                        <button id="<?php echo esc_html( $display_field_id ); ?>-clear-button" class="button alert clear-date-button" data-inputid="<?php echo esc_html( $display_field_id ); ?>" title="Delete Date" type="button">x</button>
                    </div>
                </div>
            <?php elseif ( $field_type === "connection" ) :?>
                <div id="<?php echo esc_attr( $display_field_id . '_connection' ) ?>" class="dt_typeahead">
                    <span id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container"></span>
                    <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height" data-field="<?php echo esc_html( $display_field_id ); ?>"
                                           data-post_type="<?php echo esc_html( $fields[$field_key]["post_type"] ) ?>"
                                           data-field_type="connection"
                                           name="<?php echo esc_html( $display_field_id ); ?>[query]"
                                           placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                           autocomplete="off">
                                </span>
                                <?php if ( $show_extra_controls ) : ?>
                                <span class="typeahead__button">
                                    <button type="button" data-connection-key="<?php echo esc_html( $display_field_id ); ?>" class="create-new-record typeahead__image_button input-height">
                                        <?php $icon = isset( $fields[$field_key]["create-icon"] ) ? $fields[$field_key]["create-icon"] : get_template_directory_uri() . '/dt-assets/images/add-contact.svg'; ?>
                                        <img src="<?php echo esc_html( $icon ) ?>"/>
                                    </button>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ( $field_type === "location" || $field_type === "location_meta" ) :?>
                <?php if ( DT_Mapbox_API::get_key() && empty( $post ) ) : // test if Mapbox key is present ?>
                    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                        <input id="mapbox-search" type="text" class="input-group-field" name="mapbox_search" placeholder="Search Location" autocomplete="off"/>
                        <div class="input-group-button">
                            <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
                            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" style="display:none;" type="button">&times;</button>
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
                <?php else : ?>
                    <div class="dt_location_grid">
                        <var id="location_grid-result-container" class="result-container"></var>
                        <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-location_grid input-height"
                                       data-field="<?php echo esc_html( $display_field_id ); ?>"
                                       data-field_type="location"
                                       name="location_grid[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                       autocomplete="off" />
                            </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ( $field_type === "communication_channel" ) : ?>
                <div id="edit-<?php echo esc_html( $field_key ) ?>" >
                    <?php foreach ( $post[$field_key] ?? [] as $field_value ) : ?>
                        <div class="input-group">
                            <input id="<?php echo esc_html( $field_value["key"] ) ?>"
                                   type="text"
                                   data-field="<?php echo esc_html( $display_field_id ); ?>"
                                   value="<?php echo esc_html( $field_value["value"] ) ?>"
                                   class="dt-communication-channel input-group-field" />
                            <div class="input-group-button">
                                <button class="button alert input-height delete-button-style channel-delete-button delete-button new-<?php echo esc_html( $field_key ); ?>" data-field="<?php echo esc_html( $field_key ); ?>" data-key="<?php echo esc_html( $field_value["key"] ); ?>">&times;</button>
                            </div>
                        </div>
                    <?php endforeach;
                    if ( empty( $post[$field_key] ) ?? [] ): ?>
                        <div class="input-group">
                            <input type="text"
                                    <?php echo esc_html( $required_tag ) ?>
                                   data-field="<?php echo esc_html( $field_key ) ?>"
                                   class="dt-communication-channel input-group-field" />
                        </div>
                    <?php endif ?>
                </div>
            <?php else : ?>
                <?php do_action( 'dt_render_field_for_display_template', $post, $field_type, $field_key, $required_tag ); ?>
            <?php endif;
        }
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
        function dt_is_module_enabled( string $module_key ) : bool {
            $modules = dt_get_option( "dt_post_type_modules" );
            if ( isset( $modules[$module_key] ) && isset( $modules[$module_key]["enabled"] ) &&  ! empty( $modules[$module_key]["enabled"] ) ){
                return true;
            }
            return false;
        }
    }

    /**
     * All code above here.
     */
} // end if ( ! defined( 'DT_FUNCTIONS_READY' ) )

