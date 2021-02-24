<?php

namespace Soulcodex\Keyable\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Soulcodex\Keyable\Models\ApiKey;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        //Get API token from request
        $token = $this->getKeyFromRequest($request);

        //Check for presence of key
        if (!$token) return $this->unauthorizedResponse();

        //Get API key
        $apiKey = ApiKey::getByKey($token);

        //Validate key
        if (!($apiKey instanceof ApiKey)) return $this->unauthorizedResponse();

        //Get the model
        $keyable = $apiKey->keyable;

        //Validate model
        if (config('keyable.allow_empty_models', false)) {

            if (!$keyable && (!is_null($apiKey->keyable_type) || !is_null($apiKey->keyable_id)))
                return $this->unauthorizedResponse();

        } else {

            if (!$keyable)
                return $this->unauthorizedResponse();

        }

        //Attach the apikey object to the request
        $request->attributes->add(['apiKey' => $apiKey]);

        if($keyable) {
            $request->attributes->add(['keyable' => $keyable]);
        }

        //Update last_used_at
        $apiKey->markAsUsed();

        //Return
        return $next($request);
    }

    /**
     * @param $request
     * @return mixed
     */
    protected function getKeyFromRequest($request)
    {
        // Retrieve modes from config
        $modes = config('keyable.modes', 'bearer');

        // Bearer check
        if (
            in_array('bearer', $modes) &&
            !is_null($request->bearerToken())
        ) {
            return $request->bearerToken();
        }

        // Header check
        if (
            in_array('header', $modes) &&
            !is_null($request->header(config('keyable.key', 'X-Authorization')))
        ) {
            return $request->header(config('keyable.key', 'X-Authorization'));
        }

        // Parameter check
        if (
            in_array('parameter', $modes) &&
            !is_null($request->input(config('keyable.key', 'api_key')))
        ) {
            return $request->input(config('keyable.key', 'api_key'));
        }
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    protected function unauthorizedResponse()
    {
        return response([
            'error' => [
                'message' => 'Unauthorized'
            ]
        ], 401);
    }
}
