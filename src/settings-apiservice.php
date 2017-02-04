<?php

if (!defined('SUCURISCAN_INIT') || SUCURISCAN_INIT !== true) {
    if (!headers_sent()) {
        /* Report invalid access if possible. */
        header('HTTP/1.1 403 Forbidden');
    }
    exit(1);
}

/**
 * Read and parse the content of the API service settings template.
 *
 * @return string Parsed HTML code for the API service settings panel.
 */
function sucuriscan_settings_apiservice($nonce)
{
    $params = array();

    $params['SettingsSection.ApiStatus'] = sucuriscan_settings_apiservice_status($nonce);
    $params['SettingsSection.ApiProxy'] = sucuriscan_settings_apiservice_proxy($nonce);
    $params['SettingsSection.ApiSSL'] = sucuriscan_settings_apiservice_ssl($nonce);
    $params['SettingsSection.ApiHandler'] = sucuriscan_settings_apiservice_handler($nonce);
    $params['SettingsSection.ApiTimeout'] = sucuriscan_settings_apiservice_timeout($nonce);
    $params['SettingsSection.ApiProtocol'] = sucuriscan_settings_apiservice_https($nonce);

    return SucuriScanTemplate::getSection('settings-apiservice', $params);
}

function sucuriscan_settings_apiservice_status($nonce)
{
    $params = array();
    $params['ApiStatus.StatusNum'] = '1';
    $params['ApiStatus.Status'] = 'Enabled';
    $params['ApiStatus.SwitchText'] = 'Disable';
    $params['ApiStatus.SwitchValue'] = 'disable';
    $params['ApiStatus.SwitchCssClass'] = 'button-danger';
    $params['ApiStatus.WarningVisibility'] = 'visible';
    $params['ApiStatus.ErrorVisibility'] = 'hidden';

    if ($nonce) {
        // Enable or disable the API service communication.
        if ($api_service = SucuriScanRequest::post(':api_service', '(en|dis)able')) {
            $action_d = $api_service . 'd';
            $message = 'API service communication was <code>' . $action_d . '</code>';

            SucuriScanEvent::report_info_event($message);
            SucuriScanEvent::notify_event('plugin_change', $message);
            SucuriScanOption::update_option(':api_service', $action_d);
            SucuriScanInterface::info($message);
        }
    }

    $api_service = SucuriScanOption::get_option(':api_service');

    if ($api_service === 'disabled') {
        $params['ApiStatus.StatusNum'] = '0';
        $params['ApiStatus.Status'] = 'Disabled';
        $params['ApiStatus.SwitchText'] = 'Enable';
        $params['ApiStatus.SwitchValue'] = 'enable';
        $params['ApiStatus.SwitchCssClass'] = 'button-success';
        $params['ApiStatus.WarningVisibility'] = 'hidden';
        $params['ApiStatus.ErrorVisibility'] = 'visible';
    }

    return SucuriScanTemplate::getSection('settings-apiservice-status', $params);
}

function sucuriscan_settings_apiservice_proxy($nonce)
{
    $params = array(
        'APIProxy.Host' => 'no_proxy_host',
        'APIProxy.Port' => 'no_proxy_port',
        'APIProxy.Username' => 'no_proxy_username',
        'APIProxy.Password' => 'no_proxy_password',
        'APIProxy.PasswordType' => 'default',
        'APIProxy.PasswordText' => 'empty',
    );

    if (class_exists('WP_HTTP_Proxy')) {
        $wp_http_proxy = new WP_HTTP_Proxy();

        if ($wp_http_proxy->is_enabled()) {
            $proxy_host = SucuriScan::escape($wp_http_proxy->host());
            $proxy_port = SucuriScan::escape($wp_http_proxy->port());
            $proxy_username = SucuriScan::escape($wp_http_proxy->username());
            $proxy_password = SucuriScan::escape($wp_http_proxy->password());

            $params['APIProxy.Host'] = $proxy_host;
            $params['APIProxy.Port'] = $proxy_port;
            $params['APIProxy.Username'] = $proxy_username;
            $params['APIProxy.Password'] = $proxy_password;
            $params['APIProxy.PasswordType'] = 'info';
            $params['APIProxy.PasswordText'] = 'hidden';

        }
    }

    return SucuriScanTemplate::getSection('settings-apiservice-proxy', $params);
}

function sucuriscan_settings_apiservice_ssl($nonce)
{
    global $sucuriscan_verify_ssl_cert;

    $params = array(
        'VerifySSLCert' => 'Undefined',
        'VerifySSLCertCssClass' => 0,
        'VerifySSLCertOptions' => '',
    );

    // Update the configuration for the SSL certificate verification.
    if ($nonce) {
        $verify_ssl_cert = SucuriScanRequest::post(':verify_ssl_cert');

        if ($verify_ssl_cert) {
            if (array_key_exists($verify_ssl_cert, $sucuriscan_verify_ssl_cert)) {
                $message = 'SSL certificate verification for API calls set to <code>' . $verify_ssl_cert . '</code>';

                SucuriScanOption::update_option(':verify_ssl_cert', $verify_ssl_cert);
                SucuriScanEvent::report_warning_event($message);
                SucuriScanEvent::notify_event('plugin_change', $message);
                SucuriScanInterface::info($message);
            } else {
                SucuriScanInterface::error('Invalid value for the SSL certificate verification.');
            }
        }
    }

    $verify_ssl_cert = SucuriScanOption::get_option(':verify_ssl_cert');
    $params['VerifySSLCertOptions'] = SucuriScanTemplate::selectOptions(
        $sucuriscan_verify_ssl_cert,
        $verify_ssl_cert
    );

    if (array_key_exists($verify_ssl_cert, $sucuriscan_verify_ssl_cert)) {
        $params['VerifySSLCert'] = $sucuriscan_verify_ssl_cert[$verify_ssl_cert];

        if ($verify_ssl_cert === 'true') {
            $params['VerifySSLCertCssClass'] = 1;
        }
    }

    return SucuriScanTemplate::getSection('settings-apiservice-ssl', $params);
}

