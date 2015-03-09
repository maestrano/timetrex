<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class Maestrano
{
  // Maestrano PHP API Version
  const VERSION = '0.6.0';

  /* Internal Config Map */
  protected static $config = array();
  
  /**
   * Check if the pair api_id/api_key is valid
   * for authentication purpose
   * @return whether the pair is valid or not
   */
  public static function authenticate($api_id, $api_key) {
    return !is_null($api_id) && !is_null($api_key) && 
      self::param('api.id') == $api_id && self::param('api.key') == $api_key;
  }
  
  /**
  * Configure Maestrano API from array or file (string path)
  *
  * @return true
  */
  public static function configure($settings) {
    if (is_string($settings)) {
      return self::configure(json_decode(file_get_contents($settings),true));
    }

    //-------------------------------
    // App Config
    //-------------------------------
    if (array_key_exists('environment', $settings)) {
      self::$config['environment'] = $settings['environment'];
    } else {
      self::$config['environment'] = 'test';
    }
    
    if (array_key_exists('app', $settings) && array_key_exists('host', $settings['app'])) {
      self::$config['app.host'] = $settings['app']['host'];
    } else {
      self::$config['app.host'] = 'http://localhost:8888';
    }
    
    //-------------------------------
    // API Config
    //-------------------------------
    if (array_key_exists('api', $settings) && array_key_exists('id', $settings['api'])) {
      self::$config['api.id'] = $settings['api']['id'];
    }
    
    if (array_key_exists('api', $settings) && array_key_exists('key', $settings['api'])) {
      self::$config['api.key'] = $settings['api']['key'];
    }

    if (array_key_exists('api', $settings) && array_key_exists('group_id', $settings['api'])) {
      self::$config['api.group_id'] = $settings['api']['group_id'];
    }
    
    // Get lang/platform version
    self::$config['api.version'] = Maestrano::VERSION;
    self::$config['api.lang'] = 'php';
    self::$config['api.lang_version'] = phpversion() . " " . php_uname();
    
    // Build api.token from api.id and api.key
    self::$config['api.token'] = self::$config['api.id'] . ":" . self::$config['api.key'];
    
    //-------------------------------
    // SSO Config
    //-------------------------------
    if (array_key_exists('sso', $settings) && array_key_exists('enabled', $settings['sso'])) {
      self::$config['sso.enabled'] = $settings['sso']['enabled'];
    } else {
      self::$config['sso.enabled'] = true;
    }
    
    if (array_key_exists('sso', $settings) && array_key_exists('slo_enabled', $settings['sso'])) {
      self::$config['sso.slo_enabled'] = $settings['sso']['slo_enabled'];
    } else {
      self::$config['sso.slo_enabled'] = true;
    }

    if (array_key_exists('sso', $settings) && array_key_exists('idm', $settings['sso'])) {
      self::$config['sso.idm'] = $settings['sso']['idm'];
    }

    if (array_key_exists('sso', $settings) && array_key_exists('idp', $settings['sso'])) {
      self::$config['sso.idp'] = $settings['sso']['idp'];
    }
    
    if (array_key_exists('sso', $settings) && array_key_exists('init_path', $settings['sso'])) {
      self::$config['sso.init_path'] = $settings['sso']['init_path'];
    } else {
      self::$config['sso.init_path'] = '/maestrano/auth/saml/init.php';
    }
    
    if (array_key_exists('sso', $settings) && array_key_exists('consume_path', $settings['sso'])) {
      self::$config['sso.consume_path'] = $settings['sso']['consume_path'];
    } else {
      self::$config['sso.consume_path'] = '/maestrano/auth/saml/consume.php';
    }
    
    if (array_key_exists('sso', $settings) && array_key_exists('creation_mode', $settings['sso'])) {
      self::$config['sso.creation_mode'] = $settings['sso']['creation_mode'];
    } else {
      self::$config['sso.creation_mode'] = 'real';
    }

    //-------------------------------
    // Connec! Config
    //-------------------------------
    if (array_key_exists('connec', $settings) && array_key_exists('enabled', $settings['connec'])) {
      self::$config['connec.enabled'] = $settings['connec']['enabled'];
    } else {
      self::$config['connec.enabled'] = true;
    }

    if (array_key_exists('connec', $settings) && array_key_exists('host', $settings['connec'])) {
      self::$config['connec.host'] = $settings['connec']['host'];
    }
    
    if (array_key_exists('connec', $settings) && array_key_exists('base_path', $settings['connec'])) {
      self::$config['connec.base_path'] = $settings['connec']['base_path'];
    }

    //-------------------------------
    // Webhook Config - Account
    //-------------------------------
    if (array_key_exists('webhook', $settings) 
      && array_key_exists('account', $settings['webhook'])
      && array_key_exists('groups_path', $settings['webhook']['account'])) {
      self::$config['webhook.account.groups_path'] = $settings['webhook']['account']['groups_path'];
    } else {
      self::$config['webhook.account.groups_path'] = '/maestrano/account/groups/:id';
    }
    
    if (array_key_exists('webhook', $settings) 
      && array_key_exists('account', $settings['webhook'])
      && array_key_exists('group_users_path', $settings['webhook']['account'])) {
      self::$config['webhook.account.group_users_path'] = $settings['webhook']['account']['group_users_path'];
    } else {
      self::$config['webhook.account.group_users_path'] = '/maestrano/account/groups/:group_id/users/:id';
    }
    
    //-------------------------------
    // Webhook Config - Connec
    //-------------------------------
    if (array_key_exists('webhook', $settings) 
      && array_key_exists('connec', $settings['webhook'])
      && array_key_exists('initialization_path', $settings['webhook']['connec'])) {
      self::$config['webhook.connec.initialization_path'] = $settings['webhook']['connec']['initialization_path'];
    } else {
      self::$config['webhook.connec.initialization_path'] = '/maestrano/connec/initialization';
    }

    if (array_key_exists('webhook', $settings) 
      && array_key_exists('connec', $settings['webhook'])
      && array_key_exists('notifications_path', $settings['webhook']['connec'])) {
      self::$config['webhook.connec.notifications_path'] = $settings['webhook']['connec']['notifications_path'];
    } else {
      self::$config['webhook.connec.notifications_path'] = '/maestrano/connec/notifications';
    }
    
    if (array_key_exists('webhook', $settings) 
      && array_key_exists('connec', $settings['webhook'])
      && array_key_exists('subscriptions', $settings['webhook']['connec'])) {
      self::$config['webhook.connec.subscriptions'] = $settings['webhook']['connec']['subscriptions'];
    } else {
      self::$config['webhook.connec.subscriptions'] = array();
    }
    
    
    // Not in use for now
    // Check SSL certificate on API requests
    if (array_key_exists('verify_ssl_certs', $settings)) {
      self::$config['verify_ssl_certs'] = $settings['verify_ssl_certs'];
    } else {
      self::$config['verify_ssl_certs'] = false;
    }
    
    return true;
  }
  
   
   /**
    * Return a configuration parameter
    */
   public static function param($parameter) {
     if (array_key_exists($parameter, self::$config)) {
       return self::$config[$parameter];
     } else if (array_key_exists($parameter, self::$EVT_CONFIG[self::$config['environment']])) {
       return self::$EVT_CONFIG[self::$config['environment']][$parameter];
     }
     
     return null;
   }
   
   /**
    * Return the SSO service
    * 
    * @return Maestrano_Sso_Service singleton
    */
   public static function sso() {
     return Maestrano_Sso_Service::instance();
   }
   
   /**
    * Return a json string describing the configuration
    * currently used by the PHP bindings
    */
   public static function toMetadata() {
     $config = array(
       'environment'        => Maestrano::param('environment'),
       'app' => array(
         'host'             => Maestrano::param('app.host')
       ),
       'api' => array(
         'id'               => Maestrano::param('api.id'),
         'version'          => Maestrano::param('api.version'),
         'verify_ssl_certs' => false,
         'lang'             => Maestrano::param('api.lang'),
         'lang_version'     => Maestrano::param('api.lang_version'),
         'host'             => Maestrano::param('api.host'),
         'base'             => Maestrano::param('api.base')
       ),
       'sso' => array(
         'enabled'          => Maestrano::param('sso.enabled'),
         'slo_enabled'      => Maestrano::param('sso.slo_enabled'),
         'init_path'        => Maestrano::param('sso.init_path'),
         'consume_path'     => Maestrano::param('sso.consume_path'),
         'creation_mode'    => Maestrano::param('sso.creation_mode'),
         'idm'              => Maestrano::param('sso.idm'),
         'idp'              => Maestrano::param('sso.idp'),
         'name_id_format'   => Maestrano::param('sso.name_id_format'),
         'x509_fingerprint' => Maestrano::param('sso.x509_fingerprint'),
         'x509_certificate' => Maestrano::param('sso.x509_certificate')
       ),
       'connec' => array(
         'enabled'          => Maestrano::param('connec.enabled'),
         'host'             => Maestrano::param('connec.host'),
         'base_path'        => Maestrano::param('connec.base_path')
       ),
       'webhook' => array(
         'account' => array(
           'groups_path' => Maestrano::param('webhook.account.groups_path'),
           'group_users_path' => Maestrano::param('webhook.account.group_users_path')
         ),
         'connec' => array(
           'initialization_path' => Maestrano::param('webhook.connec.initialization_path'),
           'notifications_path' => Maestrano::param('webhook.connec.notifications_path'),
           'subscriptions' => Maestrano::param('webhook.connec.subscriptions')
         )
       )
     );
     
     return json_encode($config);
   }
  
  
    /* 
    * Environment related configuration 
    */
    public static $EVT_CONFIG = array(
    'local' => array(
      'api.host'               => 'http://application.maestrano.io',
      'api.base'               => '/api/v1/',
      'connec.enabled'         => true,
      'connec.host'            => 'http://connec.maestrano.io',
      'connec.base_path'       => '/api/v2',
      'sso.idp'                => 'http://application.maestrano.io',
      'sso.name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso.x509_fingerprint'   => '01:06:15:89:25:7d:78:12:28:a6:69:c7:de:63:ed:74:21:f9:f5:36',
      'sso.x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5\nWhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu\nP2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I\n5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2\n28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo\neHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ\njYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc\nMPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc\n2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES\nRkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==\n-----END CERTIFICATE-----"
    ),
    'test' => array(
      'api.host'               => 'http://api-sandbox.maestrano.io',
      'api.base'               => '/api/v1/',
      'connec.enabled'         => true,
      'connec.host'            => 'http://api-sandbox.maestrano.io',
      'connec.base_path'       => '/connec/api/v2',
      'sso.idp'                => 'http://api-sandbox.maestrano.io',
      'sso.name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso.x509_fingerprint'   => '01:06:15:89:25:7d:78:12:28:a6:69:c7:de:63:ed:74:21:f9:f5:36',
      'sso.x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5\nWhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu\nP2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I\n5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2\n28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo\neHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ\njYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc\nMPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc\n2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES\nRkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==\n-----END CERTIFICATE-----"
    ),
    'uat' => array(
      'api.host'               => 'https://uat.maestrano.io',
      'api.base'               => '/api/v1/',
      'connec.enabled'         => true,
      'connec.host'            => 'http://api-connec.internal.apse1.uat.maestrano.io',
      'connec.base_path'       => '/api/v2',
      'sso.idp'                => 'https://uat.maestrano.io',
      'sso.name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso.x509_fingerprint'   => '8a:1e:2e:76:c4:67:80:68:6c:81:18:f7:d3:29:5d:77:f8:79:54:2f',
      'sso.x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAMzy+weDPp7qMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMzE0\nWhcNMzMxMjMwMDUyMzE0WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+2uyQeAOc/iro\nhCyT33RkkWfTGeJ8E/mu9F5ORWoCZ/h2J+QDuzuc69Rf1LoO4wZVQ8LBeWOqMBYz\notYFUIPlPfIBXDNL/stHkpg28WLDpoJM+46WpTAgp89YKgwdAoYODHiUOcO/uXOO\n2i9Ekoa+kxbvBzDJf7uuR/io6GERXwIDAQABo4HuMIHrMB0GA1UdDgQWBBTGRDBT\nie5+fHkB0+SZ5g3WY/D2RTCBuwYDVR0jBIGzMIGwgBTGRDBTie5+fHkB0+SZ5g3W\nY/D2RaGBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkAzPL7B4M+nuowDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQAw\nRxg3rZrML//xbsS3FFXguzXiiNQAvA4KrMWhGh3jVrtzAlN1/okFNy6zuN8gzdKD\nYw2n0c/u3cSpUutIVZOkwQuPCMC1hoP7Ilat6icVewNcHayLBxKgRxpBhr5Sc4av\n3HOW5Bi/eyC7IjeBTbTnpziApEC7uUsBou2rlKmTGw==\n-----END CERTIFICATE-----"
    ),
    'production' => array(
      'api.host'               => 'https://maestrano.com',
      'api.base'               => '/api/v1/',
      'connec.enabled'         => true,
      'connec.host'            => 'https://api-connec.maestrano.com',
      'connec.base_path'       => '/api/v2',
      'sso.idp'                => 'https://maestrano.com',
      'sso.name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso.x509_fingerprint'   => '2f:57:71:e4:40:19:57:37:a6:2c:f0:c5:82:52:2f:2e:41:b7:9d:7e',
      'sso.x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAPFpcH2rW0pyMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyNDEw\nWhcNMzMxMjMwMDUyNDEwWjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD3feNNn2xfEz5/\nQvkBIu2keh9NNhobpre8U4r1qC7h7OeInTldmxGL4cLHw4ZAqKbJVrlFWqNevM5V\nZBkDe4mjuVkK6rYK1ZK7eVk59BicRksVKRmdhXbANk/C5sESUsQv1wLZyrF5Iq8m\na9Oy4oYrIsEF2uHzCouTKM5n+O4DkwIDAQABo4HuMIHrMB0GA1UdDgQWBBSd/X0L\n/Pq+ZkHvItMtLnxMCAMdhjCBuwYDVR0jBIGzMIGwgBSd/X0L/Pq+ZkHvItMtLnxM\nCAMdhqGBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA8WlwfatbSnIwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQDE\nhe/18oRh8EqIhOl0bPk6BG49AkjhZZezrRJkCFp4dZxaBjwZTddwo8O5KHwkFGdy\nyLiPV326dtvXoKa9RFJvoJiSTQLEn5mO1NzWYnBMLtrDWojOe6Ltvn3x0HVo/iHh\nJShjAn6ZYX43Tjl1YXDd1H9O+7/VgEWAQQ32v8p5lA==\n-----END CERTIFICATE-----"
    )
    );
}