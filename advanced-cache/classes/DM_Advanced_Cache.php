<?php
defined( 'ABSPATH' ) || die;

class DM_Advanced_Cache {
    /**
     * @var DM_Request_Cache Request Cache object.
     */
    private $request = null;

    /**
     * Determines the appropriate logic for response that WordPress has provided.
     *
     * @var string Type of request; `page`, `redirect`, `error`, `notfound`, and `unknown` are valid values.
     */
    private $response_type = 'page';

    /**
     * Stores the Status Code in state for use through Advanced Cache.
     *
     * @var int HTTP Status Code for the current request.
     */
    private $status_code = -1;

    /**
     * Current request URL.
     *
     * @var string URL.
     */
    private $url = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->set_url();

        $this->request = new DM_Request_Cache( $this->url );
        $cache_data    = $this->request->get();

        /**
         * Ensure the Cache entry is 1) available and 2) of the correct format.
         */
        if ( empty( $cache_data ) || ! isset( $cache_data['redirect'] ) || ! isset( $cache_data['headers'] ) || ! isset( $cache_data['body'] ) ) {
            ob_start( array( $this, 'cache_output' ) );

            add_filter( 'status_header', array( $this, 'status_header' ), 10, 2 );
            add_filter( 'wp_redirect_status', array( $this, 'redirect_status' ), 10, 2 );
            return;
        }

        if ( $cache_data['redirect'] ) {
            $this->action_redirect( $cache_data );
        }

