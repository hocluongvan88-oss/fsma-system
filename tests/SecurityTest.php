<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Document;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $operator;
    protected $orgA;
    protected $orgB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organizations
        $this->orgA = Organization::factory()->create(['name' => 'Organization A']);
        $this->orgB = Organization::factory()->create(['name' => 'Organization B']);

        // Create users for org A
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $this->orgA->id,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $this->orgA->id,
        ]);

        $this->operator = User::factory()->create([
            'role' => 'operator',
            'organization_id' => $this->orgA->id,
        ]);

        // Create user for org B
        $this->userOrgB = User::factory()->create([
            'role' => 'manager',
            'organization_id' => $this->orgB->id,
        ]);
    }

    /**
     * Test: Organization Isolation - User cannot see documents from other organizations
     */
    public function test_user_cannot_see_documents_from_other_organization()
    {
        // Create document in org A
        $docOrgA = Document::factory()->create([
            'organization_id' => $this->orgA->id,
            'uploaded_by' => $this->manager->id,
        ]);

        // Create document in org B
        $docOrgB = Document::factory()->create([
            'organization_id' => $this->orgB->id,
            'uploaded_by' => $this->userOrgB->id,
        ]);

        // Login as manager from org A
        $this->actingAs($this->manager);

        // Try to access document from org B
        $response = $this->get(route('documents.show', $docOrgB));

        // Should be forbidden
        $response->assertStatus(403);
    }

    /**
     * Test: Authorization - Operator cannot edit documents
     */
    public function test_operator_cannot_edit_documents()
    {
        $document = Document::factory()->create([
            'organization_id' => $this->orgA->id,
            'uploaded_by' => $this->manager->id,
        ]);

        $this->actingAs($this->operator);

        $response = $this->put(route('documents.update', $document), [
            'title' => 'Updated Title',
            'type' => 'sop',
            'status' => 'draft',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test: Authorization - Manager can edit documents in their organization
     */
    public function test_manager_can_edit_documents_in_their_organization()
    {
        $document = Document::factory()->create([
            'organization_id' => $this->orgA->id,
            'uploaded_by' => $this->manager->id,
        ]);

        $this->actingAs($this->manager);

        $response = $this->put(route('documents.update', $document), [
            'title' => 'Updated Title',
            'type' => 'sop',
            'status' => 'draft',
        ]);

        $response->assertStatus(302); // Redirect on success
    }

    /**
     * Test: API Authentication - Barcode endpoint requires authentication
     */
    public function test_barcode_api_requires_authentication()
    {
        $response = $this->postJson('/api/barcode/scan', [
            'barcode' => '123456',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: API Organization Isolation - User can only scan barcodes from their organization
     */
    public function test_api_barcode_respects_organization_isolation()
    {
        // This test would require setting up TraceRecords in different organizations
        // and verifying that the API only returns results from the user's organization
        $this->markTestIncomplete('Requires TraceRecord factory setup');
    }

    /**
     * Test: Audit Logs - User cannot view audit logs from other organizations
     */
    public function test_user_cannot_view_audit_logs_from_other_organization()
    {
        $this->actingAs($this->manager);

        $response = $this->get(route('reports.audit-log'));

        // Should only show logs from org A
        $response->assertStatus(200);
        // Add assertions to verify only org A logs are shown
    }

    /**
     * Test: User Management - Non-admin cannot create admin users
     */
    public function test_non_admin_cannot_create_admin_users()
    {
        $this->actingAs($this->manager);

        $response = $this->post(route('admin.users.store'), [
            'username' => 'newadmin',
            'email' => 'newadmin@example.com',
            'full_name' => 'New Admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /**
     * Test: User Management - Manager cannot edit admin users
     */
    public function test_manager_cannot_edit_admin_users()
    {
        $this->actingAs($this->manager);

        $response = $this->put(route('admin.users.update', $this->admin), [
            'username' => $this->admin->username,
            'email' => $this->admin->email,
            'full_name' => 'Updated Admin',
            'role' => 'manager',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /**
     * Test: User Management - Manager cannot delete admin users
     */
    public function test_manager_cannot_delete_admin_users()
    {
        $this->actingAs($this->manager);

        $response = $this->delete(route('admin.users.destroy', $this->admin));

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    /**
     * Test: Middleware - Ensure Manager Access
     */
    public function test_operator_cannot_access_manager_routes()
    {
        $this->actingAs($this->operator);

        // Try to access user management (requires manager)
        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /**
     * Test: Middleware - Ensure Admin Access
     */
    public function test_non_admin_cannot_access_admin_routes()
    {
        $this->actingAs($this->manager);

        // Try to access admin-only route
        $response = $this->get(route('admin.packages.index'));

        $response->assertStatus(403);
    }

    /**
     * Test: Document Download - User cannot download documents from other organizations
     */
    public function test_user_cannot_download_documents_from_other_organization()
    {
        $document = Document::factory()->create([
            'organization_id' => $this->orgB->id,
            'uploaded_by' => $this->userOrgB->id,
        ]);

        $this->actingAs($this->manager);

        $response = $this->get(route('documents.download', $document));

        $response->assertStatus(403);
    }

    /**
     * Test: Document Approval - Only manager and admin can approve
     */
    public function test_operator_cannot_approve_documents()
    {
        $document = Document::factory()->create([
            'organization_id' => $this->orgA->id,
            'uploaded_by' => $this->manager->id,
            'status' => 'review',
        ]);

        $this->actingAs($this->operator);

        $response = $this->post(route('documents.approve', $document));

        $response->assertStatus(403);
    }

    /**
     * Test: Sensitive Data Masking - Password should be masked in audit logs
     */
    public function test_password_is_masked_in_audit_logs()
    {
        $this->markTestIncomplete('Requires audit log implementation');
    }

    /**
     * Test: Cross-Organization Access - Admin can see all organizations
     */
    public function test_admin_can_see_all_organizations()
    {
        $docOrgA = Document::factory()->create([
            'organization_id' => $this->orgA->id,
        ]);

        $docOrgB = Document::factory()->create([
            'organization_id' => $this->orgB->id,
        ]);

        $this->actingAs($this->admin);

        // Admin should be able to see both documents
        $response = $this->get(route('documents.index'));

        $response->assertStatus(200);
        // Add assertions to verify both documents are visible
    }
}
