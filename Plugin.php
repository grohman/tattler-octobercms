<?php namespace Grohman\Tattler;


use Backend\Facades\BackendAuth;
use Grohman\Tattler\Lib\Tattler;
use Tattler\Base\Channels\IRoom;
use Tattler\Objects\TattlerConfig;

use System\Classes\PluginBase;
use System\Classes\SettingsManager;

use Grohman\Tattler\Models\Settings;
use Illuminate\Support\Facades\Event;


/**
 * Tattler Plugin Information File
 */
class Plugin extends PluginBase
{
    /** @var Tattler $tattler */
    private $tattler;

    /** @var Settings $config */
    private $config;


    /**
     * @param Settings $config
     * @return void
     */
    private function initTattler($config)
    {
        $tattlerConfig = new TattlerConfig();
        $tattlerConfig->fromArray($config);

        $this->tattler = Tattler::instance();
        $this->tattler->setConfig($tattlerConfig);

        $this->config = $tattlerConfig;
    }

    private function initEvents()
    {
        Event::listen('backend.list.extendColumns', function ($widget) {
            $this->inject($widget);
        });

        Event::listen('backend.form.extendFields', function ($widget) {
            $this->inject($widget);
        });
    }

    /**
     * @param $widget
     */
    private function inject($widget)
    {
        if (isset($widget->model) && method_exists($widget->model, 'isClassExtendedWith')) {
            $user = $this->tattler->getBackendUser(BackendAuth::getUser());

            if ($widget->model->isClassExtendedWith('\Grohman\Tattler\Lib\Inject') == false) {
                $widget->model->extendClassWith('\Grohman\Tattler\Lib\Inject');
            }

            if (method_exists($widget, 'getColumns')) {
                $columns = $widget->model->getWidgetColumns($widget->getColumns());
            } else {
                $columns = $widget->model->getWidgetColumns();
            }

            $rooms = Tattler::instance()->getTattler()->getDefaultChannels($user);

            if ($columns) {
                /** @var IRoom $room */
                $room = $widget->model->getRoom();
                $room->allow($user);
                $rooms[] = $room->getName();
            }

            $this->loadAssets($widget, $rooms);
        }
    }

    /**
     * @param $widget
     * @param $rooms
     */
    private function loadAssets($widget, array $rooms = [])
    {
        $widget->addCss('https://cdn.jsdelivr.net/jquery.gritter/1.7.4/css/jquery.gritter.css');

        $widget->addJs('https://cdn.jsdelivr.net/jquery.gritter/1.7.4/js/jquery.gritter.min.js');
        $widget->addJs('https://cdn.socket.io/socket.io-1.4.5.js');
        $widget->addJs('/plugins/grohman/tattler/assets/js/tattler.min.js');

        $widget->addJs('/plugins/grohman/tattler/assets/js/crud_handlers.js');

        $widget->addJs('/plugins/grohman/tattler/assets/js/init.js',
            ['id' => 'tattlerJs', 'data-debug' => config()->get('app.debug'), 'data-rooms' => json_encode($rooms)]);
    }


    /**
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Tattler',
            'description' => 'OctoberCMS Tattler plugin',
            'author'      => 'Daniel Podrabinek',
            'icon'        => 'icon-leaf',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        /** @var Settings $settings */
        $config = Settings::getConfig();

        if ($config)
        {
            $this->initTattler($config);
            $this->initEvents();
        }
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Grohman\Tattler\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'grohman.tattler.some_permission' => [
                'tab' => 'tattler',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'tattler' => [
                'label'       => 'Tattler plugin',
                'description' => 'Configure Tattler settings',
                'category'    => SettingsManager::CATEGORY_CMS,
                'class'       => Settings::class,
                'icon'        => 'icon-cog',
                'permissions' => ['grohman.tattler.*'],
                'order'       => 500,
            ],
        ];
    }

}
