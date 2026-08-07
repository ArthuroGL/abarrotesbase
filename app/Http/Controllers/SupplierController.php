<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Obtiene el ID de la organización del usuario o una por defecto.
     */
    private function getOrganizationId(): string
    {
        return auth()->user()->organization_id
            ?? DB::table('organizations')->value('id')
            ?? throw new \Exception('No hay una organización registrada en el sistema.');
    }

    public function index(Request $request)
    {
        $orgId = $this->getOrganizationId();
        $search = $request->input('search');
        $status = $request->input('status');

        $suppliers = Supplier::where('organization_id', $orgId)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('business_name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%")
                        ->orWhere('rfc', 'ilike', "%{$search}%")
                        ->orWhere('contact_name', 'ilike', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === 'active');
            })
            ->orderBy('business_name')
            ->paginate(12)
            ->withQueryString();

        return view('modules.suppliers.index', compact('suppliers', 'search', 'status'));
    }

    public function create()
    {
        return view('modules.suppliers.create');
    }

    public function store(Request $request)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('suppliers')->where(fn ($q) => $q->where('organization_id', $orgId)->whereNull('deleted_at')),
            ],
            'business_name' => 'required|string|max:255',
            'rfc' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('suppliers')->where(fn ($q) => $q->where('organization_id', $orgId)->whereNotNull('rfc')->whereNull('deleted_at')),
            ],
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'payment_terms_days' => 'required|integer|min:0|max:365',
        ]);

        // Autogenerar código si viene vacío (ej. PRV-0001)
        if (empty($validated['code'])) {
            $nextNumber = Supplier::where('organization_id', $orgId)->count() + 1;
            $validated['code'] = 'PRV-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
        }

        Supplier::create([
            ...$validated,
            'organization_id' => $orgId,
            'is_active' => true,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $orgId = $this->getOrganizationId();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('suppliers')->where(fn ($q) => $q->where('organization_id', $orgId)->whereNull('deleted_at'))->ignore($supplier->id),
            ],
            'business_name' => 'required|string|max:255',
            'rfc' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('suppliers')->where(fn ($q) => $q->where('organization_id', $orgId)->whereNotNull('rfc')->whereNull('deleted_at'))->ignore($supplier->id),
            ],
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'is_active' => 'required|boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
