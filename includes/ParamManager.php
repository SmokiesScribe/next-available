<?php
namespace NextAv\Includes;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles modifications to URL params.
 * 
 * @since 1.0.3
 */
class ParamManager {
    
    /**
     * The URL.
     * 
     * @var string
     */
    private $url;

    /**
     * The nonce action.
     * 
     * @var string
     */
    private $nonce_action = 'nextav_action';

    /**
     * The nonce name.
     * 
     * @var string
     */
    private $nonce_name = '_nextav_nonce';
    
    /**
     * Constructor method.
     * 
     * @since 1.0.3
     * 
     * @param   string  $url    Optional. The URL to modify.
     *                          Defaults to the current URL.
     */
    public function __construct( $url = null ) {
        $this->url = $url ?? $this->current_url();
    }
    
    /**
     * Retrieves the current URL.
     * 
     * @since 1.0.3
     */
    private function current_url() {
        return nextav_curr_url();                
    }

    /**
     * Adds a nonce to the URL.
     * 
     * @since 1.0.4
     * 
     * @return string The URL with the nonce added.
     */
    public function add_nonce() {
        $nonce = wp_create_nonce( $this->nonce_action );
        return $this->add_param( $this->nonce_name, $nonce, $this->url );
    }

    /**
     * Adds multiple parameters to the URL.
     * 
     * @since 1.0.4
     * 
     * @param   array   $params     An associative array of params and values.
     * @param   string  $url        Optional. The url to modify.
     *                              Defaults to the current url.
     * 
     * @return  string  The new url.
     */
    public function add_params( $params, $url = null ) {
        $url = $url ?? $this->url;
        if ( is_array( $params ) && ! empty( $params ) ) {
            foreach ( $params as $param => $value ) {
                $url = $this->add_param( $param, $value, $url );
            }
        }
        return $url;
    }
    
    /**
     * Adds a parameter to the URL.
     * 
     * @since 1.0.3
     * 
     * @param   string  $param      The parameter to add.
     * @param   mixed   $value      The value of the parameter.
     * @param   string  $url        Optional. The url to modify.
     *                              Defaults to the current url.
     * 
     * @return  string  The new url.
     */
    public function add_param( $param, $value, $url = null ) {
        $url = $url ?? $this->url;

        // Validate and sanitize the parameter name
        $param = sanitize_key( $param );

        // Sanitize the parameter value
        if ( is_array( $value ) ) {
            $value = array_map( 'sanitize_text_field', $value );
        } else {
            $value = sanitize_text_field( $value );
        }

        // Parse existing query parameters from the URL
        $parsed_url = wp_parse_url( $url );
        $query_params = [];

        // Check if 'query' exists and parse it
        if ( isset( $parsed_url['query'] ) ) {
            parse_str( $parsed_url['query'], $query_params );
        }

        // Check if the url includes nonce
        if ( ! isset( $query_params[$this->nonce_name] ) ) {
            // Define nonce
            $nonce = wp_create_nonce( $this->nonce_action );

            // Add nonce to params
            $query_params[$this->nonce_name] = $nonce;
        }

        // Add the new parameter
        $query_params[$param] = $value;

        // Rebuild the query string
        $new_query_string = http_build_query( $query_params );

        // Rebuild the URL with updated query parameters
        $url = ( isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '' )
                . ( isset( $parsed_url['host'] ) ? $parsed_url['host'] : '' )
                . ( isset( $parsed_url['path'] ) ? $parsed_url['path'] : '' )
                . '?' . $new_query_string;

        // Add hash fragment if it existed in the original URL
        if ( isset( $parsed_url['fragment'] ) ) {
            $url .= '#' . $parsed_url['fragment'];
        }

        return $url;
    }
    
