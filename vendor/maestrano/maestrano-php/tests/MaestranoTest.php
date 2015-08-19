<?php

class MaestranoTest extends PHPUnit_Framework_TestCase
{
    protected $config;

    protected function setUp()
    {
      $this->config = array(
        'environment' => 'production',
        'app' => array(
          'host' => "https://mysuperapp.com",
        ),
        'api' => array(
          'id' => "myappid",
          'key' => "myappkey",
          'group_id' => "mygroupid",
          'host' => 'https://someapihost.com'
        ),
        'sso' => array(
          'init_path' => "/mno/init_path.php",
          'consume_path' => "/mno/consume_path.php",
          'idp' => "https://mysuperidp.com",
          'idm' => "https://mysuperidm.com",
          'x509_fingerprint' => "some-x509_fingerprint",
          'x509_certificate' => "some-x509_certificate"
        ),
        'connec' => array(
          'enabled' => true,
          'host' => 'http://connec.maestrano.io',
          'base_path' => '/api',
          'v2_path' => '/v2',
          'reports_path' => '/reports'
        ),
        'webhook' => array(
          'account' => array(
            'groups_path' => "/mno/groups/:id",
            'group_users_path' => "/mno/groups/:group_id/users/:id"
          ),
          'connec' => array(
            'enabled' => true,
            'initialization_path' => "/mno/connec/initialization",
            'notifications_path' => "/mno/connec/notifications",
            'subscriptions' => array(
              'organizations' => true,
              'people' => true
            )
          )
        )
      );
    }

