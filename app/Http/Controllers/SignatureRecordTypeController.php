<?php

namespace App\Http\Controllers;

use App\Models\SignatureRecordType;
use Illuminate\Http\Request;

class SignatureRecordTypeController extends Controller
{
    public function index()
    {
        $recordTypes = SignatureRecordType::withCount('signatures')->get();
        return view('admin.e-signatures.record-types.index', compact('recordTypes'));
    }

    public function show($id)
    {
        $type = SignatureRecordType::findOrFail($id);
        return response()->json([
            'success' => true,
            'record_type' => $type
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'record_type_key' => 'required|string|unique:signature_record_types,record_type',
            'model_class' => 'required|string',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'content_fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['record_type'] = $validated['record_type_key'];
        unset($validated['record_type_key']);

        $type = SignatureRecordType::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'record_type' => $type
            ], 201);
        }
        
        return redirect()->route('e-signatures.record-types.index')
            ->with('success', 'Record type created successfully');
    }

    public function update(Request $request, $id)
    {
        $type = SignatureRecordType::findOrFail($id);
        
        $validated = $request->validate([
            'model_class' => 'sometimes|required|string',
            'display_name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'content_fields' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $type->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'record_type' => $type
            ]);
        }
        
        return redirect()->route('e-signatures.record-types.index')
            ->with('success', 'Record type updated successfully');
    }

    public function toggle(Request $request, $id)
    {
        $type = SignatureRecordType::findOrFail($id);
        
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $type->update(['is_active' => $validated['is_active']]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'record_type' => $type,
                'message' => $type->is_active ? 'Record type activated' : 'Record type deactivated'
            ]);
        }
        
        return redirect()->route('e-signatures.record-types.index')
            ->with('success', $type->is_active ? 'Record type activated' : 'Record type deactivated');
    }

    public function destroy($id)
    {
        $type = SignatureRecordType::findOrFail($id);
        $type->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Record type deleted successfully'
        ]);
    }
}
