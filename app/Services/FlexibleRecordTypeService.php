<?php

namespace App\Services;

use App\Models\SignatureRecordType;
use Illuminate\Support\Facades\Cache;

class FlexibleRecordTypeService
{
    const CACHE_KEY = 'signature_record_types';
    const CACHE_TTL = 3600; // 1 hour

    public function registerRecordType(
        string $recordType,
        string $modelClass,
        string $displayName,
        array $contentFields,
        ?string $description = null
    ): SignatureRecordType {
        $type = SignatureRecordType::updateOrCreate(
            ['record_type' => $recordType],
            [
                'model_class' => $modelClass,
                'display_name' => $displayName,
                'description' => $description,
                'content_fields' => $contentFields,
                'is_active' => true,
            ]
        );

        $this->clearCache();
        return $type;
    }

    public function getActiveRecordTypes(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SignatureRecordType::active()->get()->toArray();
        });
    }

    public function getRecordType(string $recordType): ?SignatureRecordType
    {
        return SignatureRecordType::where('record_type', $recordType)
            ->where('is_active', true)
            ->first();
    }

    public function validateRecordType(string $recordType): bool
    {
        return $this->getRecordType($recordType) !== null;
    }

    public function extractRecordContent(string $recordType, int $recordId): string
    {
        $type = $this->getRecordType($recordType);
        
        if (!$type) {
            throw new \Exception("Record type not found or inactive: {$recordType}");
        }

        return $type->extractRecordContent($recordId);
    }

    public function disableRecordType(string $recordType): void
    {
        SignatureRecordType::where('record_type', $recordType)
            ->update(['is_active' => false]);
        
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
