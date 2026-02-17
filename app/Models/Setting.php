<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
	
	protected $fillable = [
        'setting_name',
		'setting_value',
        'is_active',
        'is_delete',
		'is_update_compulsory'
    ];

	 public static function filterSetting(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('settings.*')
            ->where('settings.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('settings.id', 'like', "%{$searchTerm}%");
				$query->orWhere('settings.setting_value', 'like', "%{$searchTerm}%");
                $query->orWhere('settings.setting_name', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit && $limit > 0 && $skip && $skip > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('settings.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
