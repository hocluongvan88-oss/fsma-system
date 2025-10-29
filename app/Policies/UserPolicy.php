<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Only admin and manager can view users list
        return $user->isManager();
    }

    /**
     * Determine if the user can view another user.
     * Improved with better organization checks
     */
    public function view(User $user, User $targetUser): bool
    {
        // System admin can view any user
        if ($user->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return true;
        }

        // Cannot view system admin (chặn xem người có vai trò cao nhất, tức là Admin)
        if ($targetUser->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return false;
        }

        if ($user->organization_id === null || $targetUser->organization_id === null) {
            return false;
        }

        return $user->organization_id === $targetUser->organization_id;
    }

    /**
     * Determine if the user can create a user.
     */
    public function create(User $user): bool
    {
        // Only manager and admin can create users
        return $user->isManager();
    }

    /**
     * Determine if the user can update a user.
     * Improved with better security checks
     */
    public function update(User $user, User $targetUser): bool
    {
        // Admin (vai trò cao nhất) có thể update bất kỳ user nào
        if ($user->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            // Tránh tự update chính mình, trừ khi cần thiết (giữ lại logic cũ)
            if ($user->id === $targetUser->id) {
                return true;
            }
            // Admin cao nhất có thể update tất cả mọi người (trừ Admin cao nhất khác)
            // Tùy chọn: Nếu bạn muốn chặn admin update admin khác, giữ lại kiểm tra dưới đây
            // Hiện tại ta cho phép Admin update tất cả, trừ khi người đó có vai trò cao hơn.
            return true;
        }

        // Cannot update system admin (Chặn update người có vai trò Admin cao nhất)
        if ($targetUser->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return false;
        }

        if ($user->organization_id === null || $targetUser->organization_id === null) {
            return false;
        }

        if ($user->organization_id !== $targetUser->organization_id) {
            return false;
        }

        // --- SỬA LOGIC QUYỀN HẠN TẠI ĐÂY ---
        // 1. Manager có thể cập nhật chính họ
        if ($user->id === $targetUser->id) {
            return true;
        }

        // 2. Manager có thể cập nhật các user KHÔNG phải admin (trong cùng tổ chức)
        if ($user->isManager()) {
            return !$targetUser->isAdmin(); // Giữ lại isAdmin() để chặn Manager update Admin
        }
        
        // Mặc định trả về false cho các trường hợp khác
        return false;
    }

    /**
     * Determine if the user can delete a user.
     * Improved with better security checks
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Admin (vai trò cao nhất) có thể delete bất kỳ user nào (trừ chính họ)
        if ($user->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return $user->id !== $targetUser->id; // Vẫn giữ quy tắc chặn tự xóa
        }

        // Cannot delete system admin (Chặn xóa Admin cao nhất)
        if ($targetUser->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return false;
        }

        if ($user->organization_id === null || $targetUser->organization_id === null) {
            return false;
        }

        if ($user->organization_id !== $targetUser->organization_id) {
            return false;
        }

        // Cannot delete self
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Manager can only delete non-admin users
        return $user->isManager() && !$targetUser->isAdmin();
    }

    /**
     * Determine if the user can update package.
     * Improved organization_id check to prevent cross-organization privilege escalation
     */
    public function updatePackage(User $user, User $targetUser): bool
    {
        // Only admin can update packages
        if (!$user->isAdmin()) {
            return false;
        }

        // Admin (vai trò cao nhất) có thể update package bất kỳ
        if ($user->isAdmin()) { // <-- Đã đổi từ isSystemAdmin() sang isAdmin()
            return true;
        }

        if ($user->organization_id === null || $targetUser->organization_id === null) {
            return false;
        }

        // Admin can only update packages for users in their organization
        return $user->organization_id === $targetUser->organization_id;
    }
}
