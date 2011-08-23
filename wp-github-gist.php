<?php
/*
Plugin Name: WP Github Gist
Plugin URI: http://sudarmuthu.com/wordpress/wp-github-gist
Description: Embed files and gist from Github in your blog posts or pages.
Author: Sudar
Version: 0.1
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
License: GPL
Author URI: http://sudarmuthu.com/
Text Domain: wp-github-gist

=== RELEASE NOTES ===
2011-08-23 - v0.1 - Initial Release

Based on Github Gist Plugin http://wordpress.org/extend/plugins/github-gist by Jingwen Owen Ou
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

    const REGEXP_GIST_URL = "\"https:\/\/gist.github.com\/(.+)\.js\?file=(.+)\"";
    const GIST_IT_SERVER = "http://gist-it.sudarmuthu.com/github"; //TODO: Make this a configurable parameter

    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'wp-github-gist', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        // register short code
        add_shortcode('gist', array(&$this, 'gist_shortcode_handler'));
        add_shortcode('github', array(&$this, 'github_shortcode_handler'));
    }

    /**
     * Handler for gist shortcode
     *
     * @param <type> $atts
     * @param <type> $content
     * @return <type>
     */
    function gist_shortcode_handler($atts, $content = null) {
        extract(shortcode_atts(array(
            'id' => null,
            'file' => null,
            ), $atts));

        if ($content != null && preg_match("/".self::REGEXP_GIST_URL."/", $content, $matches)) {
            $id = $matches[1];
            $file = $matches[2];
        }

        if ($id == null && $file == null) {
            return "Error when loading gists from https://gist.github.com/.".$content;
        }

        $gist_html = $this->get_gist_embed_script($id, $file);
        $gist_raw = $this->get_gist_raw($id, $file);

        return $gist_html . $gist_raw;
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
            'end_line' => 0
            ), $atts));

        if ($file == null) {
            return "Error when github pages." . $content;
        }

        return $this->get_github_embed_script($file, $start_line, $end_line);
    }

    /**
     * Get Embed script for github files
     *
     * @param <type> $id
     * @param <type> $file
     * @return <type>
     */
    private function get_github_embed_script($file, $start_line = 0, $end_line = 0) {
        $script = '<script src = "' . self::GIST_IT_SERVER . $file;

        if ($start_line > 0) {
            $script .= ":$start_line";
        }

        if ($end_line > 0) {
            $script .= ":$end_line";
        }

        $script .= '"></script>';

        return $script;
    }

    /**
     * Get Embed script for gist
     *
     * @param <type> $id
     * @param <type> $file
     * @return <type>
     */
    private function get_gist_embed_script($id, $file = '') {
        $script_url = "https://gist.github.com/".trim($id).".js";

        if ($file != '') {
            $script_url .= "?file=".trim($file);
        }

        $script = $this->get_content_from_url($script_url);

        if ($script != '') {
            $script = "<script>".$script."</script>";
        }

        return $script;
    }

    /**
     * Get the raw content of the gist
     *
     * @param <type> $id
     * @param <type> $file
     * @return <type>
     */
    private function get_gist_raw($id, $file = '') {
        $url = "https://raw.github.com/gist/".$id;

        if ($file != '') {
            $url .= "/" . $file;
        }

        $gist_raw = $this->get_content_from_url($url);
        
        if ($gist_raw != '') {
            $gist_raw =  "<div style='margin-bottom:1em;padding:0;'><noscript><code><pre style='overflow:auto;margin:0;padding:0;border:1px solid #DDD;'>"
                            .htmlentities($gist_raw)
                          ."</pre></code></noscript></div>";
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

    // PHP4 compatibility
    function WPGithubGist() {
        $this->__construct();
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'WPGithubGist' ); function WPGithubGist() { global $WPGithubGist; $WPGithubGist = new WPGithubGist(); }

?>