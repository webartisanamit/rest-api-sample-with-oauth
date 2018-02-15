# Library Api

--------------------------------------------------------------------------------
* Authentication

The client ID and secret are the key and secret for authentication.

if(empty(session_id();)) session_start();

require_once 'vendor/autoload.php';

$lib = new \Library\Lib(array(
	'client_id'     => 'XXX',
	'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
	'redirectUri'  => 'http://example.com/',
));

If the serialized token is available in the session storage, we tell the SDK
to use that token for subsequent requests.

if (isset($_SESSION['token'])) {
	$lib->setToken(unserialize($_SESSION['token']));
}

To get a access token
if (!isset($_SESSION['token']) and !$lib->getToken()) {
	$_SESSION['token'] = serialize($lib->requestAccessToken());
}

if ($lib->getToken()) {
	// Save the serialized token to the current session for subsequent requests
	$_SESSION['token'] = serialize($lib->getToken());
}

--------------------------------------------------------------------------------
* Making REST Requests

The PHP SDK is setup to allow easy access to REST endpoints. In general, a single result is returned as a Class representing that object, and multiple objects are returned as an FrilansInternational Collection, which is simply a wrapper around an array of results making them easier to manage.

The standard REST operations are mapped to a series of simple functions. We'll use the Profile service for our examples, but the operations below work on all documented FrilansInternational REST services.

To retrieve all profiles:
$profile = $lib->profiles()->all();

To retrieve profiles with filter:
$profiles = $lib->profiles()->filter('country_id', '=', 42)->all();

To retrieve profiles with include:
$profiles = $lib->profiles()->include('customers')->all();

To retrieve a single profile:
$profile = $lib->profiles()->find($profileId);

To query only completed profile:
$profile = $lib->profiles()->where('email', 'example@gmail.com')->get();

$attributes = [
    'name' => 'profile name',
    'email' => 'example@gmail.com'
];

To create a profile:
$profile = $lib->profiles()->create($attributes);

To update a profile:
$profile = $lib->profiles()->update($attributes, $profileId);

To upload a file
$profile = $lib->profiles()->upload([
   'id' => $profileId,
   'file' => $filePath
]);

And finally, to delete the profile:
$lib->profiles()->delete(1);
--------------------------------------------------------------------------------
