<?php
/**
 * Plugin Name: RRZE-Lightbox
 * Description: Responsive Lightbox mit Slideshow-Effekt.
 * Version: 1.0
 * Author: rvdforst
 * Author URI: http://blogs.fau.de/webworking/
 * License: GPLv2 or later
 */

/*
 * Based on WP jQuery Lightbox by Ulf Benjaminsson (http://wordpress.org/extend/plugins/wp-jquery-lightbox/)
 */

add_action( 'plugins_loaded', array( 'RRZE_Lightbox', 'init' ) );

register_activation_hook( __FILE__, array( 'RRZE_Lightbox', 'activation' ) );

class RRZE_Lightbox {

    const version = '1.0'; // Plugin-Version
    
    const option_name = '_rrze_lightbox';

    const version_option_name = '_rrze_lightbox_version';
    
    const textdomain = 'rrze-lightbox';
    
    const php_version = '5.3'; // Minimal erforderliche PHP-Version
    
    const wp_version = '3.5'; // Minimal erforderliche WordPress-Version
    
    public static function init() {
        
        load_plugin_textdomain( self::textdomain, false, sprintf( '%s/lang/', dirname( plugin_basename( __FILE__ ) ) ) );
        
        add_action( 'init', array( __CLASS__, 'update_version' ) );
             
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        
     }

    public static function activation() {
        self::version_compare();
        
        update_option( self::version_option_name , self::version );
    }
        
    public static function version_compare() {
        $error = '';
        
        if ( version_compare( PHP_VERSION, self::php_version, '<' ) ) {
            $error = sprintf( __( 'Ihre PHP-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die PHP-Version %s.', self::textdomain ), PHP_VERSION, self::php_version );
        }

        if ( version_compare( $GLOBALS['wp_version'], self::wp_version, '<' ) ) {
            $error = sprintf( __( 'Ihre Wordpress-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die Wordpress-Version %s.', self::textdomain ), $GLOBALS['wp_version'], self::wp_version );
        }

        if( ! empty( $error ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ), false, true );
            wp_die( $error );
        }
        
    }
    
    public static function update_version() {
		if( get_option( self::version_option_name, null) != self::version )
			update_option( self::version_option_name , self::version );
    }
    
    private static function get_options( $key = '' ) {
        $defaults = array(
            'fitToScreen'         => 1,
            'resizeSpeed'         => 0,
            'displayDownloadLink' => 1,
            'navbarOnTop'         => 0,
            'resizeCenter'        => 1,
            'marginSize'          => 0,
            'linkTarget'          => '_self',
            'slideshowSpeed'      => 4000,
            'help'                => __( 'Pfeiltasten oder P für vorheriges, N für nächstes und X/C/Esc zum schließen.', self::textdomain ),
            'prevLinkTitle'       => __( 'Vorheriges Bild', self::textdomain ),
            'nextLinkTitle'       => __( 'Nächstes Bild', self::textdomain ),
            'closeTitle'          => __( 'Bildergalerie schließen', self::textdomain ),
            'image'               => __( 'Bild ', self::textdomain ),
            'of'                  => __( ' von ', self::textdomain ),
            'download'            => __( '[Herunterladen]', self::textdomain ),
            'pause'               => __( '[Pause]', self::textdomain ),
            'play'                => __( '[Start Slideshow]', self::textdomain )
        );

        $options = (array) get_option( self::option_name );
        $options = wp_parse_args( $options, $defaults );
        $options = array_intersect_key( $options, $defaults );

        if( !empty( $key ) )
            return isset( $options[$key] ) ? $options[$key] : null;

        return $options;
    }
        
    public static function enqueue_scripts() {
        $locale = sprintf( '%s/lightbox.css', get_locale() );
        if( ! is_readable( sprintf( '%scss/%s', plugin_dir_path( __FILE__ ), get_locale(), $locale ) ) )
            $locale = 'lightbox.css';
        
        wp_register_style( 'jquery.lightbox', $src = sprintf( '%scss/%s', plugin_dir_url( __FILE__ ), $locale ), false, self::version );
        
        wp_register_script( 'jquery.touchwipe', sprintf( '%sjs/jquery.touchwipe.min.js', plugin_dir_url( __FILE__ ) ),  array('jquery'), self::version, true );	
        wp_register_script( 'jquery.lightbox', sprintf( '%sjs/jquery.lightbox.js', plugin_dir_url( __FILE__ ) ),  array('jquery'), self::version, true );
                
        wp_localize_script( 'jquery.lightbox', 'JQLBSettings', self::get_options() );        
                
        add_action( 'enqueue_lightbox', array( __CLASS__, 'enqueue_lightbox' ), 10, 0 );       
    }
    
    public static function enqueue_lightbox() {
        wp_enqueue_style( 'jquery.lightbox' );
        wp_enqueue_script( 'jquery.lightbox' );
        wp_enqueue_script( 'jquery.touchwipe' );
    }
        
}
