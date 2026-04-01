<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:tenants',
            'director_name' => 'required',
            'director_email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $tenant = Tenant::create([
                'name' => $request->name,
                'slug' => $request->slug,
            ]);

            $director = User::create([
                'name' => $request->director_name,
                'email' => $request->director_email,
                'password' => bcrypt($request->password),
                'tenant_id' => $tenant->id
            ]);

            $director->assignRole('director');

            DB::commit();

            return response()->json([
                'tenant' => $tenant,
                'director' => $director
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