function sucuriscan_settings_apiservice_handler($nonce)
{
    global $sucuriscan_api_handlers;

    $params = array();

    // Update the configuration for the SSL certificate verification.
    if ($nonce) {
        $api_handler = SucuriScanRequest::post(':api_handler');

        if ($api_handler) {
            if (array_key_exists($api_handler, $sucuriscan_api_handlers)) {
                $message = 'API request handler set to <code>' . $api_handler . '</code>';

                SucuriScanOption::update_option(':api_handler', $api_handler);
                SucuriScanEvent::report_warning_event($message);
                SucuriScanEvent::notify_event('plugin_change', $message);
                SucuriScanInterface::info($message);
            } else {
                SucuriScanInterface::error('Invalid value for the API request handler.');
            }
        }
    }

    $api_handler = SucuriScanOption::get_option(':api_handler');
    $params['ApiHandlerOptions'] = SucuriScanTemplate::selectOptions(
        $sucuriscan_api_handlers,
        $api_handler
    );

    return SucuriScanTemplate::getSection('settings-apiservice-handler', $params);
}

function sucuriscan_settings_apiservice_timeout($nonce)
{
    $params = array();

    // Update the API request timeout.
    if ($nonce) {
        $timeout = (int) SucuriScanRequest::post(':request_timeout', '[0-9]+');

        if ($timeout > 0) {
            if ($timeout <= SUCURISCAN_MAX_REQUEST_TIMEOUT) {
                $message = 'API request timeout set to <code>' . $timeout . '</code> seconds.';

                SucuriScanOption::update_option(':request_timeout', $timeout);
                SucuriScanEvent::report_info_event($message);
                SucuriScanEvent::notify_event('plugin_change', $message);
                SucuriScanInterface::info($message);
            } else {
                SucuriScanInterface::error('API request timeout in seconds is too high.');
            }
        }
    }

    $params['MaxRequestTimeout'] = SUCURISCAN_MAX_REQUEST_TIMEOUT;
    $params['RequestTimeout'] = SucuriScanOption::get_option(':request_timeout') . ' seconds';

    return SucuriScanTemplate::getSection('settings-apiservice-timeout', $params);
}

function sucuriscan_settings_apiservice_https($nonce)
{
    $params = array();

    $params['ApiProtocol.StatusNum'] = '1';
    $params['ApiProtocol.Status'] = 'Enabled';
    $params['ApiProtocol.SwitchText'] = 'Disable';
    $params['ApiProtocol.SwitchValue'] = 'http';
    $params['ApiProtocol.SwitchCssClass'] = 'button-danger';
    $params['ApiProtocol.WarningVisibility'] = 'visible';
    $params['ApiProtocol.ErrorVisibility'] = 'hidden';
    $params['ApiProtocol.AffectedUrls'] = '';

    if ($nonce) {
        // Enable or disable the API service communication.
        if ($api_protocol = SucuriScanRequest::post(':api_protocol', 'http(s)?')) {
            $message = 'API communication protocol was set to <code>' . strtoupper($api_protocol) . '</code>';

            SucuriScanEvent::report_info_event($message);
            SucuriScanEvent::notify_event('plugin_change', $message);
            SucuriScanOption::update_option(':api_protocol', $api_protocol);
            SucuriScanInterface::info($message);
        }
    }

    $api_protocol = SucuriScanOption::get_option(':api_protocol');

    if ($api_protocol !== 'https') {
        $params['ApiProtocol.StatusNum'] = '0';
        $params['ApiProtocol.Status'] = 'Disabled';
        $params['ApiProtocol.SwitchText'] = 'Enable';
        $params['ApiProtocol.SwitchValue'] = 'https';
        $params['ApiProtocol.SwitchCssClass'] = 'button-success';
        $params['ApiProtocol.WarningVisibility'] = 'hidden';
        $params['ApiProtocol.ErrorVisibility'] = 'visible';
    }

    $counter = 0;
    $affected_urls = SucuriScanAPI::ambiguousApiUrls();

    foreach ($affected_urls as $unique => $url) {
        $counter++;
        $url = SucuriScanAPI::apiUrlProtocol($url, $api_protocol);
        $css_class = ($counter % 2 === 0) ? 'alternate' : '';
        $params['ApiProtocol.AffectedUrls'] .= SucuriScanTemplate::getSnippet(
            'settings-apiservice-protocol',
            array(
                'ApiProtocol.CssClass' => $css_class,
                'ApiProtocol.ID' => $unique,
                'ApiProtocol.URL' => $url,
            )
        );
    }

    return SucuriScanTemplate::getSection('settings-apiservice-protocol', $params);
}

function sucuriscan_settings_apiservice_https_ajax()
{
    if (SucuriScanRequest::post('form_action') == 'debug_api_call') {
        $unique = SucuriScanRequest::post('api_unique');
        $response = SucuriScanAPI::debugApiCall($unique);

        header('Content-Type: application/json');
        print(@json_encode($response));
        exit(0);
    }
}
