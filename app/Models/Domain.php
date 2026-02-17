<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;
use Illuminate\Support\Facades\DB;


class Domain extends BaseDomain
{
    use HasFactory;

    public static function filterData($search, $limit, $skip)
    {
        $query = DB::table('domains')->join('tenants', 'tenants.id', '=', 'domains.tenant_id');
        if ($search) {
            $query->where('tenants.data->company_name', 'LIKE', "%$search%")->orWhere('tenants.data->domain', 'LIKE', "%$search%")->orWhere('domains.tenant_id', 'LIKE', "%$search%");
        }

        $totalQuery = $query->count();

        $result = $query->limit($limit)->skip($skip)->get();

        DB::enableQueryLog();

        return ["totalRecords" => $totalQuery, "data" => $result];
    }
}
