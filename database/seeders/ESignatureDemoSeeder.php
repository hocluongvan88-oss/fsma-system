<?php

namespace Database\Seeders;

use App\Models\ESignature;
use App\Models\User;
use App\Models\TraceRecord;
use App\Models\CTEEvent;
use App\Models\Document;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ESignatureDemoSeeder extends Seeder
{
    public function run(): void
    {
        $demoOrganization = Organization::where('name', 'VEXIM Global (Demo)')->first();
        
        if (!$demoOrganization) {
            $this->command->error('Demo organization not found. Please run DatabaseSeeder first.');
            return;
        }

        $demoOrganizationId = $demoOrganization->id;

        // Get or create demo users with correct organization_id
        $manager = User::updateOrCreate(
            ['email' => 'manager@fsma204.com'],
            [
                'username' => 'manager',
                'full_name' => 'Warehouse Manager',
                'password' => Hash::make('manager123'),
                'role' => 'manager',
                'package_id' => 'premium',
                'max_cte_records_monthly' => 2500,
                'max_documents' => 999999,
                'max_users' => 3,
                'is_active' => true,
                'organization_id' => $demoOrganizationId,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@fsma204.com'],
            [
                'username' => 'admin',
                'full_name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'package_id' => 'enterprise',
                'max_cte_records_monthly' => 99999999,
                'max_documents' => 999999,
                'max_users' => 999999,
                'is_active' => true,
                'organization_id' => 1,
            ]
        );

        $this->createTraceRecordSignatures($manager);
        $this->createCTEEventSignatures($manager, $admin);
        $this->createDocumentSignatures($admin);
        $this->createComplianceSignatures($manager);
        $this->createPerformanceTestData($manager, $admin);

        $this->command->info('E-Signature demo data created successfully!');
    }

    private function createTraceRecordSignatures(User $user): void
    {
        $traceRecords = TraceRecord::limit(3)->get();

        foreach ($traceRecords as $record) {
            ESignature::create([
                'user_id' => $user->id,
                'record_type' => 'TraceRecord',
                'record_id' => $record->id,
                'action' => 'create',
                'reason' => 'Initial trace record creation for product batch verification',
                'signature_hash' => hash('sha256', implode('|', [
                    $user->username,
                    'password123',
                    now()->toIso8601String(),
                    'TraceRecord',
                    $record->id,
                    'create',
                ])),
                'ip_address' => '192.168.1.100',
                'signed_at' => now()->subDays(rand(1, 30)),
            ]);

            ESignature::create([
                'user_id' => $user->id,
                'record_type' => 'TraceRecord',
                'record_id' => $record->id,
                'action' => 'verify',
                'reason' => 'Verification of trace record for compliance audit',
                'signature_hash' => hash('sha256', implode('|', [
                    $user->username,
                    'password123',
                    now()->subDays(rand(1, 30))->toIso8601String(),
                    'TraceRecord',
                    $record->id,
                    'verify',
                ])),
                'ip_address' => '192.168.1.100',
                'signed_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }

    private function createCTEEventSignatures(User $manager, User $admin): void
    {
        $cteEvents = CTEEvent::limit(5)->get();

        foreach ($cteEvents as $event) {
            // Manager creates the event
            ESignature::create([
                'user_id' => $manager->id,
                'record_type' => 'CTEEvent',
                'record_id' => $event->id,
                'action' => 'create',
                'reason' => 'CTE event creation - ' . $event->event_type,
                'signature_hash' => hash('sha256', implode('|', [
                    $manager->username,
                    'password123',
                    now()->subDays(rand(5, 20))->toIso8601String(),
                    'CTEEvent',
                    $event->id,
                    'create',
                ])),
                'ip_address' => '192.168.1.101',
                'signed_at' => now()->subDays(rand(5, 20)),
            ]);

            // Admin approves the event
            ESignature::create([
                'user_id' => $admin->id,
                'record_type' => 'CTEEvent',
                'record_id' => $event->id,
                'action' => 'approve',
                'reason' => 'CTE event approval - Verified and compliant',
                'signature_hash' => hash('sha256', implode('|', [
                    $admin->username,
                    'password123',
                    now()->subDays(rand(1, 15))->toIso8601String(),
                    'CTEEvent',
                    $event->id,
                    'approve',
                ])),
                'ip_address' => '192.168.1.102',
                'signed_at' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    private function createDocumentSignatures(User $user): void
    {
        $documents = Document::limit(4)->get();

        foreach ($documents as $document) {
            ESignature::create([
                'user_id' => $user->id,
                'record_type' => 'Document',
                'record_id' => $document->id,
                'action' => 'sign',
                'reason' => 'Document signed for compliance - ' . $document->title,
                'signature_hash' => hash('sha256', implode('|', [
                    $user->username,
                    'password123',
                    now()->subDays(rand(10, 25))->toIso8601String(),
                    'Document',
                    $document->id,
                    'sign',
                ])),
                'ip_address' => '192.168.1.102',
                'signed_at' => now()->subDays(rand(10, 25)),
            ]);
        }
    }

    private function createComplianceSignatures(User $user): void
    {
        // Create signatures for compliance actions
        $complianceActions = [
            ['type' => 'ComplianceReport', 'id' => 1, 'action' => 'approve', 'reason' => 'FDA compliance report approved'],
            ['type' => 'AuditLog', 'id' => 1, 'action' => 'certify', 'reason' => 'Audit log certified for regulatory submission'],
            ['type' => 'ComplianceReport', 'id' => 2, 'action' => 'approve', 'reason' => 'FSMA compliance report approved'],
        ];

        foreach ($complianceActions as $action) {
            ESignature::create([
                'user_id' => $user->id,
                'record_type' => $action['type'],
                'record_id' => $action['id'],
                'action' => $action['action'],
                'reason' => $action['reason'],
                'signature_hash' => hash('sha256', implode('|', [
                    $user->username,
                    'password123',
                    now()->subDays(rand(5, 30))->toIso8601String(),
                    $action['type'],
                    $action['id'],
                    $action['action'],
                ])),
                'ip_address' => '192.168.1.102',
                'signed_at' => now()->subDays(rand(5, 30)),
            ]);
        }
    }

    private function createPerformanceTestData(User $manager, User $admin): void
    {
        $users = [$manager, $admin];
        $recordTypes = ['TraceRecord', 'CTEEvent', 'Document', 'ComplianceReport'];
        $actions = ['create', 'update', 'verify', 'approve', 'sign'];
        
        // Create 100+ signatures spread across 30 days for realistic performance metrics
        for ($i = 0; $i < 120; $i++) {
            $daysAgo = rand(0, 30);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);
            
            $signedAt = now()
                ->subDays($daysAgo)
                ->subHours($hoursAgo)
                ->subMinutes($minutesAgo);
            
            $user = $users[array_rand($users)];
            $recordType = $recordTypes[array_rand($recordTypes)];
            $action = $actions[array_rand($actions)];
            $recordId = rand(1, 50);
            
            ESignature::create([
                'user_id' => $user->id,
                'record_type' => $recordType,
                'record_id' => $recordId,
                'action' => $action,
                'reason' => ucfirst($action) . ' action on ' . $recordType . ' #' . $recordId,
                'signature_hash' => hash('sha256', implode('|', [
                    $user->username,
                    'password123',
                    $signedAt->toIso8601String(),
                    $recordType,
                    $recordId,
                    $action,
                ])),
                'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                'signed_at' => $signedAt,
            ]);
        }
    }
}
