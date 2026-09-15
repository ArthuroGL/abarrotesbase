<?php

namespace App\Http\Controllers;

use App\Modules\Identity\Application\Services\CurrentContext;
use App\Modules\Operation\Infrastructure\Persistence\Models\Expense;
use App\Modules\Operation\Infrastructure\Persistence\Models\Sale;
use App\Modules\Operation\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Inventory\Infrastructure\Persistence\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController
{
    public function __construct(
        private readonly CurrentContext $context
    ) {}

    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q'));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'results' => [],
            ]);
        }

        $orgId = $this->context->organizationId();
        $branchId = $this->context->branchId();

        $results = [];

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->where('organization_id', $orgId)
            ->where(function ($query) use ($term) {
                $query
                    ->where('name', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%");
            })
            ->limit(5)
            ->get();

        foreach ($products as $product) {
            $results[] = [
                'type' => 'product',
                'group' => 'Productos',
                'title' => $product->name,
                'subtitle' => 'SKU: ' . $product->sku,
                'url' => route('products.show', $product),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Proveedores
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->where('organization_id', $orgId)
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query
                    ->where('business_name', 'ilike', "%{$term}%")
                    ->orWhere('rfc', 'ilike', "%{$term}%");
            })
            ->limit(5)
            ->get();

        foreach ($suppliers as $supplier) {
            $results[] = [
                'type' => 'supplier',
                'group' => 'Proveedores',
                'title' => $supplier->business_name,
                'subtitle' => $supplier->rfc ?? 'Proveedor',
                'url' => route('suppliers.show', $supplier),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Gastos
        |--------------------------------------------------------------------------
        */

        $expenses = Expense::query()
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($term) {
                $query
                    ->where('expense_number', 'ilike', "%{$term}%")
                    ->orWhere('beneficiary', 'ilike', "%{$term}%")
                    ->orWhere('source_name', 'ilike', "%{$term}%")
                    ->orWhere('reference', 'ilike', "%{$term}%");
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($expenses as $expense) {
            $results[] = [
                'type' => 'expense',
                'group' => 'Gastos',
                'title' => $expense->expense_number,
                'subtitle' => $expense->beneficiary ?: $expense->description,
                'url' => route('expenses.show', $expense),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Ventas
        |--------------------------------------------------------------------------
        */

        $sales = Sale::query()
            ->where('organization_id', $orgId)
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($term) {
                $query
                    ->where('sale_number', 'ilike', "%{$term}%");
            })
            ->latest()
            ->limit(5)
            ->get();

        foreach ($sales as $sale) {
            $results[] = [
                'type' => 'sale',
                'group' => 'Ventas',
                'title' => $sale->sale_number,
                'subtitle' => '$' . number_format($sale->total, 2),
                'url' => route('sales.show', $sale),
            ];
        }


        return response()->json([
            'results' => $results,
        ]);
    }
}
