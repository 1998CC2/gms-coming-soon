<?php
/**
 * Plugin Name: GMS - Coming Soon
 * Description: Coming-Soon-Plugin mit Ein/Aus-Schalter, editierbarer Überschrift, Text und Hintergrundbild.
 * Version: 2.0.3
 * Author: GMS - Gerber Multimedia Solutions
 * Author URI: https://gms.nrw
 * Plugin URI: https://gms.nrw/plugins
 * Text Domain: gms-coming-soon
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMS_Coming_Soon' ) ) :

class GMS_Coming_Soon {
    private $option_name = 'gms_coming_soon_settings';
    private $strings = array();

    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'template_redirect', array( $this, 'maybe_show_coming_soon' ) );
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'gms-coming-soon',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    private function get_lang() {
        if ( function_exists( 'determine_locale' ) ) {
            $locale = determine_locale();
        } else {
            $locale = get_locale();
        }

        if ( empty( $locale ) ) {
            return 'en';
        }

        if ( strpos( $locale, 'de_' ) === 0 ) {
            return 'de';
        }

        if ( strpos( $locale, 'es_' ) === 0 ) {
            return 'es';
        }

        return 'en';
    }

    private function get_strings() {
        if ( ! empty( $this->strings ) ) {
            return $this->strings;
        }

        $lang = $this->get_lang();

        $strings = array(
            'en' => array(
                'plugin_page_title'          => 'GMS Coming Soon',
                'menu_title'                 => 'Coming Soon',
                'section_title'              => 'Coming Soon Settings',
                'section_description'        => 'Enable the Coming Soon mode and adjust the content and background image.',
                'field_enabled_label'        => 'Enable Coming Soon mode',
                'field_headline_label'       => 'Headline',
                'field_message_label'        => 'Text',
                'field_background_label'     => 'Background image',
                'field_background_button'    => 'Select image',
                'submit_button'              => 'Save Changes',
                'default_headline'           => 'Coming Soon',
                'default_message'            => 'Our website is currently under construction. Please check back soon!',
            ),
            'de' => array(
                'plugin_page_title'          => 'GMS Coming Soon',
                'menu_title'                 => 'Coming Soon',
                'section_title'              => 'Coming-Soon-Einstellungen',
                'section_description'        => 'Aktiviere den Coming-Soon-Modus und passe Inhalt und Hintergrundbild an.',
                'field_enabled_label'        => 'Coming-Soon-Modus aktivieren',
                'field_headline_label'       => 'Überschrift',
                'field_message_label'        => 'Text',
                'field_background_label'     => 'Hintergrundbild',
                'field_background_button'    => 'Bild auswählen',
                'submit_button'              => 'Änderungen speichern',
                'default_headline'           => 'Coming Soon',
                'default_message'            => 'Unsere Website befindet sich derzeit im Aufbau. Bitte schau bald wieder vorbei!',
            ),
            'es' => array(
                'plugin_page_title'          => 'GMS Coming Soon',
                'menu_title'                 => 'Coming Soon',
                'section_title'              => 'Ajustes de Coming Soon',
                'section_description'        => 'Activa el modo Coming Soon y ajusta el contenido y la imagen de fondo.',
                'field_enabled_label'        => 'Activar modo Coming Soon',
                'field_headline_label'       => 'Titulo',
                'field_message_label'        => 'Texto',
                'field_background_label'     => 'Imagen de fondo',
                'field_background_button'    => 'Seleccionar imagen',
                'submit_button'              => 'Guardar cambios',
                'default_headline'           => 'Proximamente',
                'default_message'            => 'Nuestro sitio web esta actualmente en construccion. Vuelve a visitarnos pronto!',
            ),
        );

        if ( ! isset( $strings[ $lang ] ) ) {
            $lang = 'en';
        }

        $this->strings = $strings[ $lang ];
        return $this->strings;
    }

    public function activate() {
        $strings = $this->get_strings();

        $defaults = array(
            'enabled'          => 0,
            'headline'         => isset( $strings['default_headline'] ) ? $strings['default_headline'] : 'Coming Soon',
            'message'          => isset( $strings['default_message'] ) ? $strings['default_message'] : 'Our website is currently under construction. Please check back soon!',
            'background_image' => '',
        );

        $current = get_option( $this->option_name, array() );
        $merged  = wp_parse_args( $current, $defaults );

        update_option( $this->option_name, $merged );
    }

    public function add_admin_menu() {
        $strings = $this->get_strings();

        add_options_page(
            esc_html( $strings['plugin_page_title'] ),
            esc_html( $strings['menu_title'] ),
            'manage_options',
            'gms-coming-soon',
            array( $this, 'options_page' )
        );
    }

    public function settings_init() {
        $strings = $this->get_strings();

        register_setting(
            'gms_coming_soon_group',
            $this->option_name,
            array( $this, 'sanitize_settings' )
        );

        add_settings_section(
            'gms_coming_soon_section_main',
            esc_html( $strings['section_title'] ),
            function () use ( $strings ) {
                echo '<p>' . esc_html( $strings['section_description'] ) . '</p>';
            },
            'gms-coming-soon'
        );

        add_settings_field(
            'enabled',
            esc_html( $strings['field_enabled_label'] ),
            array( $this, 'field_enabled_render' ),
            'gms-coming-soon',
            'gms_coming_soon_section_main'
        );

        add_settings_field(
            'headline',
            esc_html( $strings['field_headline_label'] ),
            array( $this, 'field_headline_render' ),
            'gms-coming-soon',
            'gms_coming_soon_section_main'
        );

        add_settings_field(
            'message',
            esc_html( $strings['field_message_label'] ),
            array( $this, 'field_message_render' ),
            'gms-coming-soon',
            'gms_coming_soon_section_main'
        );

        add_settings_field(
            'background_image',
            esc_html( $strings['field_background_label'] ),
            array( $this, 'field_background_image_render' ),
            'gms-coming-soon',
            'gms_coming_soon_section_main'
        );
    }

    public function sanitize_settings( $input ) {
        $output = array();
        $output['enabled'] = isset( $input['enabled'] ) ? 1 : 0;
        $output['headline'] = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';
        $output['message'] = isset( $input['message'] ) ? wp_kses_post( $input['message'] ) : '';
        $output['background_image'] = isset( $input['background_image'] ) ? esc_url_raw( $input['background_image'] ) : '';
        return $output;
    }

    public function field_enabled_render() {
        $options = get_option( $this->option_name, array() );
        $strings = $this->get_strings();
        $enabled = isset( $options['enabled'] ) ? (int) $options['enabled'] : 0;
        ?>
        <label>
            <input type="checkbox"
                   name="<?php echo esc_attr( $this->option_name ); ?>[enabled]"
                   value="1" <?php checked( 1, $enabled ); ?> />
            <?php echo esc_html( $strings['field_enabled_label'] ); ?>
        </label>
        <?php
    }

    public function field_headline_render() {
        $options = get_option( $this->option_name, array() );
        $strings = $this->get_strings();
        $headline = isset( $options['headline'] ) ? $options['headline'] : $strings['default_headline'];
        ?>
        <input type="text"
               name="<?php echo esc_attr( $this->option_name ); ?>[headline]"
               value="<?php echo esc_attr( $headline ); ?>"
               class="regular-text"
               style="width: 100%; max-width: 400px;" />
        <?php
    }

    public function field_message_render() {
        $options = get_option( $this->option_name, array() );
        $strings = $this->get_strings();
        $message = isset( $options['message'] ) ? $options['message'] : $strings['default_message'];
        ?>
        <textarea name="<?php echo esc_attr( $this->option_name ); ?>[message]"
                  rows="5"
                  style="width: 100%; max-width: 400px;"><?php echo esc_textarea( $message ); ?></textarea>
        <?php
    }

    public function field_background_image_render() {
        $options = get_option( $this->option_name, array() );
        $strings = $this->get_strings();
        $image = isset( $options['background_image'] ) ? esc_url( $options['background_image'] ) : '';
        ?>
        <div style="max-width: 400px;">
            <input type="text"
                   id="gms_cs_background_image"
                   name="<?php echo esc_attr( $this->option_name ); ?>[background_image]"
                   value="<?php echo $image; ?>"
                   style="width: 100%; margin-bottom: 8px;" />
            <button type="button" class="button" id="gms_cs_background_image_button">
                <?php echo esc_html( $strings['field_background_button'] ); ?>
            </button>
        </div>
        <?php
    }

    public function admin_scripts( $hook ) {
        if ( 'settings_page_gms-coming-soon' !== $hook ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'gms-coming-soon-admin',
            plugin_dir_url( __FILE__ ) . 'js/gms-coming-soon-admin.js',
            array( 'jquery' ),
            '2.0.3',
            true
        );
    }

    public function maybe_show_coming_soon() {
        if ( is_admin() ) {
            return;
        }

        $options = get_option( $this->option_name, array() );
        $strings = $this->get_strings();
        $enabled = isset( $options['enabled'] ) ? (bool) $options['enabled'] : false;

        if ( ! $enabled ) {
            return;
        }

        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            return;
        }

        $headline = isset( $options['headline'] ) && '' !== trim( $options['headline'] )
            ? $options['headline']
            : $strings['default_headline'];

        $message = isset( $options['message'] ) && '' !== trim( $options['message'] )
            ? $options['message']
            : $strings['default_message'];

        $background_image = isset( $options['background_image'] ) ? $options['background_image'] : '';

        status_header( 503 );
        nocache_headers();
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title><?php echo esc_html( $headline ); ?></title>
            <?php wp_head(); ?>
            <style>
                html, body {
                    height: 100%;
                    margin: 0;
                    padding: 0;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    <?php if ( ! empty( $background_image ) ) : ?>
                    background: url('<?php echo esc_url( $background_image ); ?>') no-repeat center center fixed;
                    background-size: cover;
                    <?php else : ?>
                    background: #111;
                    <?php endif; ?>
                }
                .gms-cs-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    color: #fff;
                    padding: 20px;
                    box-sizing: border-box;
                }
                .gms-cs-content {
                    max-width: 600px;
                }
                .gms-cs-content h1 {
                    margin: 0 0 20px;
                    font-size: 2.5rem;
                }
                .gms-cs-content p {
                    margin: 0;
                    font-size: 1.1rem;
                    line-height: 1.6;
                }
            </style>
        </head>
        <body <?php body_class(); ?>>
            <div class="gms-cs-overlay">
                <div class="gms-cs-content">
                    <h1><?php echo esc_html( $headline ); ?></h1>
                    <p><?php echo wp_kses_post( nl2br( $message ) ); ?></p>
                </div>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }

    public function options_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $strings = $this->get_strings();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $strings['plugin_page_title'] ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'gms_coming_soon_group' );
                do_settings_sections( 'gms-coming-soon' );
                submit_button( esc_html( $strings['submit_button'] ) );
                ?>
            </form>
        </div>
        <?php
    }
}

endif;

new GMS_Coming_Soon();
