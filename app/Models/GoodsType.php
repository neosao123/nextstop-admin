<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsType extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_name',
        'is_active',
        'is_delete',
    ];
	
	  public static function filterGoodsType(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('goods_types.*')
            ->where('goods_types.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('goods_types.id', 'like', "%{$searchTerm}%");
                $query->orWhere('goods_types.goods_name', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('goods_types.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
