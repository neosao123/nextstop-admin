<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocumentDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'document_type',
        'document_number',
        'document_1_file_type',
        'document_2_file_type',
        'document_1',
        'document_2',
        'document_verification_status',
        'document_uploaded_at',
        'document_verified_by',
        'document_verified_at',
        'is_active',
        'is_delete',
    ];

    /**
     * Summary of driver
     * @author shreyasm@noesao
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }


    /**
     * Summary of driver Document Details
     * @author shreyasm@neosao
     */

    public static function filterDriverDocumentDetails(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('driver_document_details.*')
            ->with('Driver')
            ->where('driver_document_details.is_delete', 0);

        // Apply search filters
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('driver_document_details.document_number', 'like', "%{$searchTerm}%")
                    ->orWhereHas('Driver', function ($query) use ($searchTerm) {
                        $query->where('drivers.first_name', 'like', "%{$searchTerm}%")
                            ->orWhere('drivers.last_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Get total count of records
        $total = $query->count();

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the filtered results
        $result = $query->orderBy('driver_document_details.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }
}
