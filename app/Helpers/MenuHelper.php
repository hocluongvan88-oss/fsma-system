<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class MenuHelper
{
    /**
     * Get menu items based on user's role and package features
     * 
     * @return array
     */
    public static function getMenuItems(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $items = [];

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->isManager()) {
            $items[] = [
                'label' => __('messages.admin_users'),
                'route' => 'admin.users.index',
                'icon' => 'users',
                'permission' => 'admin_users',
                'visible' => true,
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->hasFeature('document_management')) {
            $items[] = [
                'label' => __('messages.documents'),
                'route' => 'documents.index',
                'icon' => 'file-text',
                'permission' => 'document_management',
                'visible' => true,
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->hasFeature('traceability')) {
            $items[] = [
                'label' => __('messages.traceability'),
                'route' => 'cte.receiving',
                'icon' => 'link',
                'permission' => 'traceability',
                'visible' => true,
                'submenu' => [
                    [
                        'label' => __('messages.receiving'),
                        'route' => 'cte.receiving',
                    ],
                    [
                        'label' => __('messages.transformation'),
                        'route' => 'cte.transformation',
                    ],
                    [
                        'label' => __('messages.shipping'),
                        'route' => 'cte.shipping',
                    ],
                ],
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->hasFeature('e_signatures')) {
            $items[] = [
                'label' => __('messages.e_signatures'),
                'route' => 'admin.e-signatures.index',
                'icon' => 'pen-tool',
                'permission' => 'e_signatures',
                'visible' => $user->isSystemAdmin() || $user->isAdmin(),
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->hasFeature('certificates')) {
            $items[] = [
                'label' => __('messages.certificates'),
                'route' => 'certificates.index',
                'icon' => 'shield',
                'permission' => 'certificates',
                'visible' => true,
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || ($user->isAdmin() && $user->hasFeature('data_retention'))) {
            $items[] = [
                'label' => __('messages.data_retention'),
                'route' => 'admin.retention.index',
                'icon' => 'archive',
                'permission' => 'data_retention',
                'visible' => $user->isSystemAdmin() || $user->isAdmin(),
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || ($user->isAdmin() && $user->hasFeature('archival'))) {
            $items[] = [
                'label' => __('messages.archival'),
                'route' => 'admin.archival.index',
                'icon' => 'box',
                'permission' => 'archival',
                'visible' => $user->isSystemAdmin() || $user->isAdmin(),
            ];
        }

        if ($user->isSystemAdmin() || $user->isAdmin() || $user->hasFeature('compliance_report')) {
            $items[] = [
                'label' => __('messages.compliance_report'),
                'route' => 'admin.compliance',
                'icon' => 'file-check',
                'permission' => 'compliance_report',
                'visible' => $user->isSystemAdmin() || $user->isAdmin(),
            ];
        }

        $items[] = [
            'label' => __('messages.reports'),
            'route' => 'reports.traceability',
            'icon' => 'bar-chart-2',
            'permission' => 'view_reports',
            'visible' => true,
            'submenu' => [
                [
                    'label' => __('messages.traceability_report'),
                    'route' => 'reports.traceability',
                ],
                [
                    'label' => __('messages.audit_log'),
                    'route' => 'reports.audit-log',
                ],
            ],
        ];

        return $items;
    }

    /**
     * Check if user can access a specific menu item
     * 
     * @param string $permission
     * @return bool
     */
    public static function canAccess(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        if ($user->isSystemAdmin() || $user->isAdmin()) {
            return true;
        }

        $permissionMap = [
            'admin_users' => 'admin_users',
            'document_management' => 'document_management',
            'traceability' => 'traceability',
            'e_signatures' => 'e_signatures',
            'certificates' => 'certificates',
            'data_retention' => 'data_retention',
            'archival' => 'archival',
            'compliance_report' => 'compliance_report',
            'view_reports' => 'view_reports',
        ];

        $feature = $permissionMap[$permission] ?? null;

        if (!$feature) {
            return false;
        }

        if (in_array($feature, ['admin_users', 'data_retention', 'archival', 'compliance_report'])) {
            return $user->isManager();
        }

        return $user->hasFeature($feature);
    }

    /**
     * Get feature lock message for a specific feature
     * 
     * @param string $feature
     * @return string
     */
    public static function getFeatureLockMessage(string $feature): string
    {
        $featureNames = [
            'document_management' => __('messages.document_management'),
            'traceability' => __('messages.traceability'),
            'e_signatures' => __('messages.e_signatures'),
            'certificates' => __('messages.certificates'),
            'data_retention' => __('messages.data_retention'),
            'archival' => __('messages.archival'),
            'compliance_report' => __('messages.compliance_report'),
        ];

        $featureName = $featureNames[$feature] ?? $feature;
        $packageName = Auth::user()?->package?->name ?? 'Free';

        return __('messages.feature_locked', [
            'feature' => $featureName,
            'package' => $packageName,
        ]);
    }
}
