<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TraceRecord;
use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Quét mã barcode (GS1, TLC, hoặc SKU sản phẩm)
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = $validated['barcode'];
        $currentUser = auth()->user();

        // ✅ Thử parse mã GS1-128
        $parsed = $this->parseGS1Barcode($barcode);

        if ($parsed) {
            return response()->json([
                'success' => true,
                'data' => $parsed,
            ]);
        }

        // ✅ Tìm trong bảng trace_records
        $traceRecord = TraceRecord::where('tlc', $barcode)
            ->where('organization_id', $currentUser->organization_id)
            ->first();

        if ($traceRecord) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'tlc',
                    'tlc' => $traceRecord->tlc,
                    'product' => $traceRecord->product,
                    'quantity' => $traceRecord->quantity,
                    'unit' => $traceRecord->unit,
                ],
            ]);
        }

        // ✅ Tìm trong bảng products
        $product = Product::where('sku', $barcode)
            ->where('organization_id', $currentUser->organization_id)
            ->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'data' => [
                    'type' => 'product',
                    'product' => $product,
                ],
            ]);
        }

        // ❌ Không tìm thấy
        return response()->json([
            'success' => false,
            'message' => 'Barcode not recognized',
        ], 404);
    }

    /**
     * Kiểm tra tính hợp lệ của TLC (đổi tên từ validate() để tránh xung đột)
     */
    public function validateTLC(Request $request)
    {
        $validated = $request->validate([
            'tlc' => 'required|string',
        ]);

        $currentUser = auth()->user();

        $exists = TraceRecord::where('tlc', $validated['tlc'])
            ->where('organization_id', $currentUser->organization_id)
            ->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'TLC already exists' : 'TLC is available',
        ]);
    }

    /**
     * Parse mã GS1-128 để tách các Application Identifier (AI)
     */
    private function parseGS1Barcode($barcode)
    {
        $patterns = [
            '01'  => ['name' => 'gtin', 'length' => 14],
            '10'  => ['name' => 'batch', 'length' => null],
            '11'  => ['name' => 'prod_date', 'length' => 6],
            '13'  => ['name' => 'pack_date', 'length' => 6],
            '15'  => ['name' => 'best_before', 'length' => 6],
            '17'  => ['name' => 'expiry_date', 'length' => 6],
            '21'  => ['name' => 'serial', 'length' => null],
            '310' => ['name' => 'weight_kg', 'length' => 6],
            '37'  => ['name' => 'count', 'length' => null],
        ];

        $data = [];
        $position = 0;
        $length = strlen($barcode);

        while ($position < $length) {
            $found = false;

            foreach ($patterns as $ai => $config) {
                $aiLength = strlen($ai);

                if (substr($barcode, $position, $aiLength) === $ai) {
                    $position += $aiLength;
                    $found = true;

                    if ($config['length']) {
                        $value = substr($barcode, $position, $config['length']);
                        $position += $config['length'];
                    } else {
                        // Biến độ dài – đọc đến AI tiếp theo hoặc hết chuỗi
                        $nextAI = $length;
                        foreach (array_keys($patterns) as $nextAiCode) {
                            $pos = strpos($barcode, $nextAiCode, $position);
                            if ($pos !== false && $pos < $nextAI) {
                                $nextAI = $pos;
                            }
                        }
                        $value = substr($barcode, $position, $nextAI - $position);
                        $position = $nextAI;
                    }

                    $data[$config['name']] = trim($value);
                    break;
                }
            }

            if (!$found) {
                $position++;
            }
        }

        return !empty($data)
            ? ['type' => 'gs1', 'data' => $data]
            : null;
    }
}
