<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function uploadLogo(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        try {
            // 🔥 eliminar logo anterior
            if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
                Storage::disk('public')->delete($tenant->logo);
            }

            // 🔥 nombre personalizado
            $extension = $request->file('logo')->getClientOriginalExtension();
            $filename = 'tenant_' . $tenant->id . '.' . $extension;

            $path = $request->file('logo')->storeAs('tenants', $filename, 'public');

            $tenant->update([
                'logo' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logo actualizado correctamente',
                'data' => [
                    'logo' => $path,
                    'url' => asset('storage/' . $path)
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:tenants,slug,' . $tenant->id,
        ]);

        $tenant->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant actualizado correctamente',
            'data' => $tenant
        ]);
    }
}
