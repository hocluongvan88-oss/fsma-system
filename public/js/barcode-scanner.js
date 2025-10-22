class BarcodeScanner {
  constructor(options = {}) {
    this.videoElement = options.videoElement
    this.onScan = options.onScan || (() => {})
    this.onError = options.onError || (() => {})
    this.stream = null
    this.scanning = false
    this.codeReader = null
  }

  async start() {
    try {
      // Request camera permission
      this.stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "environment" },
      })

      this.videoElement.srcObject = this.stream
      this.videoElement.play()

      // Load ZXing library dynamically
      if (!window.ZXing) {
        await this.loadZXing()
      }

      this.scanning = true
      this.scanFrame()
    } catch (error) {
      this.onError("Camera access denied or not available: " + error.message)
    }
  }

  async loadZXing() {
    return new Promise((resolve, reject) => {
      const script = document.createElement("script")
      script.src = "https://unpkg.com/@zxing/library@latest/umd/index.min.js"
      script.onload = resolve
      script.onerror = reject
      document.head.appendChild(script)
    })
  }

  async scanFrame() {
    if (!this.scanning) return

    try {
      const canvas = document.createElement("canvas")
      canvas.width = this.videoElement.videoWidth
      canvas.height = this.videoElement.videoHeight
      const ctx = canvas.getContext("2d")
      ctx.drawImage(this.videoElement, 0, 0)

      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)

      if (window.ZXing) {
        const ZXing = window.ZXing // Declare the variable here
        const codeReader = new ZXing.BrowserMultiFormatReader()
        const result = await codeReader.decodeFromImageData(imageData)

        if (result) {
          this.onScan(result.text)
          return
        }
      }
    } catch (error) {
      // No barcode found, continue scanning
    }

    // Continue scanning
    requestAnimationFrame(() => this.scanFrame())
  }

  stop() {
    this.scanning = false

    if (this.stream) {
      this.stream.getTracks().forEach((track) => track.stop())
      this.stream = null
    }

    if (this.videoElement) {
      this.videoElement.srcObject = null
    }
  }
}

// Keyboard barcode scanner support
class KeyboardBarcodeScanner {
  constructor(options = {}) {
    this.onScan = options.onScan || (() => {})
    this.buffer = ""
    this.timeout = null
    this.timeoutDuration = options.timeoutDuration || 100
    this.minLength = options.minLength || 5

    this.handleKeyPress = this.handleKeyPress.bind(this)
    document.addEventListener("keypress", this.handleKeyPress)
  }

  handleKeyPress(event) {
    // Ignore if user is typing in an input field
    if (event.target.tagName === "INPUT" || event.target.tagName === "TEXTAREA") {
      return
    }

    clearTimeout(this.timeout)

    if (event.key === "Enter") {
      if (this.buffer.length >= this.minLength) {
        this.onScan(this.buffer)
      }
      this.buffer = ""
    } else {
      this.buffer += event.key

      this.timeout = setTimeout(() => {
        this.buffer = ""
      }, this.timeoutDuration)
    }
  }

  destroy() {
    document.removeEventListener("keypress", this.handleKeyPress)
    clearTimeout(this.timeout)
  }
}

// API helper functions
async function scanBarcode(barcode) {
  const response = await fetch("/api/barcode/scan", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ barcode }),
  })

  return await response.json()
}

async function validateTLC(tlc) {
  const response = await fetch("/api/barcode/validate", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ tlc }),
  })

  return await response.json()
}
