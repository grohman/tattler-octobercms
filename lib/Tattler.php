<?php
namespace Grohman\Tattler\Lib;


use Backend\Models\User;
use Objection\TSingleton;
use Tattler\Base\Channels\IUser;
use Tattler\Base\Modules\ITattler;
use Tattler\Common;
use Tattler\Objects\TattlerConfig;


class Tattler
{
	use TSingleton;
	
	
	/** @var ITattler $tattler */
	private $tattler;
	
	
	protected static function initialize(Tattler $instance)
	{
		$instance->setTattler(Common::skeleton(ITattler::class));
	}
	
	
	/**
	 * @return ITattler
	 */
	public function getTattler()
	{
		if ($this->tattler == null)
		{
			throw new \Exception('Tattler not initialized');
		}
		
		return $this->tattler;
	}
	
	
	/**
	 * @param ITattler $tattler
	 */
	public function setTattler(ITattler $tattler)
	{
		$this->tattler = $tattler;
	}
	
	/**
	 * @param User $backendUser
	 * @return IUser
	 */
	public function getBackendUser(User $backendUser)
	{
		/** @var IUser $user */
		$user = Common::skeleton(IUser::class);
		$user->setName('backend:' . $backendUser->id);
		
		return $user;
	}
	
	public function getFrontendUser(\Rainlab\User\Models\User $frontendUser)
	{
		/** @var IUser $user */
		$user = Common::skeleton(IUser::class);
		$user->setName('frontend:' . $frontendUser->id);
		
		return $user;
	}
	
	
	/**
	 * @param TattlerConfig $config
	 * @return void
	 */
	public function setConfig(TattlerConfig $config)
	{
		$this->tattler->setConfig($config);
		
		return;
	}
}