    public function testBindingConfiguration() {
      Maestrano::configure($this->config);

      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['api']['host'], Maestrano::param('api.host'));
      $this->assertEquals($this->config['api']['group_id'], Maestrano::param('api.group_id'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['sso']['idp'], Maestrano::param('sso.idp'));
      $this->assertEquals($this->config['sso']['x509_fingerprint'], Maestrano::param('sso.x509_fingerprint'));
      $this->assertEquals($this->config['sso']['x509_certificate'], Maestrano::param('sso.x509_certificate'));
      $this->assertEquals($this->config['connec']['enabled'], Maestrano::param('connec.enabled'));
      $this->assertEquals($this->config['connec']['host'], Maestrano::param('connec.host'));
      $this->assertEquals($this->config['connec']['base_path'], Maestrano::param('connec.base_path'));
      $this->assertEquals($this->config['connec']['v2_path'], Maestrano::param('connec.v2_path'));
      $this->assertEquals($this->config['connec']['reports_path'], Maestrano::param('connec.reports_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
      $this->assertEquals($this->config['webhook']['connec']['initialization_path'], Maestrano::param('webhook.connec.initialization_path'));
      $this->assertEquals($this->config['webhook']['connec']['notifications_path'], Maestrano::param('webhook.connec.notifications_path'));
      $this->assertEquals($this->config['webhook']['connec']['subscriptions'], Maestrano::param('webhook.connec.subscriptions'));
    }

    public function testBindingConfigurationBooleanViaJson() {
      $config = array('environment' => 'production', 'sso' => array('enabled' => false));
      Maestrano::configure(json_decode(json_encode($config),true));

      $this->assertFalse(Maestrano::param('sso.enabled'));
    }

    public function testConfigurationFromFile() {
      $path = "config.json";
      file_put_contents($path, json_encode($this->config));

      Maestrano::configure($path);
      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['api']['host'], Maestrano::param('api.host'));
      $this->assertEquals($this->config['api']['group_id'], Maestrano::param('api.group_id'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['sso']['idp'], Maestrano::param('sso.idp'));
      $this->assertEquals($this->config['sso']['x509_fingerprint'], Maestrano::param('sso.x509_fingerprint'));
      $this->assertEquals($this->config['sso']['x509_certificate'], Maestrano::param('sso.x509_certificate'));
      $this->assertEquals($this->config['connec']['enabled'], Maestrano::param('connec.enabled'));
      $this->assertEquals($this->config['connec']['host'], Maestrano::param('connec.host'));
      $this->assertEquals($this->config['connec']['base_path'], Maestrano::param('connec.base_path'));
      $this->assertEquals($this->config['connec']['v2_path'], Maestrano::param('connec.v2_path'));
      $this->assertEquals($this->config['connec']['reports_path'], Maestrano::param('connec.reports_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
      $this->assertEquals($this->config['webhook']['connec']['initialization_path'], Maestrano::param('webhook.connec.initialization_path'));
      $this->assertEquals($this->config['webhook']['connec']['notifications_path'], Maestrano::param('webhook.connec.notifications_path'));
      $this->assertEquals($this->config['webhook']['connec']['subscriptions'], Maestrano::param('webhook.connec.subscriptions'));

      unlink($path);
    }

    public function testAuthenticateWhenValid() {
      Maestrano::configure($this->config);

      $this->assertTrue(Maestrano::authenticate($this->config['api']['id'],$this->config['api']['key']));
    }

    public function testAuthenticateWhenInvalid() {
      Maestrano::configure($this->config);

      $this->assertFalse(Maestrano::authenticate($this->config['api']['id'] . "aaa",$this->config['api']['key']));
      $this->assertFalse(Maestrano::authenticate($this->config['api']['id'],$this->config['api']['key'] . "aaa"));
    }

    public function testBindingConfigurationWithPreset() {
      $preset = 'some-marketplace';
      Maestrano::with($preset)->configure($this->config);

      $this->assertEquals($this->config['environment'], Maestrano::with($preset)->param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::with($preset)->param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::with($preset)->param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::with($preset)->param('api.key'));
      $this->assertEquals($this->config['api']['group_id'], Maestrano::with($preset)->param('api.group_id'));
      $this->assertEquals($this->config['api']['host'], Maestrano::with($preset)->param('api.host'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::with($preset)->param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::with($preset)->param('sso.consume_path'));
      $this->assertEquals($this->config['sso']['idp'], Maestrano::with($preset)->param('sso.idp'));
      $this->assertEquals($this->config['sso']['x509_fingerprint'], Maestrano::with($preset)->param('sso.x509_fingerprint'));
      $this->assertEquals($this->config['sso']['x509_certificate'], Maestrano::with($preset)->param('sso.x509_certificate'));
      $this->assertEquals($this->config['connec']['enabled'], Maestrano::with($preset)->param('connec.enabled'));
      $this->assertEquals($this->config['connec']['host'], Maestrano::with($preset)->param('connec.host'));
      $this->assertEquals($this->config['connec']['base_path'], Maestrano::with($preset)->param('connec.base_path'));
      $this->assertEquals($this->config['connec']['v2_path'], Maestrano::with($preset)->param('connec.v2_path'));
      $this->assertEquals($this->config['connec']['reports_path'], Maestrano::with($preset)->param('connec.reports_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::with($preset)->param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::with($preset)->param('webhook.account.group_users_path'));
      $this->assertEquals($this->config['webhook']['connec']['initialization_path'], Maestrano::with($preset)->param('webhook.connec.initialization_path'));
      $this->assertEquals($this->config['webhook']['connec']['notifications_path'], Maestrano::with($preset)->param('webhook.connec.notifications_path'));
      $this->assertEquals($this->config['webhook']['connec']['subscriptions'], Maestrano::with($preset)->param('webhook.connec.subscriptions'));
    }

    public function testToMetadata() {
      Maestrano::configure($this->config);

      $expected = array(
        'environment'        => $this->config['environment'],
        'app' => array(
          'host'             => $this->config['app']['host']
        ),
        'api' => array(
          'id'               => $this->config['api']['id'],
          'version'          => Maestrano::VERSION,
          'verify_ssl_certs' => false,
          'lang'             => 'php',
          'lang_version'     => phpversion() . " " . php_uname(),
          'host'             => $this->config['api']['host'],
          'base'             => Maestrano::$EVT_CONFIG[$this->config['environment']]['api.base'],
        ),
        'sso' => array(
          'enabled'          => true,
          'slo_enabled'      => true,
          'init_path'        => $this->config['sso']['init_path'],
          'consume_path'     => $this->config['sso']['consume_path'],
          'creation_mode'    => 'real',
          'idm'              => $this->config['sso']['idm'],
          'idp'              => $this->config['sso']['idp'],
          'name_id_format'   => Maestrano::$EVT_CONFIG[$this->config['environment']]['sso.name_id_format'],
          'x509_fingerprint' => $this->config['sso']['x509_fingerprint'],
          'x509_certificate' => $this->config['sso']['x509_certificate'],
        ),
        'connec' => array(
          'enabled'          => $this->config['connec']['enabled'],
          'host'             => $this->config['connec']['host'],
          'base_path'        => $this->config['connec']['base_path'],
          'v2_path'          => $this->config['connec']['v2_path'],
          'reports_path'     => $this->config['connec']['reports_path']
        ),
        'webhook' => array(
          'account' => array(
            'groups_path'      => $this->config['webhook']['account']['groups_path'],
            'group_users_path' => $this->config['webhook']['account']['group_users_path'],
          ),
          'connec' => array(
            'initialization_path' => $this->config['webhook']['connec']['initialization_path'],
            'notifications_path'  => $this->config['webhook']['connec']['notifications_path'],
            'subscriptions'       => $this->config['webhook']['connec']['subscriptions']
          )
        )
      );

      $this->assertEquals(json_encode($expected),Maestrano::toMetadata());
    }
}
?>
