<?php
/*
Plugin Name: WP Github Gist
Plugin URI: http://sudarmuthu.com/wordpress/wp-github-gist
Description: Embed files and gist from Github in your blog posts or pages.
Author: Sudar
Version: 0.4
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author URI: http://sudarmuthu.com/
Text Domain: wp-github-gist

=== RELEASE NOTES ===
Check readme file for full release notes

Based on Github Gist Plugin http://wordpress.org/extend/plugins/github-gist by Jingwen Owen Ou
Used the Gist-it script https://github.com/sudar/gist-it by Robert Krimen
*/

/*  Copyright 2010  Sudar Muthu  (email : sudar@sudarmuthu.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The main Plugin Class
 */
class WPGithubGist {

    // menu slug
    const MENU_SLUG = 'wp-github-gist';

    // gist
    const REGEXP_GIST_URL = "\"https:\/\/gist.github.com\/(.+)\.js\?file=(.+)\"";

    // github
    const GIST_IT_SERVER = "http://gist-it.sudarmuthu.com";
    const GITHUB = "/github";

    // for help screens
	private $admin_page;
	private $admin_screen;

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'wp-github-gist', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        // Settings hooks
        add_action( 'admin_menu', array(&$this, 'register_settings_page') );
        add_action( 'admin_init', array(&$this, 'add_settings') );

        // register short code
        add_shortcode('gist', array(&$this, 'gist_shortcode_handler'));
        add_shortcode('github', array(&$this, 'github_shortcode_handler'));

