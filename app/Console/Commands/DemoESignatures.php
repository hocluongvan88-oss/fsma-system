<?php

namespace App\Console\Commands;

use App\Models\ESignature;
use App\Models\User;
use Illuminate\Console\Command;

class DemoESignatures extends Command
{
    protected $signature = 'demo:e-signatures {--clear : Clear existing demo signatures}';
    protected $description = 'Generate demo E-Signature data for testing';

    public function handle(): int
    {
        if ($this->option('clear')) {
            $this->info('Clearing existing demo signatures...');
            ESignature::truncate();
            $this->info('Demo signatures cleared.');
        }

        $this->info('Generating E-Signature demo data...');
        $this->call('db:seed', ['--class' => 'ESignatureDemoSeeder']);

        $this->displayDemoInfo();

        return 0;
    }

    private function displayDemoInfo(): void
    {
        $this->newLine();
        $this->info('E-Signature Demo Data Generated Successfully!');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total E-Signatures', ESignature::count()],
                ['Trace Record Signatures', ESignature::where('record_type', 'TraceRecord')->count()],
                ['CTE Event Signatures', ESignature::where('record_type', 'CTEEvent')->count()],
                ['Document Signatures', ESignature::where('record_type', 'Document')->count()],
                ['Compliance Signatures', ESignature::whereIn('record_type', ['ComplianceReport', 'AuditLog'])->count()],
            ]
        );

        $this->newLine();
        $this->info('Test Credentials:');
        $this->line('Manager: manager@fsma204.com / password123');
        $this->line('Admin: admin@fsma204.com / password123');

        $this->newLine();
        $this->info('View signatures at: /admin/e-signatures');
    }
}
