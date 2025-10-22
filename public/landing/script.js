// Smooth scroll to form
function scrollToForm() {
  document.getElementById("form").scrollIntoView({ behavior: "smooth" })
}

// Smooth scroll to features
function scrollToFeatures() {
  document.getElementById("features").scrollIntoView({ behavior: "smooth" })
}

// Contact sales
function contactSales() {
  window.location.href = "mailto:sales@fsma204.com?subject=Enterprise%20Plan%20Inquiry"
}

// Form submission
document.getElementById("leadForm").addEventListener("submit", async function (e) {
  e.preventDefault()

  const formData = new FormData(this)
  const data = Object.fromEntries(formData)

  try {
    // Track conversion in Google Analytics
    const gtag = window.gtag // Declare gtag variable
    if (typeof gtag !== "undefined") {
      gtag("event", "lead_form_submission", {
        company_name: data.company_name,
        industry: data.industry,
        country: data.country,
      })
    }

    // Track conversion in Facebook Pixel
    const fbq = window.fbq // Declare fbq variable
    if (typeof fbq !== "undefined") {
      fbq("track", "Lead", {
        content_name: "FSMA 204 Free Trial",
        content_category: "Lead",
        value: 0,
        currency: "USD",
      })
    }

    // Send data to backend
    const response = await fetch("/api/leads", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
      },
      body: JSON.stringify(data),
    })

    if (response.ok) {
      const result = await response.json()

      // Show success message
      alert("Thank you! Check your email for login credentials.")

      // Redirect to signup page after 2 seconds
      setTimeout(() => {
        window.location.href = result.redirect_url || "/signup"
      }, 2000)
    } else {
      alert("An error occurred. Please try again.")
    }
  } catch (error) {
    console.error("Error:", error)
    alert("An error occurred. Please try again.")
  }
})

// Replace placeholder tracking IDs
document.addEventListener("DOMContentLoaded", () => {
  // Replace GA_MEASUREMENT_ID with actual ID from environment
  // Replace FACEBOOK_PIXEL_ID with actual ID from environment
  console.log("[v0] Landing page loaded successfully")
})
