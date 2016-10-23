<?php namespace Grohman\Tattler\Lib;


use Tattler\Common;
use Tattler\Base\Channels\IRoom;
use Tattler\Base\Objects\ITattlerMessage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use Backend\Facades\BackendAuth;
use October\Rain\Extension\ExtensionBase;

use Exception;
use Carbon\Carbon;


class Inject extends ExtensionBase
{
    /** @var string $target */
    protected $target;


    private function setEvents()
    {
        $tattler = Tattler::instance()->getTattler()->room($this->getRoom());
        $target = $this->target;

        Event::listen('eloquent.created:*', function ($model) use ($tattler, $target) {
            if (get_class($model) == get_class($target)) {
                $tattler->message($this->generateMessage($model, 'crud_create'))->say();
            }
        });

        Event::listen('eloquent.updated:*', function ($model) use ($tattler, $target) {
            if (get_class($model) == 'RainLab\User\Models\User' && $this->getUserInfo()['id'] == null) {
                // auth notifications. Who cares...
                return;
            }

            if (get_class($model) == get_class($target)) {
                $tattler->message($this->generateMessage($model, 'crud_update'))->say();
            }
        });

        Event::listen('eloquent.deleted:*', function ($model) use ($tattler, $target) {
            if (get_class($model) == get_class($target)) {
                $tattler->message($this->generateMessage($model, 'crud_delete'))->say();
            }
        });
    }


    /**
     * @param $target
     */
    public function __construct($target)
    {
        $this->target = $target;
        $this->setEvents();
    }


    /**
     * @param $model
     * @param $handler
     * @return ITattlerMessage
     */
    private function generateMessage($model, $handler)
    {
        try {
            $message = [ ];

            $columns = $this->target->getWidgetColumns(); // dynamic method handled by Plugin

            if(!$columns)
            {
                $columns = array_keys($model->toArray());
            }

            $modelData = $model->toArray();

            foreach ($columns as $column => $name) {
                if (isset($modelData[ $column ]) && is_object($model[ $column ]) == false && is_array($model[ $column ]) == false && $modelData[ $column ] != '') {
                    $message[ $column ] = $modelData[ $column ];
                }
            }

            $payload = [
                'row_id' => $model->getKey(),
                'row_key' => $model->getKeyName(),
                'by' => $this->getUserInfo(),
                'at' => Carbon::now(),
                'columns' => $columns,
                'row_data' => $message
            ];

            /** @var ITattlerMessage $message */
            $message = Common::skeleton(ITattlerMessage::class);
            $message->setHandler($handler)->setPayload($payload);

            return $message;

        } catch(Exception $e){
            if(config()->get('app.debug') == 1) {
                Log::error('Tattler::collectMessageBag -> ' . $e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    private function getUserInfo()
    {
        if(BackendAuth::getUser()) {
            $user = BackendAuth::getUser();

            return [ 'id' => $user->getKey(), 'name' => $user[ 'first_name' ] . ' ' . $user[ 'last_name' ], ];
        }

        return ['id' => null, 'name' => 'Anonymous'];
    }


    /**
     * @return IRoom
     */
    public function getRoom()
    {
        $room = Common::skeleton(IRoom::class);
        $room->setName(get_class($this->target));
        return $room;
    }

    /**
     * @return string
     */
    public function getCacheIdx()
    {
        return 'tattler:models:' . get_class($this->target) . ':' . app()->getLocale();
    }

    /**
     * @param null $columns
     * @return array
     */
    public function getWidgetColumns($columns = null)
    {
        if($columns) {
            return Cache::remember($this->getCacheIdx(), 1440, function () use ($columns) {
                $result = [ ];
                foreach ($columns as $column => $col) {
                    $result[ $column ] = trans($col->label);
                }

                return $result;
            });
        } else {
            return Cache::get($this->getCacheIdx());
        }
    }

    /**
     * @return bool
     */
    public function forgetWidgetColumns()
    {
        return Cache::forget($this->getCacheIdx());
    }
}