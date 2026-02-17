<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
	protected $fillable = [
		'service_name',
		'service_icon',
		'service_description',
		'is_active',
		'is_delete',
		'created_at',
		'updated_at',
	];
	
	public static function filterServices(string $searchTerm = "", int $limit = 0, int $skip = 0)
	{
		$query = self::where('is_delete', 0);

		// Apply search filter if provided
		if ($searchTerm) {
			$query->where(function ($query) use ($searchTerm) {
				$query->where('service_name', 'like', "%{$searchTerm}%");
					
			});
		}

		// Get the total count of records before applying pagination
		$total = $query->count();

		// Apply pagination if limit is provided
		if ($limit > 0) {
			$query->limit($limit)->offset($skip);
		}

		$result = $query->orderBy('id', 'DESC')->get();

		return ["totalRecords" => $total, "result" => $result];
	}

}
