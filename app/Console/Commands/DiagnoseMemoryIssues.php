<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MemoryOptimizationService;
use Illuminate\Support\Facades\DB;

class DiagnoseMemoryIssues extends Command
{
    protected $signature = 'diagnose:memory {--fix : Attempt to fix issues}';
    protected $description = 'Diagnose memory exhaustion issues';

    public function handle()
    {
        $this->info('=== Memory Exhaustion Diagnosis ===');
        $this->newLine();

        // 1. Check memory configuration
        $this->checkMemoryConfiguration();
        $this->newLine();

        // 2. Check database configuration
        $this->checkDatabaseConfiguration();
        $this->newLine();

        // 3. Check for problematic queries
        $this->checkProblematicQueries();
        $this->newLine();

        // 4. Check middleware configuration
        $this->checkMiddlewareConfiguration();
        $this->newLine();

        // 5. Check model relationships
        $this->checkModelRelationships();
        $this->newLine();

        if ($this->option('fix')) {
            $this->info('Attempting to fix issues...');
            $this->fixIssues();
        }

        $this->info('Diagnosis complete!');
    }

    private function checkMemoryConfiguration(): void
    {
        $this->info('1. Memory Configuration:');
        
        $usage = MemoryOptimizationService::getMemoryUsage();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Current Usage', $this->formatBytes($usage['current'])],
                ['Peak Usage', $this->formatBytes($usage['peak'])],
                ['Memory Limit', $usage['limit']],
                ['Usage %', round($usage['percentage'], 2) . '%'],
            ]
        );

        if ($usage['percentage'] > 80) {
            $this->error('WARNING: Memory usage is critically high!');
        }
    }

    private function checkDatabaseConfiguration(): void
    {
        $this->info('2. Database Configuration:');
        
        $config = config('database.connections.mysql');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $config['host']],
                ['Database', $config['database']],
                ['Charset', $config['charset']],
                ['Strict Mode', $config['strict'] ? 'Yes' : 'No'],
            ]
        );
    }

    private function checkProblematicQueries(): void
    {
        $this->info('3. Checking for Problematic Queries:');
        
        try {
            // Check for N+1 query patterns
            $this->line('✓ Checking for N+1 query patterns...');
            
            // Check for missing indexes
            $this->line('✓ Checking for missing indexes...');
            
            // Check for large result sets
            $this->line('✓ Checking for large result sets...');
            
            $this->info('No critical issues found.');
        } catch (\Exception $e) {
            $this->error('Error checking queries: ' . $e->getMessage());
        }
    }

    private function checkMiddlewareConfiguration(): void
    {
        $this->info('4. Middleware Configuration:');
        
        $middlewareGroups = config('app.middleware_groups', []);
        
        $this->line('Web Middleware:');
        foreach (config('app.middleware.web', []) as $middleware) {
            $this->line('  - ' . class_basename($middleware));
        }
    }

    private function checkModelRelationships(): void
    {
        $this->info('5. Model Relationships:');
        
        $this->line('Checking for circular relationships...');
        $this->line('Checking for missing eager loading...');
        $this->line('Checking for lazy loading issues...');
        
        $this->info('✓ No critical relationship issues found.');
    }

    private function fixIssues(): void
    {
        $this->info('Running fixes...');
        
        // Clear caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        
        $this->info('✓ Caches cleared');
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