    /**
     * Removes a parameter from the URL.
     * 
     * @since 1.0.3
     * 
     * @param   string  $param      The parameter to remove.
     * 
     * @return  string  The new url.
     */
    public function remove_param( $param ) {
        // Parse existing query parameters from the URL
        $parsed_url = wp_parse_url( $this->url );
        $query_params = array();

        // Check if 'query' exists and parse it
        if ( isset( $parsed_url['query'] ) ) {
            parse_str( $parsed_url['query'], $query_params );
        }

        // Remove the parameter if it exists
        if ( isset( $query_params[$param] ) ) {
            unset( $query_params[$param] );
        }

        // Rebuild the query string
        $new_query_string = http_build_query( $query_params );

        // Rebuild the URL with updated query parameters
        $this->url = ( isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '' )
                   . ( isset( $parsed_url['host'] ) ? $parsed_url['host'] : '' )
                   . ( isset( $parsed_url['path'] ) ? $parsed_url['path'] : '' )
                   . '?' . $new_query_string;

        // Add hash fragment if it existed in the original URL
        if ( isset( $parsed_url['fragment'] ) ) {
            $this->url .= '#' . $parsed_url['fragment'];
        }
        
        return $this->url;
    }

    /**
     * Strips all query parameters from the URL.
     * 
     * @since 1.0.17
     * 
     * @return  string  The updated URL without query parameters.
     */
    public function strip_params() {
        // Parse the existing URL
        $parsed_url = wp_parse_url( $this->url );

        // Rebuild the URL without query parameters
        $this->url = ( isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '' )
                . ( isset( $parsed_url['host'] ) ? $parsed_url['host'] : '' )
                . ( isset( $parsed_url['path'] ) ? $parsed_url['path'] : '' );

        // Add the hash fragment if it existed in the original URL
        if ( isset( $parsed_url['fragment'] ) ) {
            $this->url .= '#' . $parsed_url['fragment'];
        }
        
        return $this->url;
    }

    /**
     * Retrieves the value of a url param.
     * 
     * @since 1.0.4
     * 
     * @param string $param  The param key.
     */
    public function get( $param ) {
        // Parse the URL to extract query parameters
        $parsed_url = wp_parse_url( $this->url );
        $query_params = [];
    
        // If there's a query string, parse it into an associative array
        if ( isset( $parsed_url['query'] ) ) {
            parse_str( $parsed_url['query'], $query_params );
        }
    
        // Verify nonce from $query_params instead of $_GET
        if ( isset( $query_params[$this->nonce_name] ) ) {
            $nonce = sanitize_text_field( wp_unslash( $query_params[$this->nonce_name] ) );
            if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
                // Exit if nonce fails
                $new_url = $this->remove_nonce();
                if ( ! empty( $new_url ) ) {
                    wp_redirect( esc_url_raw( $new_url ) );
                    exit;
                } else {
                    return;
                }                
            }
        }
    
        // Get value of url param from $query_params
        if ( isset( $query_params[$param] ) ) {
            // Decode the parameter value and then sanitize it
            return sanitize_text_field( wp_unslash( $query_params[$param] ) ) ?? null;
        }

        // Param not found
        return null;
    }

    /**
     * Removes the nonce from the url.
     * 
     * @since 1.0.21
     */
    private function remove_nonce() {
        return $this->remove_param( $this->nonce_name );
    }

    /**
     * Retrieves all url parameters.
     * 
     * @since 1.0.15
     * 
     * @return  array   An array of url params.
     */
    public function get_all_params() {
        
        // Verify nonce
        if ( isset( $_GET[$this->nonce_name] ) ) {
            $nonce = sanitize_text_field( wp_unslash( $_GET[$this->nonce_name] ) );
            if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
                // Exit if nonce fails
                return;
            }
        }

        // Parse the URL to extract the query string
        $parsed_url = wp_parse_url( $this->url );

        // Initialize an empty array for the query parameters
        $query_params = [];

        if ( isset( $parsed_url['query'] ) ) {
            // Parse the query string into an associative array
            parse_str( $parsed_url['query'], $query_params );
        
            // Decode each parameter
            foreach ( $query_params as $key => $value ) {
                if ( is_array( $value ) ) {
                    // If the value is an array, decode each element individually
                    foreach ( $value as &$sub_value ) {
                        $sub_value = urldecode( $sub_value );
                    }
                } else {
                    // Decode the single value
                    $query_params[$key] = urldecode( $value );
                }
            }
        }

        // Return all url params        
        return $query_params;
    }

    /**
     * Retrieves the modified URL.
     * 
     * @since 1.0.3
     * 
     * @return string The modified URL.
     */
    public function get_url() {
        return $this->url;
    }
}
