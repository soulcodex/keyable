# Laravel Keyable

Keyable is a package that allows you to add API Keys to any model. This allows you to associate incoming requests with their respective models. You can also use Policies to authorize requests.

[![Latest Stable Version](https://poser.pugx.org/soulcodex/model-keyable/v)](//packagist.org/packages/soulcodex/model-keyable) [![Total Downloads](https://poser.pugx.org/soulcodex/model-keyable/downloads)](//packagist.org/packages/soulcodex/model-keyable) [![Latest Unstable Version](https://poser.pugx.org/soulcodex/model-keyable/v/unstable)](//packagist.org/packages/soulcodex/model-keyable) [![License](https://poser.pugx.org/soulcodex/model-keyable/license)](//packagist.org/packages/soulcodex/model-keyable)

## Installation

Require the ```soulcodex/keyable``` package in your ```composer.json``` and update your dependencies:

```bash
composer require soulcodex/keyable
```

Publish the migration and config files:
```bash
php artisan vendor:publish --provider="Soulcodex\Keyable\KeyableServiceProvider"
```

Run the migration:
```bash
php artisan migrate
```

## Usage

Add the ```Soulcodex\Keyable\Keyable``` trait to your model(s):

```php
use Illuminate\Database\Eloquent\Model;
use Soulcodex\Keyable\Keyable;

class Account extends Model
{
    use Keyable;

    // ...
}
```

Add the ```auth.apiKey``` middleware to the ```mapApiRoutes()``` function in your ```App\Providers\RouteServiceProvider``` file:

```php
// ...

protected function mapApiRoutes()
{
    Route::prefix('api')
        ->middleware(['api', 'auth.apikey'])
	->namespace($this->namespace . '\API')
	->group(base_path('routes/api.php'));
}

// ...
```

The middleware will authenticate API requests, ensuring they contain an API key that is valid.

### Accessing keyable models in your controllers
The model associated with the key will be attached to the incoming request as ```keyable```:

```php
use App\Http\Controllers\Controller;

class FooController extends Controller {

    public function index(Request $request) 
    {
        $model = $request->keyable;

        // ...
    }

}
```
Now you can use the keyable model to scope your associated API resources, for example:
```php
return $model->foo()->get();
```

### Keys Without Models

Sometimes you may not want to attach a model to an API key (if you wanted to have administrative access to your API). By default this functionality is turned off:

```php
<?php
	
return [
	
    'allow_empty_models' => true
	
];
```

### UUID support
Before migrate you can config if you prefer use bigint or uuid identifiers.
By default use `bigint` like ***keyable_id***

```php
<?php

return [

    'identifier' => 'bigint'
    
];
```

## Making Requests

By default, laravel-keyable uses bearer tokens to authenticate requests. Attach the API key to the header of each request:

```
Authorization: Bearer <key>
```

You can change where the API key is retrieved from by altering the setting in the `keyable.php` config file. Supported options are: `bearer`, `header`, and `parameter`.

As it is an array, you can use more than one of these options and combine them.

```php
<?php
	
return [
	
    'modes' => ['header'],
	
    'key' => 'X-Authorization',
	
];
```

Need to pass the key as a URL parameter? Set the mode to `parameter` and the key to the string you'll use in your URL:
```php
<?php
	
return [
	
    'modes' => ['parameter'],
	
    'key' => 'api_key'
	
];
```
Now you can make requests like this:
```php
https://example.com/api/posts?api_key=<key>
```

## Authorizing Requests

Laravel offers a great way to perform [Authorization](https://laravel.com/docs/5.8/authorization) on incoming requests using Policies. However, they are limited to authenticated users. We replicate that functionality to let you authorize requests on any incoming model.

To begin, add the `AuthorizeKeyableRequest` trait to your base `Controller.php class`:

```php
<?php

namespace App\Http\Controllers;

// ...

use Soulcodex\Keyable\Auth\AuthorizeKeyableRequest;

class Controller extends BaseController
{
    use AuthorizeKeyableRequest;
}
```

Next, create the `app/Policies/KeyablePolicies` folder and create a new policy:

```php
<?php

namespace App\Policies\KeyablePolicies;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Soulcodex\Keyable\Models\ApiKey;

class PostPolicy {

    public function view(ApiKey $apiKey, Model $keyable, Post $post) {
    	return !is_null($keyable->posts()->find($post->id));
    }
    
}
```

Lastly, register your policies in `AuthServiceProvider.php`:

```php
<?php

namespace App\Providers;

// ...

use App\Models\Post;
use App\Policies\KeyablePolicies\PostPolicy;
use Soulcodex\Keyable\Facades\Keyable;

class AuthServiceProvider extends ServiceProvider
{
	
    // ...
    
    protected $keyablePolicies = [
        Post::class => PostPolicy::class
    ];

    public function boot(GateContract $gate)
    {
        // ...
        Keyable::registerKeyablePolicies($this->keyablePolicies);
    }
    
}
```

In your controller, you can now authorize the request using the policy by calling `$this->authorizeKeyable(<ability>, <model>)`:

```php
<?php

namespace App\Http\Controllers\PostController;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller {

    public function show(Post $post) {
        $this->authorizeKeyable('view', $post);
        // ...
    }

}
```

## Artisan Commands

Generate an API key:

```bash
php artisan api-key:generate --id=1 --type="App\Models\Account"
php artisan api-key:generate --id='6324d582-5614-430b-a35c-c24b621a93c5' --type="App\Models\Account"
```

Delete an API key:
```bash
php artisan api-key:delete --id=12345
php artisan api-key:delete --id='6324d582-5614-430b-a35c-c24b621a93c5'
```

## Security

If you discover any security related issues, please email [info@soulcodex.es](mailto:info@soulcodex.es).

## License
Released under the [MIT](https://choosealicense.com/licenses/mit/) license. See [LICENSE](LICENSE.md) for more information.
