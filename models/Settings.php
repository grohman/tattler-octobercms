<?php namespace Grohman\Tattler\Models;


use Illuminate\Support\Facades\Cache;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Validation;


/**
 * Settings Model
 * @property string $Server
 * @property string $Secure
 * @property string $Namespace
 */
class Settings extends Model
{
    use Validation;

    private static $cacheIdx = 'tattler:config';

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'tattler';

    public $settingsFields = 'fields.yaml';

    protected $rules = [
        'Server'    => 'string|required',
        'Port'      => 'integer|required',
        'Secure'    => 'boolean|required',
        'Namespace' => 'string|required',
    ];

    public function afterSave()
    {
        $data = $this->toArray();
        $config = json_encode($data['value']);
        return Cache::forever(self::$cacheIdx, $config);
    }

    public static function getConfig()
    {
        return json_decode(Cache::get(self::$cacheIdx));
    }
}