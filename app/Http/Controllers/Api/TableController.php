<?php

namespace App\Http\Controllers\Api;
use App\Models\Table;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TableResource;
use App\Http\Requests\StoreTableRequest;

class TableController extends Controller
{
    public function index()
{
    $tables = Table::latest()->get();

    return response()->json([
        'success' => true,
        'data' => $tables
    ]);
}

public function store(StoreTableRequest $request)
{
    $table = Table::create($request->validated());

    return response()->json([
        'success' => true,
        'message' => 'Table created successfully',
        'data' => new TableResource($table)
    ], 201);
}

public function show($id)
{
    $table = Table::find($id);

    if (!$table) {
        return response()->json([
            'success' => false,
            'message' => 'Table not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $table
    ]);
}

public function update(Request $request, $id)
{
    $table = Table::find($id);

    if (!$table) {
        return response()->json([
            'success' => false,
            'message' => 'Table not found'
        ], 404);
    }

    $request->validate([
        'table_number' => 'required',
        'capacity' => 'required|integer|min:1',
        'location' => 'required'
    ]);

    $table->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Table updated',
        'data' => $table
    ]);
}

public function destroy($id)
{
    $table = Table::find($id);

    if (!$table) {
        return response()->json([
            'success' => false,
            'message' => 'Table not found'
        ], 404);
    }

    $table->delete();

    return response()->json([
        'success' => true,
        'message' => 'Table deleted'
    ]);
}
}