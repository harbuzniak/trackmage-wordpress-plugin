<?php
/**
 * The Admin class.
 *
 * Initialize and render the settings page.
 *
 * @package TrackMage\WordPress\Admin
 * @author  TrackMage
 */

namespace TrackMage\WordPress\Admin;

use TrackMage\WordPress\Plugin;
use TrackMage\WordPress\Helper;
use TrackMage\Client\Swagger\ApiException;

/**
 * The Admin class.
 *
 * @since 0.1.0
 */
class Admin {

    /**
     * The constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_page']);
        add_action('admin_init', [$this, 'settings']);
        add_action('wp_ajax_trackmage_test_credentials', [$this, 'test_credentials']);
        add_filter('pre_update_option_trackmage_workspace', [$this, 'select_workspace'], 10, 3);
    }

    /**
     * Registers setting pages.
     *
     * @since 0.1.0
     */
    public function add_page() {
        add_menu_page(
            __('TrackMage', 'trackmage'),
            __('TrackMage', 'trackmage'),
            'manage_options',
            'trackmage-settings',
            '',
            TRACKMAGE_URL . 'assets/dist/images/trackmage-icon-white-16x16.png',
            30
        );

        add_submenu_page(
            'trackmage-settings',
            __('Settings', 'trackmage'),
            __('Settings', 'trackmage'),
            'manage_options',
            'trackmage-settings',
            [$this, 'renderSettings']
        );

        add_submenu_page(
            'trackmage-settings',
            __('Status Manager', 'trackmage'),
            __('Status Manager', 'trackmage'),
            'manage_options',
            'trackmage-status-manager',
            [$this, 'renderStatusManager']
        );
    }

    /**
     * Registers the general setting fields.
     *
     * @since 0.1.0
     */
    public function settings() {
        // General settings.
        register_setting('trackmage_general', 'trackmage_client_id');
        register_setting('trackmage_general', 'trackmage_client_secret');
        register_setting('trackmage_general', 'trackmage_workspace');

        // Statuses settings.
        register_setting('trackmage_general', 'trackmage_sync_statuses');
    }

    /**
     * Renders settings page.
     *
     * @since 1.0.0
     */
    public function renderSettings() {
        require_once TRACKMAGE_VIEWS_DIR . 'admin-page-settings.php';
    }

    /**
     * Renders status manager page.
     *
     * @since 1.0.0
     */
    public function renderStatusManager() {
        require_once TRACKMAGE_VIEWS_DIR . 'admin-page-status-manager.php';
    }

    /**
     * Tests API keys.
     *
     * @since 0.1.0
     */
    public function test_credentials() {
        $credentials = Helper::check_credentials($_POST['clientId'], $_POST['clientSecret']);

        if (Helper::CREDENTIALS_INVALID === $credentials) {
            wp_send_json_error([
                'status' => 'error',
                'errors' => [
                    __('Invalid credentials.', 'trackmage'),
                ]
            ]);
        }

        if (Helper::CREDENTIALS_VALID === $credentials) {
            wp_send_json_success([
                'status' => 'success',
            ]);
        }

        if (Helper::CREDENTIALS_ERROR === $credentials) {
            wp_send_json_error([
                'status' => 'error',
                'errors' => [
                    __('We could not peform the check. Please try again.', 'trackmage'),
                ]
            ]);
        }
    }

    /**
     * Add/remove webhooks based on the selected workspace.
     *
     * @since 0.1.0
     */
    public function select_workspace($value, $old_value, $option) {
        // Exit if value has not changed.
        if ($value === $old_value) {
            return $old_value;
        }

        $client = Plugin::get_client();
        $url = Helper::get_endpoint();

        // Find and remove any activated webhook, if any.
        $webhook = get_option('trackmage_webhook', '');
        if (! empty($webhook)) {
            try {
                $client->getWorkflowApi()->deleteWorkflowItem($webhook);
                update_option('trackmage_webhook', '');
            } catch (ApiException $e) {
                // Do nothing. Webhook might be removed from TrackMage.
            }
        }

        // Stop here if no workspace is selected.
        if (empty($value)) {
            return 0;
        }

        // Generate random username and password.
        $username = wp_get_current_user()->user_login . '_' . substr(md5(time() . rand(0, 1970)), 0, 5);
        $password = md5($username . rand(1, 337));
        update_option('trackmage_webhook_username', $username);
        update_option('trackmage_webhook_password', $password);

        $workflow = [
            'type' => 'webhook',
            'period' => 'immediately',
            'title' => get_bloginfo('name'),
            'workspace' => '/workspaces/' . $value,
            'url' => $url,
            'authType' => 'basic',
            'username' => $username,
            'password' => $password,
            'enabled' => true,
        ];

        try {
            $response = $client->getGuzzleClient()->post('/workflows', ['json' => $workflow]);
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents, true);
        } catch (ApiException $e) {
            // Trigger error message and exit.
            return $old_value;
        }

        update_option( 'trackmage_order_status_aliases', [] );
        update_option('trackmage_webhook', $data['id']);
        return $value;
    }
}