        // add action links
        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'add_action_links'));
    }

    /**
     * Register the settings page
     */
    function register_settings_page() {
        //Save the handle to your admin page - you'll need it to create a WP_Screen object
        $this->admin_page = add_options_page( __('wp-github-gist', 'wp-github-gist'), __('wp-github-gist', 'wp-github-gist'), 'manage_options', self::MENU_SLUG, array(&$this, 'settings_page') );

		add_action("load-{$this->admin_page}",array(&$this,'create_help_panel'));
    }

    /**
     * Add Help Panel
     */
	function create_help_panel() {

		/**
		 * Create the WP_Screen object against your admin page handle
		 * This ensures we're working with the right admin page
		 */
		$this->admin_screen = WP_Screen::get($this->admin_page);

		/**
		 * Content specified inline
		 */
		$this->admin_screen->add_help_tab(
			array(
				'title'    => __('About Plugin', 'wp-github-gist'),
				'id'       => 'about_tab',
				'content'  => '<p>' . __("WP Github Gist WordPress Plugin, provides the ability to embed gist and files from Github in your blog posts or pages. Even though Github doesn't provide a way to embed files, this Plugin still works by using the gist-it service.", 'wp-github-gist') . '</p>',
				'callback' => false
			)
		);

        // Add help sidebar
		$this->admin_screen->set_help_sidebar(
            '<p><strong>' . __('More information', 'wp-github-gist') . '</strong></p>' .
            '<p><a href = "http://sudarmuthu.com/wordpress/wp-github-gist">' . __('Plugin Homepage/support', 'wp-github-gist') . '</a></p>' .
            '<p><a href = "http://sudarmuthu.com/blog">' . __("Plugin author's blog", 'wp-github-gist') . '</a></p>' .
            '<p><a href = "http://sudarmuthu.com/wordpress/">' . __("Other Plugin's by Author", 'wp-github-gist') . '</a></p>'
        );
	}

    /**
     * add options
     */
    function add_settings() {
        // Register options
        register_setting( 'wp-github-gist-options', 'wp-github-gist-options', array(&$this, 'validate_settings'));

        //Add default Options section
        add_settings_section('wgg_global_section', '', array(&$this, 'wgg_global_section_text'), self::MENU_SLUG);

		// add setting fields
        add_settings_field('gist-it-server', __('Gist it Server', 'wp-github-gist'), array(&$this, 'wgg_gist_it_server_callback'), self::MENU_SLUG, 'wgg_global_section');
    }

    /**
     * Display the Settings page
     */
    function settings_page() {
?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e( 'wp-github-gist Settings', 'wp-github-gist' ); ?></h2>

            <iframe height = "950" src = "http://sudarmuthu.com/projects/wordpress/wp-github-gist/sidebar.php?color=<?php echo get_user_option('admin_color'); ?>"></iframe>

			<div style = "float:left; width:75%">
				<form id="smer_form" method="post" action="options.php">
					<?php settings_fields('wp-github-gist-options'); ?>
					<?php do_settings_sections(self::MENU_SLUG); ?>

					<p class="submit">
						<input type="submit" name="wp-github-gist-submit" class="button-primary" value="<?php _e('Save Changes', 'wp-github-gist') ?>" />
					</p>
				</form>
			</div>
        </div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array(&$this, 'add_footer_links'));
    }

    /**
     * Adds Footer links.
     *
     * Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
     */
    function add_footer_links() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf('%1$s ' . __("plugin", 'wp-github-gist') .' | ' . __("Version", 'wp-github-gist') . ' %2$s | '. __('by', 'wp-github-gist') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
    }

    /**
     * hook to add action links
     * @param <type> $links
     * @return <type>
     */
    function add_action_links( $links ) {
        // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=' . self::MENU_SLUG . '">' . __("Settings", 'wp-github-gist') . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Validate the options entered by the user
     *
     * @param <type> $input
     * @return <type>
     */
    function validate_settings($input) {
        $input['gist-it-server'] = esc_url($input['gist-it-server'], array('http', 'https'));
        return $input;
    }

    /**
     * Print global section text
     */
    function  wgg_global_section_text() {
		// Empty as of now
    }

	/**
	 * Callback for printing Gist it server Setting
	 *
	 * @return void
	 * @author Sudar
	 */
    function wgg_gist_it_server_callback() {
        $options = get_option('wp-github-gist-options');

        if (empty($options['gist-it-server'])) {
            $options['gist-it-server'] = self::GIST_IT_SERVER;
        }

        echo "<input id='gist-it-server' name='wp-github-gist-options[gist-it-server]' size='40' type='text' value='{$options['gist-it-server']}' ><br>";
        _e('By default <code>http://gist-it.sudarmuthu.com</code> server will be used', 'wp-github-gist');
        _e('But you can also setup your own gist-it server and use it. Instructions on how to deploy your own gist-it server, can be found in the <a href = "http://sudarmuthu.com/wordpress/wp-github-gist#settings">Plugins homepage</a>', 'wp-github-gist');
    }

    /**
     * Handler for gist shortcode
     *
     * @param  array  $atts    Attributes array
     * @param  string $content Content inside the shortcode
     * @return string          The string to be replaced with
     */
    function gist_shortcode_handler($atts, $content = null) {
        extract(shortcode_atts(array(
            'id'    => null,
            'user'  => null,
            'file'  => null,
            'width' => '100%'
            ), $atts));

        $user  = apply_filters( 'wp-github-gist-user', $user, $id );

        $id    = trim( $id );
        $user  = trim( $user );
        $file  = trim( $file );
        $width = trim( $width );

        if ($content != null && preg_match("/".self::REGEXP_GIST_URL."/", $content, $matches)) {
            $id = $matches[1];
            $file = $matches[2];
        }

        if ( $id == null && $file == null ) {
            return "Error when loading gists from https://gist.github.com/." . $content;
        }

        $gist_html     = $this->get_gist_embed_script( $id, $user, $file );
        $gist_raw      = $this->get_gist_raw( $id, $user, $file );

        $wrap_html     = '<div class="wrap_githubgist" style="width:' . $width . '">';
        $wrap_html_end = '</div>';

        return $wrap_html . $gist_html . $gist_raw . $wrap_html_end;
    }

    /**
     * Handler for github shortcode
     *
     * @param <type> $atts
     * @param <type> $content
     * @return <type>
     */
    function github_shortcode_handler($atts, $content = null) {
        extract(shortcode_atts(array(
            'file' => null,
            'start_line' => 0,
            'end_line' => 0,
            'width' => '100%'
            ), $atts));

        if ($file == null) {
            return "Error when github pages." . $content;
        }

        $github_html = $this->get_github_embed_script($file, $start_line, $end_line);
        $wrap_html = '<div class="wrap_githubgist" style="width:'.$width.'">';
        $wrap_html_end = '</div>';

        return $wrap_html . $github_html . $wrap_html_end;
    }

    /**
     * Get Embed script for github files
     *
     * @param <type> $id
     * @param <type> $file
     * @return <type>
     */
    private function get_github_embed_script($file, $start_line = 0, $end_line = 0) {

        $options = get_option('wp-github-gist-options');
        if (empty($options['gist-it-server'])) {
            $options['gist-it-server'] = self::GIST_IT_SERVER;
        }

        $script = '<script src = "' . $options['gist-it-server'] . self::GITHUB . $file;

        if ($start_line > 0 || $end_line > 0) {
            $script .= "?slice=$start_line:$end_line&footer=minimal";
        } else {
            $script .= "?footer=minimal";
        }

        $script .= '"></script>';

        return $script;
    }

    /**
     * Get Embed script for gist
     *
     * @param  string $id   Id of the gist
     * @param  string $user User of the gist
     * @param  string $file File of the gist
     * @return string       Embed script
     */
    private function get_gist_embed_script( $id, $user = '', $file = '' ) {
        $script_url = 'https://gist.github.com/';

        if ( $user != '' ) {
            $script_url .= $user . '/';
        }

        $script_url .= $id . '.js';

        if ( $file != '') {
            $script_url .= "?file=" . $file;
        }

        $script = $this->get_content_from_url( $script_url );

        if ( $script != '' ) {
            $script = "<script>" . $script . "</script>";
        }

        return $script;
    }

    /**
     * Get the raw content of the gist
     *
     * @param  string $id       Gist id
     * @param  string $user     Gist User
     * @param  string $file     Gist file name. Optional
     * @return string $gist_raw Raw gist content
     */
    private function get_gist_raw( $id, $user = '', $file = '' ) {
        $url = "https://gist.github.com/$user/$id/raw/";

        if ( $file != '' ) {
            $url .= $file;
        }

        $gist_raw = $this->get_content_from_url($url);

        if ($gist_raw != '') {
            $gist_raw =  "<div style='margin-bottom:1em;padding:0;'>" .
                "<noscript><code><pre style='overflow:auto;margin:0;padding:0;border:1px solid #DDD;'>" .
                    htmlentities( $gist_raw ) .
                "</pre></code></noscript></div>";
        }

        return $gist_raw;
    }

    /**
     * Helper function to get data from remote server
     *
     * @param <type> $url
     * @return <type>
     */
    private function get_content_from_url($url) {
        $key = md5($url) . 'wp-github-gist';

        if (false === ($content = get_transient($key))) {
            $content = wp_remote_retrieve_body(wp_remote_get($url)) ;
            if ($content != '') {
                set_transient($key, $content, 86400); // one day 60 * 60 * 24
            }
        }

        return $content;
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'WPGithubGist' ); function WPGithubGist() { global $WPGithubGist; $WPGithubGist = new WPGithubGist(); }
?>
