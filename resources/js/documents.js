/**
 * Document Module - Performance & UX Optimizations
 * Handles bulk operations, filtering, and performance enhancements
 */

// ============================================
// PERFORMANCE MONITORING
// ============================================

const DocumentPerformance = {
  metrics: {},

  startMeasure(name) {
    this.metrics[name] = performance.now()
  },

  endMeasure(name) {
    if (this.metrics[name]) {
      const duration = performance.now() - this.metrics[name]
      console.log(`[v0] ${name}: ${duration.toFixed(2)}ms`)
      delete this.metrics[name]
    }
  },

  logMetrics() {
    const metrics = performance.getEntriesByType("navigation")[0]
    if (metrics) {
      console.log("[v0] Page Load Metrics:", {
        domContentLoaded: metrics.domContentLoadedEventEnd - metrics.domContentLoadedEventStart,
        loadComplete: metrics.loadEventEnd - metrics.loadEventStart,
        domInteractive: metrics.domInteractive - metrics.fetchStart,
      })
    }
  },
}

// ============================================
// BULK OPERATIONS OPTIMIZATION
// ============================================

const BulkOperations = {
  selectedDocuments: [],

  init() {
    this.attachEventListeners()
    this.optimizeCheckboxes()
  },

  attachEventListeners() {
    const selectAllCheckbox = document.getElementById("select-all-checkbox")
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener("change", () => this.toggleSelectAll())
    }

    // Delegate checkbox events for better performance
    document.addEventListener("change", (e) => {
      if (e.target.id && e.target.id.startsWith("doc-checkbox-")) {
        const docId = Number.parseInt(e.target.id.replace("doc-checkbox-", ""))
        this.toggleDocumentSelection(docId)
      }
    })
  },

  optimizeCheckboxes() {
    // Use requestAnimationFrame for smooth checkbox updates
    const checkboxes = document.querySelectorAll('[id^="doc-checkbox-"]')
    checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", () => {
        requestAnimationFrame(() => this.updateBulkActionsToolbar())
      })
    })
  },

  toggleDocumentSelection(docId) {
    const checkbox = document.getElementById(`doc-checkbox-${docId}`)
    if (checkbox.checked) {
      if (!this.selectedDocuments.includes(docId)) {
        this.selectedDocuments.push(docId)
      }
    } else {
      this.selectedDocuments = this.selectedDocuments.filter((id) => id !== docId)
    }
  },

  toggleSelectAll() {
    const selectAllCheckbox = document.getElementById("select-all-checkbox")
    const checkboxes = document.querySelectorAll('[id^="doc-checkbox-"]')

    this.selectedDocuments = []
    checkboxes.forEach((checkbox) => {
      checkbox.checked = selectAllCheckbox.checked
      if (selectAllCheckbox.checked) {
        const docId = Number.parseInt(checkbox.id.replace("doc-checkbox-", ""))
        this.selectedDocuments.push(docId)
      }
    })
    this.updateBulkActionsToolbar()
  },

  updateBulkActionsToolbar() {
    const toolbar = document.getElementById("bulkActionsToolbar")
    const count = document.getElementById("selectedCount")

    if (this.selectedDocuments.length > 0) {
      toolbar.style.display = "flex"
      count.textContent = `${this.selectedDocuments.length} document${this.selectedDocuments.length !== 1 ? "s" : ""} selected`
    } else {
      toolbar.style.display = "none"
    }
  },

  clearSelection() {
    this.selectedDocuments = []
    document.querySelectorAll('[id^="doc-checkbox-"]').forEach((checkbox) => {
      checkbox.checked = false
    })
    document.getElementById("select-all-checkbox").checked = false
    this.updateBulkActionsToolbar()
  },

  async bulkApprove() {
    if (this.selectedDocuments.length === 0) return
    if (!confirm(`Approve ${this.selectedDocuments.length} document(s)?`)) return

    DocumentPerformance.startMeasure("bulkApprove")

    try {
      const response = await fetch(
        document.querySelector('meta[name="bulk-approve-url"]')?.content || "/documents/bulk-approve",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ document_ids: this.selectedDocuments }),
        },
      )

      const data = await response.json()
      if (data.success) {
        location.reload()
      } else {
        alert("Error: " + data.message)
      }
    } catch (error) {
      console.error("[v0] Bulk approve error:", error)
      alert("An error occurred. Please try again.")
    } finally {
      DocumentPerformance.endMeasure("bulkApprove")
    }
  },

  async bulkArchive() {
    if (this.selectedDocuments.length === 0) return
    if (!confirm(`Archive ${this.selectedDocuments.length} document(s)?`)) return

    DocumentPerformance.startMeasure("bulkArchive")

    try {
      const response = await fetch(
        document.querySelector('meta[name="bulk-archive-url"]')?.content || "/documents/bulk-archive",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ document_ids: this.selectedDocuments }),
        },
      )

      const data = await response.json()
      if (data.success) {
        location.reload()
      } else {
        alert("Error: " + data.message)
      }
    } catch (error) {
      console.error("[v0] Bulk archive error:", error)
      alert("An error occurred. Please try again.")
    } finally {
      DocumentPerformance.endMeasure("bulkArchive")
    }
  },

  bulkExport() {
    if (this.selectedDocuments.length === 0) return

    DocumentPerformance.startMeasure("bulkExport")

    const form = document.createElement("form")
    form.method = "POST"
    form.action = document.querySelector('meta[name="bulk-export-url"]')?.content || "/documents/bulk-export"
    form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="document_ids" value="${this.selectedDocuments.join(",")}">
        `
    document.body.appendChild(form)
    form.submit()
    document.body.removeChild(form)

    DocumentPerformance.endMeasure("bulkExport")
  },
}

// ============================================
// FILTER OPTIMIZATION
// ============================================

const FilterOptimization = {
  filterForm: null,
  debounceTimer: null,

  init() {
    this.filterForm = document.getElementById("advancedFiltersForm")
    if (this.filterForm) {
      this.attachDebouncedSearch()
    }
  },

  attachDebouncedSearch() {
    const searchInput = this.filterForm.querySelector('input[name="search"]')
    if (searchInput) {
      searchInput.addEventListener("input", () => {
        clearTimeout(this.debounceTimer)
        this.debounceTimer = setTimeout(() => {
          this.filterForm.submit()
        }, 500)
      })
    }
  },

  toggleAdvancedFilters() {
    const form = document.getElementById("advancedFiltersForm")
    const icon = document.getElementById("filterToggleIcon")

    if (form.style.display === "none") {
      form.style.display = "grid"
      icon.textContent = "−"
      localStorage.setItem("documentsFiltersOpen", "true")
    } else {
      form.style.display = "none"
      icon.textContent = "+"
      localStorage.setItem("documentsFiltersOpen", "false")
    }
  },

  restoreFilterState() {
    const isOpen = localStorage.getItem("documentsFiltersOpen") !== "false"
    if (!isOpen) {
      this.toggleAdvancedFilters()
    }
  },
}

// ============================================
// MODAL OPTIMIZATION
// ============================================

const ModalOptimization = {
  currentModal: null,

  openModal(modalId) {
    const modal = document.getElementById(modalId)
    if (modal) {
      this.currentModal = modal
      modal.style.display = "flex"
      document.body.style.overflow = "hidden"

      // Trap focus in modal
      this.trapFocus(modal)
    }
  },

  closeModal(modalId) {
    const modal = document.getElementById(modalId)
    if (modal) {
      modal.style.display = "none"
      document.body.style.overflow = "auto"
      this.currentModal = null
    }
  },

  trapFocus(modal) {
    const focusableElements = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
    )
    const firstElement = focusableElements[0]
    const lastElement = focusableElements[focusableElements.length - 1]

    modal.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.closeModal(modal.id)
      }
      if (e.key === "Tab") {
        if (e.shiftKey && document.activeElement === firstElement) {
          e.preventDefault()
          lastElement.focus()
        } else if (!e.shiftKey && document.activeElement === lastElement) {
          e.preventDefault()
          firstElement.focus()
        }
      }
    })
  },
}

// ============================================
// LAZY LOADING OPTIMIZATION
// ============================================

const LazyLoadOptimization = {
  init() {
    if ("IntersectionObserver" in window) {
      this.setupIntersectionObserver()
    }
  },

  setupIntersectionObserver() {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const element = entry.target
            if (element.dataset.src) {
              element.src = element.dataset.src
              element.removeAttribute("data-src")
              observer.unobserve(element)
            }
          }
        })
      },
      {
        rootMargin: "50px",
      },
    )

    document.querySelectorAll("img[data-src]").forEach((img) => {
      observer.observe(img)
    })
  },
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener("DOMContentLoaded", () => {
  DocumentPerformance.logMetrics()
  BulkOperations.init()
  FilterOptimization.init()
  FilterOptimization.restoreFilterState()
  LazyLoadOptimization.init()

  console.log("[v0] Document module initialized")
})

// ============================================
// GLOBAL FUNCTIONS (for inline onclick handlers)
// ============================================

function toggleAdvancedFilters() {
  FilterOptimization.toggleAdvancedFilters()
}

function toggleDocumentSelection(docId) {
  BulkOperations.toggleDocumentSelection(docId)
}

function toggleSelectAll() {
  BulkOperations.toggleSelectAll()
}

function clearSelection() {
  BulkOperations.clearSelection()
}

function bulkApprove() {
  BulkOperations.bulkApprove()
}

function bulkArchive() {
  BulkOperations.bulkArchive()
}

function bulkExport() {
  BulkOperations.bulkExport()
}

function openNewVersionModal() {
  ModalOptimization.openModal("newVersionModal")
}

function closeNewVersionModal() {
  ModalOptimization.closeModal("newVersionModal")
}
