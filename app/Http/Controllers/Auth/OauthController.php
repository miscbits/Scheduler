<?php
namespace App\Http\Controllers\Auth;

use Auth;
use App\User;
use Socialite;
use App\Http\Controllers\Controller;

class OAuthController extends Controller
{

    protected $redirectTo = '/home';

    /**
     * Redirect the user to the OAuth Provider.
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that 
     * redirect them to the authenticated users homepage.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect($this->redirectTo);
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser($user, $provider)
    {
        $prop = $provider . '_id';
        $authUser = User::where('email', $user->email)->first();
        if ($authUser) {
            if($authUser->$prop != $user->id) {
                $authUser->$prop = $user->id;
                $authUser->save();
            }
            return $authUser;
        }

        return User::create([
            'name'     => $user->name,
            'email'    => $user->email,
            $prop => $provider,
        ]);
    }
}