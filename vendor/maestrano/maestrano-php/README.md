<p align="center">
<img src="https://raw.github.com/maestrano/maestrano-php/master/maestrano.png" alt="Maestrano Logo">
<br/>
<br/>
</p>

Maestrano Cloud Integration is currently in closed beta. Want to know more? Send us an email to <contact@maestrano.com>.



- - -

1. [Getting Setup](#getting-setup)
2. [Getting Started](#getting-started)
  * [Installation](#installation)
  * [Configuration](#configuration)
  * [Metadata Endpoint](#metadata-endpoint)
3. [Single Sign-On Setup](#single-sign-on-setup)
  * [User Setup](#user-setup)
  * [Group Setup](#group-setup)
  * [Controller Setup](#controller-setup)
  * [Other Controllers](#other-controllers)
  * [Redirecting on logout](#redirecting-on-logout)
  * [Redirecting on error](#redirecting-on-error)
4. [Account Webhooks](#account-webhooks)
  * [Groups Controller](#groups-controller-service-cancellation)
  * [Group Users Controller](#group-users-controller-business-member-removal)
5. [API](#api)
  * [Payment API](#payment-api)
    * [Bill](#bill)
    * [Recurring Bill](#recurring-bill)
  * [Membership API](#membership-api)
    * [User](#user)
    * [Group](#group)
6. [Connec!™ Data Sharing](#connec-data-sharing)
  * [Making Requests](#making-requests)
  * [Webhook Notifications](#webhook-notifications)

- - -

## Getting Setup
Before integrating with us you will need an App ID and API Key. Maestrano Cloud Integration being still in closed beta you will need to contact us beforehand to gain production access.

For testing purpose we provide an API Sandbox where you can freely obtain an App ID and API Key. The sandbox is great to test single sign-on and API integration (e.g: billing API).

To get started just go to: http://api-sandbox.maestrano.io

A **php demo application** is also available: https://github.com/maestrano/demoapp-php

## Getting Started

### Installation

To install maestrano-php using Composer, add this dependency to your project's composer.json:
```
{
  "require": {
    "maestrano/maestrano-php": "~0.10.0"
  }
}
```

Then install via:
```
composer install
```

To use the bindings, either use Composer's [autoload](https://getcomposer.org/doc/00-intro.md#autoloading):
```php
require_once('vendor/autoload.php');
```

Or manually:
```php
require_once('/path/to/vendor/maestrano/maestrano-php/lib/Maestrano.php');
```

### Configuration
#### Via config file

You can configure maestrano via json using a configuration file like "maestrano.json" which you can load using:
```php
Maestrano::configure('/path/to/maestrano.json');
```

You can add other configuration presets by doing the following:
```php
Maestrano::with('my-preset')->configure('/path/to/some-other-preset.json');
```
Additional presets can then be specified when doing particular action, such as initializing a Connec!™ client or triggering a SSO handshake. These presets are particularly useful if you are dealing with multiple Maestrano-style marketplaces (multi-enterprise integration).

Note that the following two commands are equivalent:
```php
Maestrano::configure('/path/to/config.json');
// equivalent to
Maestrano::with('maestrano')->configure('/path/to/config.json');
```

The json file may look like this:
```php
{
  # ===> App Configuration
  #
  # => environment
  # The environment to connect to. If set to 'production' then all Single Sign-On (SSO) and API requests will be made to maestrano.com. If set to 'test' then requests will be made to api-sandbox.maestrano.io.
  # The api-sandbox allows you to easily test integration scenarios.
  "environment": "test",

  # => host
  # This is your application host (e.g: my-app.com) which is ultimately used to redirect users to the right SAML url during SSO handshake.
  "app": {
    "host": "http://localhost:8888"
  },

  # ===> Api Configuration
  #
  "api": {
    # => id, key
    # Your application App ID and API key which you can retrieve on http://maestrano.com via your cloud partner dashboard.
    # For testing you can retrieve/generate an api.id and api.key from the API Sandbox directly on http://api-sandbox.maestrano.io.
    "id": "prod_or_sandbox_app_id",
    "key": "prod_or_sandbox_api_key",

    # => Default group id (optional)
    # Setting a default group_id is only useful if your application is SINGLE TENANT (max one customer per instance of your application).
    # Otherwise, the group_id should be specified at runtime when making API calls. (e.g.: instantiating a Connec!™ Client)
    "group_id": "some-maestrano-group-id",

    # => Account API host (optional)
    # This is the host and base path that should be used for any API call related to account management (billing, fetch users/groups)
    "host": "https://maestrano.com",
    "base": "/api/v1/",
  },

  # ===> SSO Configuration
  #
  "sso": {

    # => enabled
    # Enable/Disable single sign-on. When troubleshooting authentication issues you might want to disable SSO temporarily
    "enabled": true,

    # => slo_enabled
    # Enable/Disable single logout. When troubleshooting authentication issues you might want to disable SLO temporarily.
    # If set to false then MnoSession#isValid - which should be used in a controller action filter to check user session - always return true
    "slo_enabled": true,

    # => idp (optional)
    # This is the URL of the identity provider to use when triggering a SSO handshake
    "idp": "https://maestrano.com",

    # => idm
    # By default we consider that the domain managing user identification is the same as your application host (see above config.app.host parameter).
    # If you have a dedicated domain managing user identification and therefore responsible for the single sign-on handshake (e.g: https://idp.my-app.com) then you can specify it below
    "idm": "https://idp.myapp.com",

    # => init_path
    # This is your application path to the SAML endpoint that allows users to initialize SSO authentication.
    # Upon reaching this endpoint users your application will automatically create a SAML request and redirect the user to Maestrano. Maestrano will then authenticate and authorize the user.
    # Upon authorization the user gets redirected to your application consumer endpoint (see below) for initial setup and/or login.
    "init_path": "/maestrano/auth/saml/init.php"

    # => consume_path
    #This is your application path to the SAML endpoint that allows users to finalize SSO authentication.
    # During the 'consume' action your application sets users (and associated group) up and/or log them in.
    "consume_path": "/maestrano/auth/saml/consume.php"

    # => creation_mode
    # !IMPORTANT
    # On Maestrano users can take several "instances" of your service. You can consider
    # each "instance" as 1) a billing entity and 2) a collaboration group (this is
    # equivalent to a 'customer account' in a commercial world). When users login to
    # your application via single sign-on they actually login via a specific group which
    # is then supposed to determine which data they have access to inside your application.
    #
    # E.g: John and Jack are part of group 1. They should see the same data when they login to
    # your application (employee info, analytics, sales etc..). John is also part of group 2
    # but not Jack. Therefore only John should be able to see the data belonging to group 2.
    #
    # In most application this is done via collaboration/sharing/permission groups which is
    # why a group is required to be created when a new user logs in via a new group (and
    # also for billing purpose - you charge a group, not a user directly).
    #
    # - mode: 'real'
    # In an ideal world a user should be able to belong to several groups in your application.
    # In this case you would set the 'sso.creation_mode' to 'real' which means that the uid
    # and email we pass to you are the actual user email and maestrano universal id.
    #
    # - mode: 'virtual'
    # Now let's say that due to technical constraints your application cannot authorize a user
    # to belong to several groups. Well next time John logs in via a different group there will
    # be a problem: the user already exists (based on uid or email) and cannot be assigned
    # to a second group. To fix this you can set the 'sso.creation_mode' to 'virtual'. In this
    # mode users get assigned a truly unique uid and email across groups. So next time John logs
    # in a whole new user account can be created for him without any validation problem. In this
    # mode the email we assign to him looks like "usr-sdf54.cld-45aa2@mail.maestrano.com". But don't
    # worry we take care of forwarding any email you would send to this address
    "creation_mode": "virtual",
  },

  # ===> Connec!™ Configuration
  #
  # => host and API paths (optional)
  # The Connec!™ endpoint to use if you need to overwrite it (i.e. if you want to proxy requests or use a stub)
  "connec": {
    # == Connec!™ enabled
    # Data-sharing can be enabled/disabled
    "enabled": true,

    # == Connec!™ API endpoint configuration
    "host": "http://connec.maestrano.io",
    "base_path": "/api",
    "v2_path": "/v2",
    "reports_path": "/reports",

    # == Connec!™ client timeout
    # Timeout value in seconds when connecting to the Connec!™ API
    "timeout": 180
  },

  # ===> Webhooks
  # This section describe how to configure the Account and Connec!™ webhooks

  "webhook": {

    # Single sign on has been setup into your app and Maestrano users are now able
    # to use your service. Great! Wait what happens when a business (group) decides to
    # stop using your service? Also what happens when a user gets removed from a business?
    # Well the endpoints below are for Maestrano to be able to notify you of such
    # events.
    #
    # Even if the routes look restful we issue only issue DELETE requests for the moment
    # to notify you of any service cancellation (group deletion) or any user being
    # removed from a group.
    "account": {
      "groups_path": "/maestrano/account/groups/:id",
      "group_users_path": "/maestrano/account/groups/:group_id/users/:id"
    },

    # ==> Connec Subscriptions/Webhook
    # The following section is used to configure the Connec!™ webhooks and which entities
    # you should receive via webhook.
    #
    #
    "connec": {

      # == Initialization Path
      # Only for applications hosted on Maestrano
      # The endpoint to trigger when the application is started.
      # This should be used as a hook to retrieve updates from Connec!™ whils the application was idle.
      #
      "initialization_path": "/maestrano/connec/initialization",

      # == Notification Path
      # This is the path of your application where notifications (created/updated entities) will
      # be POSTed to.
      # You should have a controller matching this path handling the update of your internal entities
      # based on the Connec!™ entities you receive
      #
      "notifications_path": "/maestrano/connec/notifications",

      # == Subscriptions
      # This is the list of entities (organizations,people,invoices etc.) for which you want to be
      # notified upon creation/update in Connec!™
      #
      "subscriptions": {
        "accounts": true,
        "company": true,
        "employees": false,
        "events": false,
        "event_orders": false,
        "invoices": true,
        "items": true,
        "journals": false,
        "opportunities": true,
        "organizations": true,
        "payments": false,
        "pay_items": false,
        "pay_schedules": false,
        "pay_stubs": false,
        "pay_runs": false,
        "people": true,
        "projects": false,
        "purchase_orders": false,
        "quotes": false,
        "sales_orders": false,
        "tax_codes": true,
        "tax_rates": false,
        "time_activities": false,
        "time_sheets": false,
        "venues": false,
        "warehouses": false,
        "work_locations": false
      }
    }
  }
}
```

#### At runtime

You can configure maestrano using an associative array if you prefer. The structure is the same as for the json above:

```php
Maestrano::configure(array(
  'environment' => 'production',
  'sso' => array(
    'creation_mode' => 'real'
  )
));
```

You can also define a specific configuration preset at runtime:
```php
Maestrano::with('my-config-preset')->configure(array('sso' => array('creation_mode' => 'real')));
```

### Metadata Endpoint
Your configuration initializer is now all setup and shiny. Great! But we need to know about it. Of course
we could propose a long and boring form on maestrano.com for you to fill all these details (especially the webhooks) but we thought it would be more convenient to fetch that automatically.

For that we expect you to create a metadata endpoint that we can fetch regularly (or when you press 'refresh metadata' in your maestrano cloud partner dashboard). By default we assume that it will be located at
YOUR_WEBSITE/maestrano/metadata(.json or .php)

Of course if you prefer a different url you can always change that endpoint in your maestrano cloud partner dashboard.

What would the controller action look like? First let's talk about authentication. You don't want that endpoint to be visible to anyone. Maestrano always uses http basic authentication to contact your service remotely. The login/password used for this authentication are your actual api.id and api.key.

So here is an example of page to adapt depending on the framework you're using:

```php
header('Content-Type: application/json');

// Authenticate using http basic
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  echo Maestrano::toMetadata();
} else {
  echo "Sorry! I'm not giving you my API metadata";
}

// With configuration preset
// if (Maestrano::with('my-config-preset')->authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
//   echo Maestrano::with('my-config-preset')->toMetadata();
// } else {
//   echo "Sorry! I'm not giving you my API metadata";
// }
```

## Single Sign-On Setup

> **Heads up!** Prefer to use OpenID rather than our SAML implementation? Just look at our [OpenID Guide](https://maestrano.atlassian.net/wiki/display/CONNECAPIV2/SSO+via+OpenID) to get started!

In order to get setup with single sign-on you will need a user model and a group model. It will also require you to write a controller for the init phase and consume phase of the single sign-on handshake.

You might wonder why we need a 'group' on top of a user. Well Maestrano works with businesses and as such expects your service to be able to manage groups of users. A group represents 1) a billing entity 2) a collaboration group. During the first single sign-on handshake both a user and a group should be created. Additional users logging in via the same group should then be added to this existing group (see controller setup below)

### User Setup
Let's assume that your user model is called 'User'. The best way to get started with SSO is to define a class method on this model called 'findOrCreateForMaestrano' accepting a Maestrano.Sso.User and aiming at either finding an existing maestrano user in your database or creating a new one. Your user model should also have a 'Provider' property and a 'Uid' property used to identify the source of the user - Maestrano, LinkedIn, AngelList etc..

### Group Setup
The group setup is similar to the user one. The mapping is a little easier though. Your model should also have the 'Provider' property and a 'Uid' properties. Also your group model could have a AddMember method and also a hasMember method (see controller below)

### Controller Setup
You will need two controller action init and consume. The init action will initiate the single sign-on request and redirect the user to Maestrano. The consume action will receive the single sign-on response, process it and match/create the user and the group.

The init action is all handled via Maestrano methods and should look like this:
```php
// Build SSO request - Make sure GET parameters gets passed
// to the constructor
$req = new Maestrano_Saml_Request($_GET);

// You can also use a specific configuration preset
// $req = Maestrano_Saml_Request::with('my-config-preset')->new($_GET);

// Redirect the user to Maestrano Identity Provider
header('Location: ' . $req->getRedirectUrl());
%>
```

Based on your application requirements the consume action might look like this:
```php
session_start();

// Build SSO Response using SAMLResponse parameter value sent via
// POST request
$resp = new Maestrano_Saml_Response($_POST['SAMLResponse']);

// With configuration preset
// $resp = Maestrano_Saml_Response::with('my-config-preset')->new($_POST['SAMLResponse']);

if ($resp->isValid()) {

  // Get the user as well as the user group
  $user = new Maestrano_Sso_User($resp);
  $group = new Maestrano_Sso_Group($resp);

  //-----------------------------------
	// For the sake of simplicity we store everything in session. This
  // step should actually involve linking/creating the Maestrano user and group
  // as models in your application
  //-----------------------------------
  $_SESSION["loggedIn"] = true;
  $_SESSION["firstName"] = $user->getFirstName();
  $_SESSION["lastName"] = $user->getLastName();

  // Important - toId() and toEmail() have different behaviour compared to
  // getId() and getEmail(). In you maestrano configuration file, if your sso > creation_mode
  // is set to 'real' then toId() and toEmail() return the actual id and email of the user which
  // are only unique across users.
  // If you chose 'virtual' then toId() and toEmail() will return a virtual (or composite) attribute
  // which is truly unique across users and groups
  $_SESSION["id"] = $user->toId();
  $_SESSION["email"] = $user->toEmail();

  // Store group details
  $_SESSION["groupName"] = $group->getName();
  $_SESSION["groupId"] = $group->getId();


  // Set Maestrano Session (used for single logout - see below)
  $mnoSession = new Maestrano_Sso_Session($_SESSION,$user);
  $mnoSession->save();

  // Redirect the user to home page
  header('Location: /');

} else {
  echo "Holy Banana! Saml Response does not seem to be valid";
}
%>
```

Note that for the consume action you should disable CSRF authenticity if your framework is using it by default. If CSRF authenticity is enabled then your app will complain on the fact that it is receiving a form without CSRF token.

### Other Controllers
If you want your users to benefit from single logout then you should define the following filter in a module and include it in all your controllers except the one handling single sign-on authentication.

```php
$mnoSession = new Maestrano_Sso_Session($_SESSION);

// With a configuration preset
// $mnoSession = Maestrano_Sso_Session::with('my-config-preset')->new($_SESSION);

// Trigger SSO handshake if session not valid anymore
if (!$mnoSession->isValid()) {
  header('Location: ' . Maestrano::sso()->getInitUrl());

  // With a configuration preset
  // header('Location: ' . Maestrano::with('my-config-preset')->sso()->getInitUrl());

}
```

The above piece of code makes at most one request every 3 minutes (standard session duration) to the Maestrano website to check whether the user is still logged in Maestrano. Therefore it should not impact your application from a performance point of view.

If you start seing session check requests on every page load it means something is going wrong at the http session level. In this case feel free to send us an email and we'll have a look with you.

### Redirecting on logout
When Maestrano users sign out of your application you can redirect them to the Maestrano logout page. You can get the url of this page by calling:

```php
Maestrano::sso()->getLogoutUrl()

// With a configuration preset
// Maestrano::with('my-config-preset')->sso()->getLogoutUrl()
```

### Redirecting on error
If any error happens during the SSO handshake, you can redirect users to the following URL:

```php
Maestrano::sso()->getUnauthorizedUrl()

// With a configuration preset
// Maestrano::with('my-config-preset')->sso()->getUnauthorizedUrl()
```

## Account Webhooks
Single sign on has been setup into your app and Maestrano users are now able to use your service. Great! Wait what happens when a business (group) decides to stop using your service? Also what happens when a user gets removed from a business? Well the controllers describes in this section are for Maestrano to be able to notify you of such events.

### Groups Controller (service cancellation)
Sad as it is a business might decide to stop using your service at some point. On Maestrano billing entities are represented by groups (used for collaboration & billing). So when a business decides to stop using your service we will issue a DELETE request to the webhook.account.groups_path endpoint (typically /maestrano/account/groups/:id).

Maestrano only uses this controller for service cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

The controller example below reimplements the authenticate_maestrano! method seen in the [metadata section](#metadata) for completeness. Utimately you should move this method to a helper if you can.

The example below needs to be adapted depending on your application:

```php
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  $someGroup = MyGroupModel::findByMnoId(restfulIdFromUrl);
  $someGroup->disableAccess();
}

// With a configuration preset
// if (Maestrano::with('my-config-preset')->authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
//   $someGroup = MyGroupModel::findByMnoId(restfulIdFromUrl);
//   $someGroup->disableAccess();
// }
```

### Group Users Controller (business member removal)
A business might decide at some point to revoke access to your services for one of its member. In such case we will issue a DELETE request to the webhook.account.group_users_path endpoint (typically /maestrano/account/groups/:group_id/users/:id).

Maestrano only uses this controller for user membership cancellation so there is no need to implement any other type of action - ie: GET, PUT/PATCH or POST. The use of other http verbs might come in the future to improve the communication between Maestrano and your service but as of now it is not required.

The controller example below reimplements the authenticate_maestrano! method seen in the [metadata section](#metadata) for completeness. Utimately you should move this method to a helper if you can.

The example below needs to be adapted depending on your application:

```php
if (Maestrano::authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
  $someGroup = MyGroupModel::findByMnoId(restfulGroupIdFromUrl);
  $someGroup->removeUserById(restfulIdFromUrl);
}

// With a configuration preset
// if (Maestrano::with('my-config-preset')->authenticate($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
//  $someGroup = MyGroupModel::findByMnoId(restfulGroupIdFromUrl);
//  $someGroup->removeUserById(restfulIdFromUrl);
// }
```

## API
The maestrano package also provides bindings to its REST API allowing you to access, create, update or delete various entities under your account (e.g: billing).

### Payment API

#### Bill
A bill represents a single charge on a given group.

```php
Maestrano_Account_Bill
```

##### Attributes
All attributes are available via their getter/setter counterpart. E.g:
```php
// for priceCents field
$bill->getPriceCents();
$bill->setPriceCents(2000);
```

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the bill</td>
<tr>

<tr>
<td><b>groupId</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The id of the group you are charging</td>
<tr>

<tr>
<td><b>priceCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The amount in cents to charge to the customer</td>
<tr>

<tr>
<td><b>description</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>A description of the product billed as it should appear on customer invoice</td>
<tr>

<tr>
<td><b>createdAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the the bill was created</td>
<tr>

<tr>
<td><b>updatedAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the bill was last updated</td>
<tr>

<tr>
<td><b>status</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>Status of the bill. Either 'submitted', 'invoiced' or 'cancelled'.</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>AUD</td>
<td>The currency of the amount charged in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>units</b></td>
<td>read/write</td>
<td>Float</td>
<td>-</td>
<td>1.0</td>
<td>How many units are billed for the amount charged</td>
<tr>

<tr>
<td><b>periodStartedAt</b></td>
<td>read/write</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>If the bill relates to a specific period then specifies when the period started. Both period_started_at and period_ended_at need to be filled in order to appear on customer invoice.</td>
<tr>

<tr>
<td><b>periodEndedAt</b></td>
<td>read/write</td>
<td>Date</td>
<td>-</td>
<td>false</td>
<td>If the bill relates to a specific period then specifies when the period ended. Both period_started_at and period_ended_at need to be filled in order to appear on customer invoice.</td>
<tr>

<tr>
<td><b>thirdParty</b></td>
<td>read/write</td>
<td>Boolean</td>
<td>-</td>
<td>-</td>
<td>Whether this bill is related to a third party cost or not. External expenses engaged for customers - such as paying a  provider for sending SMS on behalf of customers - should be flagged as third party.</td>
<tr>

</table>

##### Actions

List all bills you have created and iterate through the list
```php
$bills = Maestrano_Account_Bill::all();

// With configuration preset
// $bills = Maestrano_Account_Bill::with('my-config-preset')->all();
```

Access a single bill by id
```php
$bill = Maestrano_Account_Bill::retrieve("bill-f1d2s54");

// With configuration preset
// $bills = Maestrano_Account_Bill::with('my-config-preset')->retrieve("bill-f1d2s54");
```

Create a new bill
```php
$bill = Maestrano_Account_Bill::create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase"
));

// With configuration preset
// $bill = Maestrano_Account_Bill::with('my-config-preset')->create(array(
//   'groupId' => 'cld-3',
//   'priceCents' => 2000,
//   'description' => "Product purchase"
// ));
```

Cancel a bill
```php
$bill = Maestrano_Account_Bill::retrieve("bill-f1d2s54");
$bill->cancel();

// With configuration preset
// $bills = Maestrano_Account_Bill::with('my-config-preset')->retrieve("bill-f1d2s54");
// $bill->cancel();
```

#### Recurring Bill
A recurring bill charges a given customer at a regular interval without you having to do anything.

```php
Maestrano_Account_RecurringBill
```

##### Attributes
All attributes are available via their getter/setter counterpart. E.g:
```php
// for priceCents field
$bill->getPriceCents();
$bill->setPriceCents(2000);
```

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the recurring bill</td>
<tr>

<tr>
<td><b>groupId</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The id of the group you are charging</td>
<tr>

<tr>
<td><b>priceCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>Yes</b></td>
<td>-</td>
<td>The amount in cents to charge to the customer</td>
<tr>

<tr>
<td><b>description</b></td>
<td>read/write</td>
<td>String</td>
<td><b>Yes</b></td>
<td>-</td>
<td>A description of the product billed as it should appear on customer invoice</td>
<tr>

<tr>
<td><b>period</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>Month</td>
<td>The unit of measure for the billing cycle. Must be one of the following: 'Day', 'Week', 'SemiMonth', 'Month', 'Year'</td>
<tr>

<tr>
<td><b>frequency</b></td>
<td>read/write</td>
<td>Integer</td>
<td>-</td>
<td>1</td>
<td>The number of billing periods that make up one billing cycle. The combination of billing frequency and billing period must be less than or equal to one year. If the billing period is SemiMonth, the billing frequency must be 1.</td>
<tr>

<tr>
<td><b>cycles</b></td>
<td>read/write</td>
<td>Integer</td>
<td>-</td>
<td>nil</td>
<td>The number of cycles this bill should be active for. In other words it's the number of times this recurring bill should charge the customer.</td>
<tr>

<tr>
<td><b>startDate</b></td>
<td>read/write</td>
<td>DateTime</td>
<td>-</td>
<td>Now</td>
<td>The date when this recurring bill should start billing the customer</td>
<tr>

<tr>
<td><b>createdAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the the bill was created</td>
<tr>

<tr>
<td><b>updatedAt</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the recurring bill was last updated</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>read/write</td>
<td>String</td>
<td>-</td>
<td>AUD</td>
<td>The currency of the amount charged in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>status</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>Status of the recurring bill. Either 'submitted', 'active', 'expired' or 'cancelled'.</td>
<tr>

<tr>
<td><b>initialCents</b></td>
<td>read/write</td>
<td>Integer</td>
<td><b>-</b></td>
<td>0</td>
<td>Initial non-recurring payment amount - in cents - due immediately upon creating the recurring bill</td>
<tr>

</table>

##### Actions

List all recurring bills you have created:
```php
$recBills = Maestrano_Account_RecurringBill::all();

// With configuration preset
// $recBills = Maestrano_Account_RecurringBill::with('my-config-preset')->all();
```

Access a single bill by id
```php
$recBill = Maestrano_Account_RecurringBill::retrieve("rbill-f1d2s54");

// With configuration preset
// $recBills = Maestrano_Account_RecurringBill::with('my-config-preset')->retrieve("rbill-f1d2s54");
```

Create a new recurring bill
```php
$recBill = Maestrano_Account_RecurringBill::create(array(
  'groupId' => 'cld-3',
  'priceCents' => 2000,
  'description' => "Product purchase",
  'period' => 'Month',
  'startDate' => (new DateTime('NOW'))
));

// With configuration preset
// $recBill = Maestrano_Account_RecurringBill::with('my-config-preset')->create(array(
//   'groupId' => 'cld-3',
//   'priceCents' => 2000,
//   'description' => "Product purchase",
//   'period' => 'Month',
//   'startDate' => (new DateTime('NOW'))
// ));
```

Cancel a bill
```php
$recBill = Maestrano_Account_RecurringBill::retrieve("bill-f1d2s54");
$recBill->cancel();

// With configuration preset
// $recBill = Maestrano_Account_RecurringBill::with('my-config-preset')->retrieve("bill-f1d2s54");
// $recBill->cancel();
```

### Membership API

#### User
A user is a member of a group having access to your application. Users are currently readonly.

```php
Maestrano_Account_User
```

##### Attributes

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the user</td>
<tr>

<tr>
<td><b>first_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user first name</td>
<tr>

<tr>
<td><b>last_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user last name</td>
<tr>

<tr>
<td><b>email</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user real email address</td>
<tr>

<tr>
<td><b>company_name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The user company name as it was entered when they signed up. Nothing related to the user group name.</td>
<tr>

<tr>
<td><b>country</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The country of the user in <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2 format</a> (2 letter code). E.g: 'US' for USA, 'AU' for Australia.</td>
<tr>

<tr>
<td><b>created_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the user was created</td>
<tr>

<tr>
<td><b>updated_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the user was last updated</td>
<tr>

</table>

##### Actions

List all users having access to your application
```php
$users = Maestrano_Account_User::all();

// With configuration preset
// $users = Maestrano_Account_User::with('my-config-preset')->all();
```

Access a single user by id
```php
$user = Maestrano_Account_User::retrieve("usr-f1d2s54");
$user->getFirstName();

// With configuration preset
// $user = Maestrano_Account_User::with('my-config-preset')->retrieve("usr-f1d2s54");
```

#### Group
A group represents a customer account and is composed of members (users) having access to your application. A group also represents a chargeable account (see Bill/RecurringBill). Typically you can remotely check if a group has entered a credit card on Maestrano.

Groups are currently readonly.


```php
Maestrano_Account_Group
```

##### Attributes

<table>
<tr>
<th>Field</th>
<th>Mode</th>
<th>Type</th>
<th>Required</th>
<th>Default</th>
<th>Description</th>
<tr>

<tr>
<td><b>id</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The id of the group</td>
<tr>

<tr>
<td><b>name</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The group name</td>
<tr>

<tr>
<td><b>email</b></td>
<td>readonly</td>
<td>string</td>
<td><b>-</b></td>
<td>-</td>
<td>The principal email address for this group (admin email address)</td>
<tr>

<tr>
<td><b>has_credit_card</b></td>
<td>readonly</td>
<td>Boolean</td>
<td><b>-</b></td>
<td>-</td>
<td>Whether the group has entered a credit card on Maestrano or not</td>
<tr>

<tr>
<td><b>free_trial_end_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td><b>-</b></td>
<td>-</td>
<td>When the group free trial will be finishing on Maestrano. You may optionally consider this date for your own free trial (optional)</td>
<tr>

<tr>
<td><b>currency</b></td>
<td>readonly</td>
<td>String</td>
<td>-</td>
<td>-</td>
<td>The currency used by this Group in <a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes">ISO 4217 format</a> (3 letter code)</td>
<tr>

<tr>
<td><b>country</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The country of the group in <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">ISO 3166-1 alpha-2 format</a> (2 letter code). E.g: 'US' for USA, 'AU' for Australia.</td>
<tr>

<tr>
<td><b>city</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The city of the group</td>
<tr>

<tr>
<td><b>main_accounting</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>Main accounting package used by this group. Possible values: 'quickbooks', 'xero', 'myob'</td>
<tr>

<tr>
<td><b>timezone</b></td>
<td>readonly</td>
<td>String</td>
<td><b>-</b></td>
<td>-</td>
<td>The group timezone in <a href="http://en.wikipedia.org/wiki/List_of_tz_database_time_zones">Olson format</a></td>
<tr>

<tr>
<td><b>created_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the group was created</td>
<tr>

<tr>
<td><b>updated_at</b></td>
<td>readonly</td>
<td>DateTime</td>
<td>-</td>
<td>-</td>
<td>When the group was last updated</td>
<tr>

</table>

##### Actions

List all groups having access to your application
```php
$groups = Maestrano_Account_Group::all();

// With configuration preset
// $groups = Maestrano_Account_Group::with('my-config-preset')->all();
```

Access a single group by id
```php
$group = Maestrano_Account_Group::retrieve("usr-f1d2s54");
$group->getName();

// With configuration preset
// $group = Maestrano_Account_Group::with('my-config-preset')->retrieve("usr-f1d2s54");
```


## Connec!™ Data Sharing
Maestrano offers the capability to share actual business data between applications via its data sharing platform Connec!™.

The platform exposes a set of RESTful JSON APIs allowing your application to receive data generated by other applications and update data in other applications as well!

Connec!™ also offers the ability to create webhooks on your side to get automatically notified of changes happening in other systems.

Connec!™ enables seamless data sharing between the Maestrano applications as well as popular apps such as QuickBooks and Xero. One connector - tens of integrations!

### Making Requests

Connec!™ REST API documentation can be found here: http://maestrano.github.io/connec

The Maestrano API provides a built-in client - based on CURL - for connecting to Connec!™. Things like connection and authentication are automatically managed by the Connec!™ client.


```php
# Pass the customer group id as argument or use the default one specified in the json configuration
$client = new Maestrano_Connec_Client("cld-f7f5g4")

// With configuration preset
// $client = Maestrano_Connec_Client::with('my-config-preset')->new("cld-f7f5g4")

# Retrieve all organizations (customers and suppliers) created in other applications
$resp = $client->get('/organizations')
$resp['body'] # returns the raw response "{\"organizations\":[ ... ]}"
$resp['code'] # returns the response code. E.g. "200"

# Create a new organization
$client->post('/organizations', array('organizations' => array('name' => "DoeCorp Inc.")) )

# Update an organization
$client->put('/organizations/e32303c1-5102-0132-661e-600308937d74', array('organizations' => array('is_customer_' => true)))

# Retrieve a report
$client->getReport('/profit_and_loss', array('from' => '2015-01-01', 'to' => '2015-01-01', 'period' => 'MONTHLY'))
```


### Webhook Notifications
If you have configured the Maestrano API to receive update notifications (see 'subscriptions' configuration at the top) from Connec!™ then you can expect to receive regular POST requests on the notification_path you have configured.

Notifications are JSON messages containing the list of entities that have recently changed in other systems. You will only receive notifications for entities you have subscribed to.

Example of notification message:
```ruby
{
  "organizations": [
    { "id": "e32303c1-5102-0132-661e-600308937d74", name: "DoeCorp Inc.", ... }
  ],
  "people": [
    { "id": "a34303d1-4142-0152-362e-610408337d74", first_name: "John", last_name: "Doe", ... }
  ]
}
```

Entities sent via notifications follow the same data structure as the one described in our REST API documentation (available at http://maestrano.github.io/connec)


## Support
This README is still in the process of being written and improved. As such it might not cover some of the questions you might have.

So if you have any question or need help integrating with us just let us know at support@maestrano.com

## License

MIT License. Copyright 2014 Maestrano Pty Ltd. https://maestrano.com

You are not granted rights or licenses to the trademarks of Maestrano.