        if ( ! empty( $cache_data ) ) {
            $this->do_headers( $cache_data['headers'] );
            die( $this->do_output( $cache_data['body'] ) );
        }
    }

    /**
     * Action the Cached redirect request.
     *
     * @param  array $data Request Cache Entry.
     * @return void
     */
    private function action_redirect( $data = [] ) {
        /**
         * Pull together the other headers from WordPress when it was cached as well as the Dark Matter headers.
         */
        $this->do_headers( $data['headers'] );

        /**
         * Determine which protocol PHP is using.
         */
        $protocol = 'HTTP/1.0';

        if ( in_array( $_SERVER['SERVER_PROTOCOL'], [ 'HTTP/2.0', 'HTTP/1.1', 'HTTP/1.0' ], true ) ) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        }

        /**
         * Generate the header which tells the browser that the request is a redirect.
         */
        $text = [
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
        ];

        if ( ! empty( $text[ $data['http_code'] ] ) ) {
            header( $protocol . ' ' . $data['http_code'] . ' ' . $text[ $data['http_code'] ], true );
        } else {
            header( $protocol . ' 302 Found', true );
        }

        /**
         * Finally, issue the location header telling the browser where to go.
         */
        header( 'Location: ' . $data['location'], true );
        die;
    }

    /**
     * Handle the output caching for the request. This is done by utilising the
     * output buffering feature of PHP.
     *
     * @param  string $output HTML as generated by WordPress.
     * @return string         HTML, either from Cache or by WordPress.
     */
    public function cache_output( $output = '' ) {
        if ( ! $this->do_cache() ) {
            header( 'X-DarkMatter-Cache: BYPASS' );
            return $output;
        }

        $data = $this->request->set( $output, headers_list() );

        $this->do_headers();
        return $this->do_output( $data['body'] );
    }

    /**
     * Determine if the current response should be cached.
     *
     * @return boolean Return true if the current response should be cached. False if it should not.
     */
    public function do_cache() {
        $do_cache = true;

        /**
         * Ensure the Response Type can be cached.
         */
        if ( ! in_array( $this->response_type, [ 'page', 'redirect' ], true ) ) {
            $do_cache = false;
        }

        /**
         * Check Cookies to make sure that caching is suitable, i.e. do not cache if the User is logged in.
         */
        $cookies = $_COOKIE;
        $bypass  = apply_filters( 'dark_matter_cookie_bypass', [] );

        if ( ! empty( $cookies ) && is_array( $cookies ) ) {
            foreach ( $cookies as $name => $value ) {
                /**
                 * Check for Login. We bypass the override options if the User is logged in.
                 */
                if ( 0 === stripos( $name, 'wp_' ) || 0 === stripos( $name, 'wordpress_' ) ) {
                    $do_cache = false;
                }

                /**
                 * Check to see if the cookie is included in the Bypass set.
                 */
                if ( in_array( $name, $bypass ) ) {
                    $do_cache = false;
                }
            }
        }

        return apply_filters( 'dark_matter_do_cache', $do_cache, $this->response_type, $this->status_code );
    }

    /**
     *
     * @param array $headers
     */
    public function do_headers( $headers = [] ) {
        /**
         * This is for a generated response.
         */
        if ( empty( $headers ) ) {
            header( 'X-DarkMatter-Cache: LOOKUP' );
            return;
        }

        /**
         * This is a cached response.
         */
        header( 'X-DarkMatter-Cache: HIT' );

        foreach ( $headers as $name => $value ) {
            header( "{$name}: {$value}", true );
        }
    }

    /**
     * Produces the entire HTML output.
     *
     * @param  string $html HTML, either generated or from Cache.
     * @return string       HTML, possibly with additional details from Dark Matter.
     */
    private function do_output( $html = '' ) {
        $head_pos = strpos( $html, '</body>' );

        if ( false !== $head_pos ) {
            $debug = <<<HTML
<!--
________  ________  ________  ___  __            _____ ______   ________  _________  _________  _______   ________
|\   ___ \|\   __  \|\   __  \|\  \|\  \         |\   _ \  _   \|\   __  \|\___   ___\\___   ___\\  ___ \ |\   __  \
\ \  \_|\ \ \  \|\  \ \  \|\  \ \  \/  /|_       \ \  \\\__\ \  \ \  \|\  \|___ \  \_\|___ \  \_\ \   __/|\ \  \|\  \
 \ \  \ \\ \ \   __  \ \   _  _\ \   ___  \       \ \  \\|__| \  \ \   __  \   \ \  \     \ \  \ \ \  \_|/_\ \   _  _\
  \ \  \_\\ \ \  \ \  \ \  \\  \\ \  \\ \  \       \ \  \    \ \  \ \  \ \  \   \ \  \     \ \  \ \ \  \_|\ \ \  \\  \|
   \ \_______\ \__\ \__\ \__\\ _\\ \__\\ \__\       \ \__\    \ \__\ \__\ \__\   \ \__\     \ \__\ \ \_______\ \__\\ _\
    \|_______|\|__|\|__|\|__|\|__|\|__| \|__|        \|__|     \|__|\|__|\|__|    \|__|      \|__|  \|_______|\|__|\|__|
-->
HTML;

            /**
             * Insert the debug just before the closing <body> tag.
             */
            $html = substr_replace( $html, $debug, $head_pos, 0 );
        }

        return $html;
    }

    /**
     * Retrieve the destination for a redirect issued using the WordPress logic.
     *
     * @param  integer $status   HTTP status code for the Redirect (i.e. 301, 302, etc.)
     * @param  string  $location Destination for the redirect to go to.
     * @return integer           HTTP status code, unmodified.
     */
    public function redirect_status( $status = 0, $location = '' ) {
        $this->request->set_redirect( $status, $location, headers_list() );

        return $status;
    }

    /**
     * Set the URL from the current request and to be used in later processing.
     */
    public function set_url() {
        $protocol = 'http://';
        if ( isset( $_SERVER['HTTPS'] ) ) {
            if ( 'on' == strtolower( $_SERVER['HTTPS'] ) || '1' == $_SERVER['HTTPS'] ) {
                $protocol = 'https://';
            }
        } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            $protocol = 'https://';
        }

        $host = rtrim( trim( $_SERVER['HTTP_HOST'] ), '/' );
        $path = ltrim( trim( $_SERVER['REQUEST_URI'] ), '/' );

        $this->url = $protocol . $host . '/' . $path;
    }

    /**
     * Retrieve the Status Code of the current request.
     *
     * @param  string  $header The header as generated by WordPress.
     * @param  integer $code   Status - i.e. 200 / 404 / 500 - which corresponds to the current request.
     * @return string          WordPress generated header string, returned unchanged.
     */
    public function status_header( $header = '', $code = 0 ) {
        $this->status_code = absint( $code );

        /**
         * Set the response type property based on the status code. This will be used later for determining the best way
         * for Dark Matter to respond.
         */
        if ( 200 === $this->status_code ) {
            $this->response_type = 'page';
        } elseif ( 404 === $this->status_code ) {
            $this->response_type = 'notfound';
        } elseif ( in_array( $this->status_code, [ 301, 302, 303, 307 ], true ) ) {
            $this->response_type = 'redirect';
        } elseif ( 5 === intval( $this->status_code / 100 ) ) {
            $this->response_type = 'error';
        }

        return $header;
    }

    /**
     * Return the Singleton Instance of the class.
     *
     * @return bool|DM_Advanced_Cache
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}

DM_Advanced_Cache::instance();