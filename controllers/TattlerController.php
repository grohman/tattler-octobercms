<?php
namespace Grohman\Tattler\Controllers;


use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Firebase\JWT\JWT;
use Grohman\Tattler\Lib\Tattler;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tattler\Base\Modules\ITattler;


class TattlerController extends Controller
{
    /** @var ITattler $tattler */
    private $tattler;


    public function __construct()
    {
        $this->tattler = Tattler::instance()->getTattler();
    }


    public function getWs()
    {
        return ['ws' => $this->tattler->getWsAddress()];
    }

    public function getChannels(Request $request)
    {
        $socketId = $request->get('socketId');
        $channels = $request->get('channels');

        /** @var User $backendUser */
        $backendUser = BackendAuth::getUser();

        if ($backendUser)
        {
            $user = Tattler::instance()->getBackendUser($backendUser);
            $user->setSocketId($socketId);

            $this->tattler->setUser($user);
        }

        return ['channels' => $this->tattler->getChannels($channels)];
    }
    
    public function getAuth()
    {
	    /** @var User $backendUser */
	    $backendUser = BackendAuth::getUser();
	
	    if ($backendUser)
	    {
	    	$payload = ['is_backend' => true, 'userId' => $backendUser->id];
	    }
	    else
	    {
	    	$payload = ['is_backend' => false];
	    }
	    
	    return ['token' => JWT::encode($payload, $this->tattler->getJWTSecret())];
    }
}