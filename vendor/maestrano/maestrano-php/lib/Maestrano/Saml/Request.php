<?php

/**
 * Create a SAML authorization request.
 */
class Maestrano_Saml_Request
{
    const ID_PREFIX = 'Maestrano';

    /**
     * A settings objects
     * @var Maestrano_Saml_Settings
     */
    protected $_settings;
    
    /**
     * GET parameters passed during
     * @var Array
     */
    protected $_get_params;

    /**
     * Construct the Request object.
     *
     * @param $get_params the GET parameters associative array
     */
    public function __construct($get_params = array(), $settings = null)
    {
        if ($settings == null) {
          $settings = Maestrano::sso()->getSamlSettings();
        }
      
        $this->_settings = $settings;
        $this->_get_params = $get_params;
    }

    /**
     * Generate the request.
     *
     * @return string A fully qualified URL that can be redirected to in order to process the authorization request.
     */
    public function getRedirectUrl()
    {

        // Build the request
        $id = $this->_generateUniqueID();
        $issueInstant = $this->_getTimestamp();

        $request = <<<AUTHNREQUEST
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="$id"
    Version="2.0"
    IssueInstant="$issueInstant"
    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
    AssertionConsumerServiceURL="{$this->_settings->spReturnUrl}">
    <saml:Issuer>{$this->_settings->spIssuer}</saml:Issuer>
    <samlp:NameIDPolicy
        Format="{$this->_settings->requestedNameIdFormat}"
        AllowCreate="true"></samlp:NameIDPolicy>
    <samlp:RequestedAuthnContext Comparison="exact">
        <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport</saml:AuthnContextClassRef>
    </samlp:RequestedAuthnContext>
</samlp:AuthnRequest>
AUTHNREQUEST;
        
        // Encode the request
        $deflatedRequest = gzdeflate($request);
        $base64Request = base64_encode($deflatedRequest);
        $encodedRequest = urlencode($base64Request);
        
        // Build redirect URL
        $url = $this->_settings->idpSingleSignOnUrl . "?SAMLRequest=" . $encodedRequest;
        
        // Keep the original GET parameters
        foreach ($this->_get_params as $param => $value) {
          $url .= "&" . $param . "=" . urlencode($value);
        }
        
        return $url;
    }

    protected function _generateUniqueID()
    {
        return self::ID_PREFIX . sha1(uniqid(mt_rand(), TRUE));
    }

    protected function _getTimestamp()
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $timestamp = strftime("%Y-%m-%dT%H:%M:%SZ");
        date_default_timezone_set($defaultTimezone);
        return $timestamp;
    }
}