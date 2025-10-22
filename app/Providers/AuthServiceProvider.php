protected $policies = [
    \App\Models\Document::class => \App\Policies\DocumentPolicy::class,
    \App\Models\AuditLog::class => \App\Policies\AuditLogPolicy::class,
    \App\Models\User::class => \App\Policies\UserPolicy::class,
];
