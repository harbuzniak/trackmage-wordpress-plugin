<?php
/**
 * Settings/General
 *
 * @package TrackMage\WordPress
 * @author  TrackMage
 */

use TrackMage\WordPress\Helper;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Fields.
$client_id     = get_option( 'trackmage_client_id', '' );
$client_secret = get_option( 'trackmage_client_secret', '' );
$workspace     = get_option( 'trackmage_workspace', 0 );

$workspaces = Helper::get_workspaces();
$credentials = Helper::check_credentials();
$statuses = Helper::getOrderStatuses();
$sync_statuses = get_option( 'trackmage_sync_statuses', [] );
?>

<div class="intro">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</div>

<form method="post" action="options.php">
    <?php settings_fields( 'trackmage_general' ); ?>
    <!-- Section: Credentials -->
    <div class="section">
        <h2 class="headline"><?php _e( 'Credentials', 'trackmage' ); ?></h2>
        <p class="message"><?php echo sprintf( __( 'If you have not created API keys yet, please <a href="%1$s" target="_blank">login</a> to TrackMage account and generate a new key for this website.', 'trackmage' ), 'https://app.test.trackmage.com/dashboard/user-profile/api-keys' ); ?></p>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="trackmage_client_id"><?php _e( 'Client ID', 'trackmage' ); ?></label></th>
                    <td><input name="trackmage_client_id" type="text" id="trackmage_client_id" value="<?php echo esc_attr( $client_id ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="trackmage_client_secret"><?php _e( 'Client Secret', 'trackmage' ); ?></label></th>
                    <td><input name="trackmage_client_secret" type="password" id="trackmage_client_secret" value="<?php echo esc_attr( $client_secret ); ?>" class="regular-text" /></td>
                </tr>
            </tbody>
        </table>
        <div class="test-credentials">
            <input id="testCredentials" type="button" class="button" value="<?php _e( 'Test Credentials', 'trackmage' ); ?>"/>
            <span class="spinner"></span>
        </div>
    </div>
    <!-- End Section: Credentials -->

    <!-- Section: Workspace -->
    <div class="section<?php Helper::add_css_class( ! $credentials, 'disabled', true, true ); ?>">
        <h2 class="headline"><?php _e( 'Workspace', 'trackmage' ); ?></h2>
        <p class="message"><?php echo sprintf( __( 'Please select a workspace in TrackMage.', 'trackmage' ) ); ?></p>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="trackmage_workspace"><?php _e( 'Workspace', 'trackmage' ); ?></label></th>
                    <td>
                        <select name="trackmage_workspace" id="trackmage_workspace">
                            <option value="0"><?php _e( '— Select —', 'trackmage' ); ?></option>
                            <?php foreach ( $workspaces as $ws ) : ?>
                                <option value="<?php echo $ws['id']; ?>" <?php selected( $ws['id'], $workspace ); ?>><?php echo $ws['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php echo sprintf( __( 'Select a workspace or <a href="%1$s">create a new one</a> in TrackMage.', 'trackmage'), 'https://app.test.trackmage.com/dashboard/workspaces' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- End Section: Workspace -->


    <!-- Section: Sync With TrackMage -->
    <div class="section<?php Helper::add_css_class( ! $credentials && !$workspace, 'disabled', true, true ); ?>">
        <h2 class="headline"><?php _e( 'Sync With TrackMage', 'trackmage' ); ?></h2>
        <p class="message"><?php echo sprintf( __( 'Please select orders statuses to sync with TrackMage.', 'trackmage' ) ); ?></p>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="trackmage_sync_statuses"><?php _e( 'Statuses', 'trackmage' ); ?></label></th>
                <td>
                    <select name="trackmage_sync_statuses[]" id="trackmage_sync_statuses" multiple>
                        <?php foreach ( $sync_statuses as $slug ): ?>
                            <?php if ( isset( $statuses[ $slug ] ) ) : ?>
                                <option value="<?php echo $slug; ?>" selected><?php echo $statuses[ $slug ]['name']; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e( 'Create an order on TrackMage when the status changes to. If none is selected, all new orders will be synced with TrackMage upon creation.', 'trackmage' ); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- End Section: Sync With TrackMage -->

    <p class="actions"><?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?></p>
</form>
