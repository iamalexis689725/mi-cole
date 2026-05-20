<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    private function buildLogoUrl($path)
    {
        if (!$path) return null;

        return request()->getSchemeAndHttpHost() . '/storage/' . $path;
    }

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

            $modules = Module::all();

            foreach ($modules as $module) {
                $tenant->modules()->attach($module->id, [
                    'activo' => false
                ]);
            }

            DB::commit();

            return response()->json([
                'tenant' => $tenant,
                'logo_url' => null,
                'director' => $director
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $tenants = Tenant::all();

        $tenants->map(function ($tenant) {
            $tenant->logo_url = $this->buildLogoUrl($tenant->logo);
            return $tenant;
        });

        return response()->json($tenants);
    }

    public function show($id)
    {
        $tenant = Tenant::findOrFail($id);

        return response()->json([
            'data' => $tenant,
            'logo_url' => $this->buildLogoUrl($tenant->logo)
        ]);
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
            Storage::disk('public')->delete($tenant->logo);
        }

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant eliminado correctamente'
        ]);
    }

    public function myTenant()
    {
        $tenant = auth()->user()->tenant;

        return response()->json([
            'data' => $tenant,
            'logo_url' => $this->buildLogoUrl($tenant->logo)
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $tenant = auth()->user()->tenant;

        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        // eliminar anterior
        if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
            Storage::disk('public')->delete($tenant->logo);
        }

        $extension = $request->file('logo')->getClientOriginalExtension();
        $filename = 'tenant_' . $tenant->id . '.' . $extension;

        $path = $request->file('logo')->storeAs('tenants', $filename, 'public');

        $tenant->update([
            'logo' => $path
        ]);

        return response()->json([
            'success' => true,
            'data' => $tenant,
            'logo_url' => $this->buildLogoUrl($path)
        ]);
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
            'data' => $tenant,
            'logo_url' => $this->buildLogoUrl($tenant->logo)
        ]);
    }
}