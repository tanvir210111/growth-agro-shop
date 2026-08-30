<?php
/**
 * Plugin Name: Creative Design Fraud Checker
 * Plugin URI: https://www.creativedesign.com.bd
 * Description: WooCommerce orders fraud checking using Creative Design API.
 * Version: 1.0.0
 * Author: Creative Design
 * Text Domain: cd-fraud-checker
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class CreativeDesignFraudChecker {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'woocommerce_admin_order_data_after_shipping_address', [ $this, 'display_fraud_status' ] );
        add_action( 'wp_ajax_cd_check_fraud', [ $this, 'ajax_check_fraud' ] );
    }

    // এডমিন মেনু তৈরি
    public function add_settings_page() {
        add_menu_page(
            'CD Fraud Settings',
            'CD Fraud Checker',
            'manage_options',
            'cd-fraud-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-shield-alt',
            56
        );
    }

    // CSS লোড করা
    public function enqueue_assets($hook) {
        if ( $hook !== 'toplevel_page_cd-fraud-settings' && $hook !== 'woocommerce_page_wc-orders' && $hook !== 'post.php' ) return;
        wp_enqueue_style( 'cd-admin-style', plugins_url( 'style.css', __FILE__ ) );
        wp_enqueue_script( 'jquery' );
    }

    // এপিআই কল ফাংশন
    public static function call_api($phone) {
        $api_key = get_option('cd_fraud_api_key', '');
        $url = 'https://www.creativedesign.com.bd/api/v1/check-fraud';

        $response = wp_remote_post($url, [
            'headers' => [
                'x-api-key'    => $api_key,
                'Content-Type' => 'application/json',
            ],
            'body'    => json_encode(['phone' => $phone]),
            'timeout' => 45,
        ]);

        if ( is_wp_error( $response ) ) return null;
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    // সেটিংস পেজ রেন্ডার করা
    public function render_settings_page() {
        if ( isset($_POST['save_cd_settings']) ) {
            update_option('cd_fraud_api_key', sanitize_text_field($_POST['cd_api_key']));
            echo '<div class="updated"><p>Settings Saved!</p></div>';
        }
        $api_key = get_option('cd_fraud_api_key', '');
        ?>
        <div class="wrap cd-page-wrapper">
            <div class="cd-header-card">
                <div class="cd-header-flex">
                    <div>
                        <h1>Creative Design</h1>
                        <p>আপনার API সেটিংস কনফিগার করুন নিরাপদে</p>
                    </div>
                    <a href="https://www.creativedesign.com.bd" target="_blank" class="cd-btn-outline">Visit Website</a>
                </div>
            </div>

            <div class="cd-row">
                <div class="cd-col-left">
                    <div class="cd-card">
                        <div class="cd-card-header">API Configuration</div>
                        <div class="cd-card-body">
                            <form method="post">
                                <div class="cd-form-group">
                                    <label>Creative Design API Key *</label>
                                    <input type="password" name="cd_api_key" value="<?php echo esc_attr($api_key); ?>" placeholder="Enter your API key here" required>
                                </div>
                                <button type="submit" name="save_cd_settings" class="cd-btn-save">Update Settings</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="cd-col-right">
                    <h3>কিভাবে API Key পাবেন?</h3>
                    <div class="cd-timeline">
                        <div class="cd-timeline-item">
                            <span class="num">১</span>
                            <p>CreativeDesign.com.bd এ লগইন করুন।</p>
                        </div>
                        <div class="cd-timeline-item">
                            <span class="num">২</span>
                            <p>API সেকশন থেকে আপনার ইউনিক <b>x-api-key</b> টি কপি করুন।</p>
                        </div>
                        <div class="cd-timeline-item">
                            <span class="num">৩</span>
                            <p>বামে দেওয়া ইনপুট বক্সে কি-টি পেস্ট করে সেভ করুন।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // অর্ডারের ভেতর ডেটা দেখানো
    public function display_fraud_status($order) {
        $phone = $order->get_billing_phone();
        if (!$phone) return;

        $data = self::call_api($phone);
        
        if ($data && $data['status'] === 'success') {
            $is_fraud = $data['is_fraud'];
            $class = $is_fraud ? 'cd-fraud-alert' : 'cd-safe-alert';
            $text = $is_fraud ? '⚠️ Reported as Fraud!' : '✅ Safe Customer';
            ?>
            <div class="cd-status-box <?php echo $class; ?>">
                <h4><?php echo $text; ?></h4>
                <p><?php echo esc_html($data['message']); ?></p>
                <small>Remaining Limit: <?php echo esc_html($data['remaining_limit']); ?></small>
            </div>
            <?php
        }
    }
}
new CreativeDesignFraudChecker();