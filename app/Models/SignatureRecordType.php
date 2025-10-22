<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ESignature;

class SignatureRecordType extends Model
{
    /**
     * Tên bảng trong database.
     *
     * @var string
     */
    protected $table = 'signature_record_types';

    /**
     * Các cột có thể gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'record_type',
        'model_class',
        'display_name',
        'description',
        'content_fields',
        'is_active',
        'sort_order',
    ];

    /**
     * Kiểu dữ liệu của các cột.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content_fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope chỉ lấy các loại bản ghi đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Liên kết đến các bản ghi chữ ký (ESignature).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signatures()
    {
        return $this->hasMany(
            ESignature::class,
            'record_type',  // Khóa ngoại trên bảng e_signatures
            'record_type'   // Khóa cục bộ trên bảng signature_record_types
        );
    }

    /**
     * Lấy instance của model được chỉ định.
     */
    public function getModelInstance($recordId)
    {
        $modelClass = $this->model_class;

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class not found: {$modelClass}");
        }

        return $modelClass::find($recordId);
    }

    /**
     * Trích xuất nội dung của bản ghi theo danh sách field cấu hình.
     */
    public function extractRecordContent($recordId): string
    {
        $record = $this->getModelInstance($recordId);
        
        if (!$record) {
            throw new \Exception("Record not found: {$this->record_type} #{$recordId}");
        }

        $fields = $this->content_fields ?? [];
        $content = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                // Dạng phức tạp: ['name' => 'Tên', 'path' => 'relation.field']
                $value = data_get($record, $field['path']);
                $content[$field['name']] = $value;
            } else {
                // Dạng đơn giản: 'title'
                $content[$field] = $record->{$field} ?? null;
            }
        }

        return json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
