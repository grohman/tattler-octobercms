<?php


use Tattler\Common;
use Tattler\Channels\Broadcast;
use Tattler\Base\Channels\IRoom;
use Tattler\Base\Objects\ITattlerMessage;

use Grohman\Tattler\Lib\Tattler;
use Illuminate\Support\Facades\Queue;


class TattlerHelper
{
    private $wrapper;

    private $data = [];


    /**
     * @return void
     */
    private function testTarget()
    {
        if(!isset($data['target']))
        {
            $data['target'] = [];
        }

        if(!isset($data['target']['users']))
        {
            $data['target']['users'] = [];
        }

        if(!isset($data['target']['rooms']))
        {
            $data['target']['rooms'] = [];
        }

        return;
    }


    public function __construct()
    {
        $this->wrapper = Tattler::instance();
    }


    /**
     * @param string $handler
     * @param array $payload
     * @return static
     */
    public function message($handler, $payload)
    {
        $this->data['handler'] = $handler;
        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * @param $user
     * @return static
     */
    public function to($user)
    {
        $this->testTarget();

        if($user instanceof \Backend\Models\User)
        {
            $result = $this->wrapper->getBackendUser($user);
        } else if($user instanceof \Rainlab\User\Models\User)
        {
	        $result = $this->wrapper->getFrontendUser($user);
        }
        else
        {
        	throw new \Exception('User instance class unsupported');
        }
        
        $this->data['target']['users'][] = $result;

        return $this;
    }

    /**
     * @param string $roomName
     * @return static
     */
    public function room($roomName)
    {
        $this->testTarget();

        /** @var IRoom $room */
        $room = Common::skeleton(IRoom::class);
        $room->setName($roomName);

        $this->data['target']['rooms'][] = $room;

        return $this;
    }

    /**
     * @return static
     */
    public function broadcast()
    {
        $this->testTarget();

        $this->data['target']['rooms'] = Broadcast::class;

        return $this;
    }

    /**
     * @return bool
     */
    public function say()
    {
        Queue::push('TattlerHelper@queue', $this->data);
        $this->data = [];

        return true;
    }
	
	/**
	 * @param mixed $job
	 * @param array $data
	 * @return bool
	 */
    public function queue($job, $data)
    {
        $tattler = $this->wrapper->getTattler();

        $receiversCount = 0;

        if (isset($data['target'])) {
            foreach ($data['target']['users'] as $user) {
                $tattler->user($user);
                $receiversCount++;
            }

            foreach ($data['target']['rooms'] as $room) {
                $tattler->room($room);
                $receiversCount++;
            }

        }

        if($receiversCount==0)
        {
            $tattler->broadcast();
        }

        /** @var ITattlerMessage $message */
        $message = Common::skeleton(ITattlerMessage::class);
        $message->setHandler($data['handler'])->setPayload($data['payload']);

        $result = $tattler->message($message)->say();

        $job->delete();

        return $result;
    }
}


function tattler()
{
    return new TattlerHelper();
}