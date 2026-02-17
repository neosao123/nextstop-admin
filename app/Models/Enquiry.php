<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;
	
	
	//filter enquiry function which is used for list
	 public static function filterEnquiry(string $search = "", $limit = 0, $offset = 0)
    {
        $query = self::select("enquiries.*");
			$query->where(function ($query) use ($search) {
				$query->where('name', 'like', "%{$search}%")
					 ->orWhere('email', 'like', "%{$search}%")
					->orWhere('contactno', 'like', "%{$search}%")
					->orWhere('subject', 'like', "%{$search}%")
					->orWhere('message', 'like', "%{$search}%");
			})
            ->orderBy('enquiries.id', 'DESC');

        $total = $query->count();
        if ($limit && $limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        $result = $query->get();
        return ["totalRecords" => $total, "result" => $result];
    }
}
