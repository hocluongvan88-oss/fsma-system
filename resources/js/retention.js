/**
 * Retention Policy Management Module
 * Handles all retention policy UI interactions
 */

const RetentionModule = {
  apiBaseUrl: "/admin/retention",

  /**
   * Open create policy modal
   */
  openCreatePolicyModal() {
    document.getElementById("modalTitle").textContent =
      window.retentionMessages?.createPolicy || "Create Retention Policy"
    document.getElementById("policyForm").action = window.retentionRoutes?.store || "/admin/retention"
    document.getElementById("methodField").value = "POST"
    document.getElementById("policyId").value = ""
    document.getElementById("policyName").value = ""
    document.getElementById("dataType").value = ""
    document.getElementById("retentionMonths").value = ""
    document.getElementById("backupBeforeDeletion").checked = true
    document.getElementById("description").value = ""
    document.getElementById("policyModal").style.display = "flex"
  },

  /**
   * Edit existing policy
   */
  editPolicy(policyId) {
    const editUrl = `${this.apiBaseUrl}/${policyId}/edit`

    fetch(editUrl)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: Failed to load policy`)
        }
        return response.json()
      })
      .then((result) => {
        if (!result.success || !result.data) {
          throw new Error("Invalid response format from server")
        }

        const data = result.data
        document.getElementById("modalTitle").textContent =
          window.retentionMessages?.editPolicy || "Edit Retention Policy"
        document.getElementById("policyForm").action = `${this.apiBaseUrl}/${policyId}`
        document.getElementById("methodField").value = "PUT"

        document.getElementById("policyId").value = policyId
        document.getElementById("policyName").value = data.policy_name || ""
        document.getElementById("dataType").value = data.data_type || ""
        document.getElementById("retentionMonths").value = data.retention_months || ""
        document.getElementById("backupBeforeDeletion").checked = data.backup_before_deletion || false
        document.getElementById("description").value = data.description || ""
        document.getElementById("policyModal").style.display = "flex"
      })
      .catch((error) => {
        console.error("[v0] Error fetching policy:", error)
        this.showError(`Failed to load policy: ${error.message}`)
      })
  },

  /**
   * Close policy modal
   */
  closePolicyModal() {
    document.getElementById("policyModal").style.display = "none"
  },

  /**
   * Execute cleanup with confirmation
   */
  executeCleanup(policyId) {
    const executeUrl = `${this.apiBaseUrl}/${policyId}/execute`

    if (!policyId || policyId <= 0) {
      this.showError("Invalid policy ID")
      return
    }

    document.getElementById("executeForm").action = executeUrl
    document.getElementById("executePolicyId").value = policyId
    document.getElementById("executeModal").style.display = "flex"
  },

  /**
   * Close execute modal
   */
  closeExecuteModal() {
    document.getElementById("executeModal").style.display = "none"
  },

  /**
   * Show error message
   */
  showError(message) {
    alert(message)
    console.error("[v0] Retention Module Error:", message)
  },

  /**
   * Initialize event listeners
   */
  init() {
    const policyModal = document.getElementById("policyModal")
    if (policyModal) {
      policyModal.addEventListener("click", (e) => {
        if (e.target === policyModal) this.closePolicyModal()
      })
    }

    const executeModal = document.getElementById("executeModal")
    if (executeModal) {
      executeModal.addEventListener("click", (e) => {
        if (e.target === executeModal) this.closeExecuteModal()
      })
    }

    const executeForm = document.getElementById("executeForm")
    if (executeForm) {
      executeForm.addEventListener("submit", (e) => {
        const action = executeForm.action
        if (!action || !action.includes("/execute")) {
          e.preventDefault()
          this.showError("Form configuration error. Please close and try again.")
          return false
        }
      })
    }

    console.log("[v0] Retention Module initialized")
  },
}

document.addEventListener("DOMContentLoaded", () => {
  RetentionModule.init()
})

function openCreatePolicyModal() {
  RetentionModule.openCreatePolicyModal()
}

function editPolicy(policyId) {
  RetentionModule.editPolicy(policyId)
}

function closePolicyModal() {
  RetentionModule.closePolicyModal()
}

function executeCleanup(policyId) {
  RetentionModule.executeCleanup(policyId)
}

function closeExecuteModal() {
  RetentionModule.closeExecuteModal()
}
