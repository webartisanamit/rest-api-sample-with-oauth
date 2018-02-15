# rest-api-sample-with-oauth

Sample of RestApi With Lravel Oauth
# Library Api

--------------------------------------------------------------------------------
## Authentication

The client ID and secret are the key and secret for authentication.

if(empty(session_id();)) session_start();<br>

require_once 'vendor/autoload.php';<br>

$lib = new \Library\Lib(array(<br>
	'client_id'     => 'XXX',<br>
	'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',<br>
	'redirectUri'  => 'http://example.com/',<br>
));<br>

If the serialized token is available in the session storage, we tell the SDK
to use that token for subsequent requests.<br>

if (isset($_SESSION['token'])) {<br>
	$lib->setToken(unserialize($_SESSION['token']));<br>
}<br>

To get a access token<br>
if (!isset($_SESSION['token']) and !$lib->getToken()) {<br>
	$_SESSION['token'] = serialize($lib->requestAccessToken());<br>
}<br>

if ($lib->getToken()) {<br>
	// Save the serialized token to the current session for subsequent requests<br>
	$_SESSION['token'] = serialize($lib->getToken());<br>
}

--------------------------------------------------------------------------------
## Making REST Requests

The PHP SDK is setup to allow easy access to REST endpoints. In general, a single result is returned as a Class representing that object, and multiple objects are returned as an FrilansInternational Collection, which is simply a wrapper around an array of results making them easier to manage.

The standard REST operations are mapped to a series of simple functions. We'll use the Profile service for our examples, but the operations below work on all documented FrilansInternational REST services.

To retrieve all profiles:<br>
$profile = $lib->profiles()->all();
<br><br>
To retrieve profiles with filter:<br>
$profiles = $lib->profiles()->filter('country_id', '=', 42)->all();
<br><br>
To retrieve profiles with include:<br>
$profiles = $lib->profiles()->include('customers')->all();
<br><br>
To retrieve a single profile:<br>
$profile = $lib->profiles()->find($profileId);
<br><br>
To query only completed profile:<br>
$profile = $lib->profiles()->where('email', 'example@gmail.com')->get();
<br><br>
$attributes = [<br>
    'name' => 'profile name',<br>
    'email' => 'example@gmail.com'<br>
];<br>
<br>
To create a profile:<br>
$profile = $lib->profiles()->create($attributes);
<br><br>
To update a profile:<br>
$profile = $lib->profiles()->update($attributes, $profileId);
<br><br>
To upload a file<br>
$profile = $lib->profiles()->upload([<br>
   'id' => $profileId,<br>
   'file' => $filePath<br>
]);<br><br>

And finally, to delete the profile:<br>
$lib->profiles()->delete(1);
