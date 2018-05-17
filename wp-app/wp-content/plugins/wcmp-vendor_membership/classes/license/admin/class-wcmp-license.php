<?php

class WCMp_License {

    private $current_tab;

    public function __construct($tab) {
        global $WCMp, $WCMP_Vendor_Membership, $GLOBALS;

        $this->current_tab = $tab;

        add_action('admin_init', array($this, 'license_page_init'));
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'), 50);

        add_menu_page(
                __('WCMp License', 'wcmp-vendor_membership'), __('WCMp License', 'wcmp-vendor_membership'), 'manage_woocommerce', 'wcmp-license', array($this, 'create_wcmp_license_settings'), $WCMp->plugin_url . 'assets/images/dualcube.png'
        );
    }

    /**
     * Register and add settings
     */
    public function license_page_init() {
        do_action('befor_settings_page_init');

        // Register each tab settings
        foreach ($this->get_wcmp_license_tabs() as $tab => $name) :
            do_action("settings_page_{$tab}_tab_init", $tab);
        endforeach;

        do_action('after_settings_page_init');
    }

    function get_wcmp_license_tabs() {
        global $WCMp, $WCMP_Vendor_Membership;
        $tabs = apply_filters('wcmp_license_tabs', array(
        ));
        return $tabs;
    }

    function get_wcmp_license_tab_desc() {
        global $WCMp, $WCMP_Vendor_Membership;
        $tab_desc = apply_filters('wcmp_license_tabs_desc', array(
        ));
        return $tab_desc;
    }

    function wcmp_license_tabs($current = '') {
        global $WCMp, $WCMP_Vendor_Membership;
        if (isset($_GET['tab'])) :
            $current = $_GET['tab'];
        else:
            $current = $this->current_tab;
        endif;

        $links = array();
        foreach ($this->get_wcmp_license_tabs() as $tab => $name) :
            if ($tab == $current) :
                $links[] = "<a class='nav-tab nav-tab-active' href='?page=wcmp-license&tab=$tab'>$name</a>";
            else :
                $links[] = "<a class='nav-tab' href='?page=wcmp-license&tab=$tab'>$name</a>";
            endif;
        endforeach;
        echo '<div class="icon32" id="dualcube_menu_ico"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($links as $link)
            echo $link;
        echo '</h2>';

        foreach ($this->get_wcmp_license_tabs() as $tab => $name) :
            if ($tab == $current) :
                printf(__("<h2>%s License</h2>", 'wcmp-vendor_membership'), $name);
            endif;
        endforeach;

        $tab_desc = $this->get_wcmp_license_tab_desc();
        foreach ($this->get_wcmp_license_tabs() as $tabd => $named) :
            if ($tabd == $current && !empty($tab_desc[$tabd])) :
                printf(__("<h4 style='border-bottom: 1px solid rgb(215, 211, 211);padding-bottom: 21px;'>%s</h4>", 'wcmp-vendor_membership'), $tab_desc[$tabd]);
            endif;
        endforeach;
    }

    /**
     * Options page callback
     */
    public function create_wcmp_license_settings() {
        global $WCMp;
        ?>
        <div class="wrap">
        <?php $this->wcmp_license_tabs(); ?>
        <?php
        $tab = ( isset($_GET['tab']) ? $_GET['tab'] : 'wcmp_vendor_membership_license' );
        $this->options = get_option("wcmp_{$tab}_settings_name");
        //print_r($this->options);
        // This prints out all hidden setting errors
        settings_errors("wcmp_{$tab}_settings_name");
        ?>
            <form class='wcmp_vendors_settings' method="post" action="options.php">
            <?php
            // This prints out all hidden setting fields
            settings_fields("wcmp_{$tab}_settings_group");
            do_settings_sections("wcmp-{$tab}-settings-admin");
            submit_button();
            ?>
            </form>
        </div>
        <?php
        do_action('dualcube_admin_footer');
    }

    /**
     * Admin Scripts
     */
    public function enqueue_admin_script() {
        global $WCMp;
        $screen = get_current_screen();

        // Enqueue admin script and stylesheet from here
        if (in_array($screen->id, array('toplevel_page_wcmp-license'))) :
            wp_enqueue_style('wcmp_admin_css', $WCMp->plugin_url . 'assets/admin/css/admin.css', array(), $WCMp->version);
        endif;
    }

}
        