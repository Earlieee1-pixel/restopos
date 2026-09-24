<?php

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Ari ang pag-manage sa mga mesa
class TableController extends Controller
{
    // Tanan mesa ug ilang status
    public function index(): JsonResponse
    {
        $tables = Table::with('activeOrder')->orderBy('table_number')->get();
        return response()->json($tables);
    }

    // Bag-ong mesa
    public function store(Request $request): JsonResponse
    {
        $table = Table::create($request->validate([
            'table_number' => ['required', 'integer', 'unique:tables'],
            'capacity'     => ['required', 'integer', 'min:1'],
            'floor'        => ['nullable', 'string'],
        ]));

        return response()->json($table, 201);
    }

    // I-update ang status sa mesa (available, occupied, reserved)
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $table = Table::findOrFail($id);
        $table->update($request->validate([
            'status' => ['required', 'in:available,occupied,reserved'],
        ]));

        return response()->json($table);
    }
}
