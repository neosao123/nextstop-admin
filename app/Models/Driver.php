<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Rating;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // Driver Model
    protected $fillable = [
        'driver_first_name',
        'driver_last_name',
        'driver_phone',
        'driver_email',
        'driver_gender',
        'driver_otp',
        'driver_document_verification_status',
        'driver_vehicle_verification_status',
        'driver_vehicle_document_verification_status',
        'driver_training_video_verification_status',
        'driver_status',
        'driver_serviceable_location',
        'driver_photo',
        'is_driver_delete',
        'is_driver_block',
        'is_active',
        'is_delete',
        'admin_verification_status',
        'admin_verification_reason',
        'driver_firebase_token',
        'driver_wallet',
    ];

    /**
     * Summary of vehicle Details
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author shreyasm@neosao
     */
    public function vehicleDetails()
    {
        return $this->hasOne(DriverVehicleDetails::class, 'driver_id');
    }

    /**
     * Documents relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author shreyasm@neosao
     */
    public function documents()
    {
        return $this->hasMany(DriverDocumentDetails::class, 'driver_id', 'id');
    }

    /**
     * Servicable Zones relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author shreyasm@neosao
     */
    public function serviceableZones()
    {
        return $this->belongsTo(ServiceableZone::class, 'driver_serviceable_location');
    }


    /**
     * Summary of rating
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     * @author seemashelar@neosao
     */
    public function rating()
    {
        return $this->hasMany(Rating::class, 'rating_driver_id')->where('rating_given_by', 'customer');
    }

    /**
     * Filter driver Information For Datatable List
     * @param string $searchTerm
     * @param int $limit
     * @param int $skip
     * @return array
     * @author shreyasm@neosao
     */
    public static function filterPendingDriver(string $searchTerm = "", int $limit = 0, int $skip = 0,  array $filterArray = [])
    {
        // Start query with relationships
        $query = self::with('vehicleDetails.vehicle', 'serviceableZones')
            ->where('drivers.is_delete', 0)
            ->where(function ($query) {
                // Conditions for pending verifications
                $query->orWhere('drivers.driver_document_verification_status', '!=', 1)
                    ->orWhere('drivers.driver_vehicle_verification_status', '!=', 1)
                    ->orWhere('drivers.driver_vehicle_document_verification_status', '!=', 1)
                    ->orWhere('drivers.admin_verification_status', '!=', 1)
                    ->orWhere('drivers.driver_training_video_verification_status', '!=', 1);
            });

        // Apply search filter if provided
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('drivers.driver_first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_wallet', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_phone', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_email', 'like', "%{$searchTerm}%");

                // Search through related vehicle details (assuming 'vehicleDetails' and 'vehicle' are properly defined)
                $query->orWhereHas('vehicleDetails', function ($query) use ($searchTerm) {
                    $query->whereHas('vehicle', function ($query) use ($searchTerm) {
                        $query->where('vehicle_name', 'like', "%{$searchTerm}%");
                    });
                });

                // Search through related serviceable zones (assuming 'serviceableZones' is properly defined)
                $query->orWhereHas('serviceableZones', function ($query) use ($searchTerm) {
                    $query->where('serviceable_zone_name', 'like', "%{$searchTerm}%");
                });
            });
        }

        if (!empty($filterArray)) {
            // Filter By Name ( First Name & Last Name)
            if ($filterArray['search_name'] != '') {
                $query->where(function ($query) use ($filterArray) {
                    $searchName = $filterArray['search_name'];
                    $query->whereRaw("CONCAT(drivers.driver_first_name, ' ', drivers.driver_last_name) LIKE ?", ["%{$searchName}%"]);
                });
            }
            // Filter By Vehicle
            if ($filterArray['search_driver_vehicle'] != '') {
                $vehicle_id = $filterArray['search_driver_vehicle'];
                $query->whereHas('vehicleDetails', function ($query) use ($vehicle_id) {
                    $query->whereHas('vehicle', function ($query) use ($vehicle_id) {
                        $query->where('id', $vehicle_id);
                    });
                });
            }
            // Filter By Serviceable Location
            if ($filterArray['search_driver_serviceable_location'] != '') {
                $location_id = $filterArray['search_driver_serviceable_location'];
                $query->whereHas('serviceableZones', function ($query) use ($location_id) {
                    $query->where('id', $location_id);
                });
            }
            // Filter By Account Status
            if ($filterArray['search_account_status'] != '') {
                $account_status = $filterArray['search_account_status'] === 'active' ? 1 : 0;
                $query->where('is_active', $account_status);
            }

            // Filter By Account Status
            if (!empty($filterArray['search_verification_status_type']) && $filterArray['search_verification_status'] != '') {
                $type = $filterArray['search_verification_status_type'];
                $status = null;

                // Determine the status based on the search_verification_status value
                switch ($filterArray['search_verification_status']) {
                    case 'pending':
                        $status = 0;
                        break;
                    case 'verified':
                        $status = 1;
                        break;
                    case 'rejected':
                        $status = 2;
                        break;
                }

                // If status is not set, don't proceed
                if ($status === null) {
                    return;
                }

                // Initialize an array to store dynamic conditions
                $conditions = [];

                // Map types to the corresponding verification fields
                $verificationFields = [
                    'document' => 'driver_document_verification_status',
                    'vehicle' => 'driver_vehicle_verification_status',
                    'training-video' => 'driver_training_video_verification_status',
                ];

                // Loop through types and add corresponding conditions
                foreach ($type as $item) {
                    if (isset($verificationFields[$item])) {
                        $conditions[] = $verificationFields[$item];
                    }
                }

                // Apply conditions to the query
                foreach ($conditions as $field) {
                    $query->where($field, $status);
                }
            }
        }

        // Get the total count of records before applying pagination
        $total = $query->count();

        // Apply pagination if limit is provided
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the filtered results and order by ID
        $result = $query->orderBy('drivers.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }


    /**
     * Filter Delivery Verified Partner Information For Datatable List
     * @param string $searchTerm
     * @param int $limit
     * @param int $skip
     * @return array
     * @author shreyasm@neosao
     */
    public static function filterVerifiedDriver(string $searchTerm = "", int $limit = 0, int $skip = 0, array $filterArray = [])
    {
        $query = self::with('vehicleDetails.vehicle', 'serviceableZones')
            ->where('driver_document_verification_status', 1)
            ->where('driver_vehicle_verification_status', 1)
            ->where('driver_training_video_verification_status', 1)
            ->where('admin_verification_status', 1);
        if (isset($filterArray['search_account_status']) && !empty($filterArray['search_account_status'])) {
            if ($filterArray['search_account_status'] === 'active') {
                $query->where('is_delete', 0);
            } elseif ($filterArray['search_account_status'] === 'inactive') {
                $query->where('is_active', 0)->where('is_delete', 0);
            } elseif ($filterArray['search_account_status'] === 'delete') {
                $query->where('is_driver_delete', 1);
            }
        } else {
            $query->where('is_delete', 0);
        }
        // Apply search filters
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('drivers.driver_first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_wallet', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_phone', 'like', "%{$searchTerm}%")
                    ->orWhere('drivers.driver_email', 'like', "%{$searchTerm}%");

                // Search through related vehicle details (assuming 'vehicleDetails' and 'vehicle' are properly defined)
                $query->orWhereHas('vehicleDetails', function ($query) use ($searchTerm) {
                    $query->whereHas('vehicle', function ($query) use ($searchTerm) {
                        $query->where('vehicle_name', 'like', "%{$searchTerm}%");
                    });
                });

                // Search through related serviceable zones (assuming 'serviceableZones' is properly defined)
                $query->orWhereHas('serviceableZones', function ($query) use ($searchTerm) {
                    $query->where('serviceable_zone_name', 'like', "%{$searchTerm}%");
                });
            });
        }

        if (!empty($filterArray)) {
            // Filter By Name ( First Name & Last Name)
            if ($filterArray['search_name'] != '') {
                $query->where(function ($query) use ($filterArray) {
                    $searchName = $filterArray['search_name'];
                    $query->whereRaw("CONCAT(drivers.driver_first_name, ' ', drivers.driver_last_name) LIKE ?", ["%{$searchName}%"]);
                });
            }
            // Filter By Vehicle
            if ($filterArray['search_driver_vehicle'] != '') {
                $vehicle_id = $filterArray['search_driver_vehicle'];
                $query->whereHas('vehicleDetails', function ($query) use ($vehicle_id) {
                    $query->whereHas('vehicle', function ($query) use ($vehicle_id) {
                        $query->where('id', $vehicle_id);
                    });
                });
            }
            // Filter By Serviceable Location
            if ($filterArray['search_driver_serviceable_location'] != '') {
                $location_id = $filterArray['search_driver_serviceable_location'];
                $query->whereHas('serviceableZones', function ($query) use ($location_id) {
                    $query->where('id', $location_id);
                });
            }
            // Filter By Account Status
            if ($filterArray['search_account_status'] != '') {
                if ($filterArray['search_account_status'] === 'active') {
                    $query->where('is_active', 1);
                } elseif ($filterArray['search_account_status'] === 'inactive') {
                    $query->where('is_active', 0);
                } elseif ($filterArray['search_account_status'] === 'delete') {
                    $query->where('is_driver_delete', 1); // Assuming 1 means deleted
                }
            }
        }

        // Get total count of records
        $total = $query->count();

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($skip);
        }

        // Get the filtered results
        $result = $query->orderBy('drivers.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }


    //filter customer rating function which is used for list
    public static function filterRating(string $search = "", int $limit = 0, int $offset = 0, string $customer = "", string $trip = "", string $driver = "")
    {
        $query = Rating::select(
            'trips.trip_unique_id',
            'ratings.*',
            'customers.customer_first_name as customer_first_name',
            'customers.customer_last_name as customer_last_name',
            'drivers.driver_first_name as driver_first_name',
            'drivers.driver_last_name as driver_last_name'
        )
            ->join("customers", "customers.id", "=", "ratings.rating_customer_id")
            ->join("trips", "trips.id", "=", "ratings.rating_trip_id")
            ->join("drivers", "drivers.id", "=", "ratings.rating_driver_id")
            ->where('ratings.is_delete', 0)
            ->where('ratings.rating_given_by', 'customer');

        if (!empty($customer)) {
            $query->where('ratings.rating_customer_id', $customer);
        }

        if (!empty($trip)) {
            $query->where('ratings.rating_trip_id', $trip);
        }

        if (!empty($driver)) {
            $query->where('ratings.rating_driver_id', $driver);
        }

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('customers.customer_first_name', 'like', "%{$search}%")
                    ->orWhere('customers.customer_last_name', 'like', "%{$search}%")
                    ->orWhere('drivers.driver_first_name', 'like', "%{$search}%")
                    ->orWhere('drivers.driver_last_name', 'like', "%{$search}%")
                    ->orWhere('trips.trip_unique_id', 'like', "%{$search}%")
                    ->orWhere('ratings.rating_value', 'like', "%{$search}%")
                    ->orWhere('ratings.rating_description', 'like', "%{$search}%");
            });
        }

        $query->orderBy('ratings.id', 'DESC');

        $total = $query->count();

        if ($limit > 0) {
            $query->limit($limit)->offset($offset);
        }

        $result = $query->get();

        return [
            "totalRecords" => $total,
            "result" => $result,
        ];
    }

    public function earnings()
    {
        return $this->hasMany(DriverEarning::class, 'driver_id', 'id');
    }
}
