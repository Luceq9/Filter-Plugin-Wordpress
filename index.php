<?php
    /*
    plugin name: Filter Plugin
    author: Luceq
    description: this plugin filters out bad words from your content
    version: 1.0
    */

    if(!defined('ABSPATH')){
        die;
    }

    class FilterPlugin {
        function __construct(){
            add_action('admin_menu', array($this, 'add_menu'));
            add_action('admin_init', array($this, 'options_form'));
            if(get_option('filter') ){
                add_filter('the_content', array($this, 'filter_content'));
            }
        }

        function options_form(){
            add_settings_section('filter_settings', null, null, 'options');
            register_setting('replacementFields', 'replacementText');
            add_settings_field('replacementText', 'Nakładka która wyświetli się zamiast słów', array($this, 'filter_html'), 'options', 'filter_settings');
        }

        function filter_html(){
            ?>
            <input type="text" name="replacementText" id="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
            <?php
        }


        function filter_content($content){
            $badwords = explode(',', get_option('filter'));
            $wordsTrimmed = array_map('trim', $badwords);
            $content = str_ireplace($wordsTrimmed, esc_html(get_option('replacementText', '***')), $content);
            return $content;
        }

        function add_menu(){
            $main_page_hook = add_menu_page('Filter Plugin', 'Filter Plugin', 'manage_options', 'filter-plugin', array($this, 'menuHtml'), 'dashicons-filter', 110);
            add_submenu_page('filter-plugin', 'Filter Options', 'Filtrowane słowa', 'manage_options', 'filter-plugin', array($this, 'options_form'));
            add_submenu_page('filter-plugin', 'Filter Options', 'Wyświetlany tekst', 'manage_options', 'word-filter-options', array($this, 'submenuHtml'));
            add_action("load-{$main_page_hook}", array($this, 'load_css'));
        }

        function load_css(){
            wp_enqueue_style('filter-plugin', plugin_dir_url(__FILE__) . 'assets/style.css'); 
        }

        function handleForm(){
            if(wp_verify_nonce($_POST['ourNonce'], 'update-options') && current_user_can('manage_options')){
            update_option('filter', sanitize_text_field($_POST['filter'])); ?>
            <div class="updated">
                <p>Wtyczka została zaktualizowana</p>
            </div>
            <?php
            } else {
                ?>
                <div class="error">
                    <p>Error - wtyczka nie działa poprawnie</p>
                </div>
                <?php
            }
        }

        function menuHtml(){
            ?>
            <h1>Filter Plugin</h1>
            <?php 
                   if (isset($_POST['justsubmitted']) && $_POST['justsubmitted'] == 'true') {
                    $this->handleForm();
                }
                    ?>
            <h3>Wprowadź prosze po przecinku wyrazy które uważasz za zakazane na swojej stronie (łobuzie, tchórzu etc...)</h3>
                <div class="container">
                    <form method="POST">
                        <input type="hidden" name="justsubmitted" value="true" >
                        <?php wp_nonce_field('update-options', 'ourNonce'); ?>
                        <div>
                        <textarea name="filter" id="filter"  placeholder='Bad, words, dont, accept'><?php echo esc_textarea(get_option('filter')) ?></textarea>
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Zapisz zmiany">
                        </div>
                    </form>

                </div>
            <?php
        }

        
        function submenuHtml(){
            ?>
            <h1>Filter Plugin</h1>
            <h3>Wprowadź jaki tekst ma być widoczny zamiast zakazanych słów</h3>
            <form action="options.php" method="POST">
            <?php
            settings_errors();
            settings_fields('replacementFields');
            do_settings_sections('options');
            submit_button('Zapisz zmiany', 'primary', 'submit', true);
            ?>
            </form>

            <?php
        }
    }

    $FilterPlugin = new FilterPlugin();


?>