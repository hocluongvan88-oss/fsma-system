<div id="barcodeScannerModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.95); z-index: 2000;">
    <div style="max-width: 600px; margin: 2rem auto; padding: 2rem;">
        <div style="background: var(--bg-secondary); border-radius: 1rem; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 600;">Scan Barcode</h2>
                <button onclick="closeBarcodeScanner()" class="btn btn-secondary">Close</button>
            </div>
            
            <div style="position: relative; background: #000; border-radius: 0.5rem; overflow: hidden; margin-bottom: 1rem;">
                <video id="barcodeScannerVideo" style="width: 100%; height: auto; display: block;"></video>
                <div id="scannerOverlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; height: 60%; border: 2px solid var(--accent-primary); border-radius: 0.5rem; pointer-events: none;">
                    <div style="position: absolute; top: -2px; left: -2px; width: 20px; height: 20px; border-top: 4px solid var(--accent-primary); border-left: 4px solid var(--accent-primary);"></div>
                    <div style="position: absolute; top: -2px; right: -2px; width: 20px; height: 20px; border-top: 4px solid var(--accent-primary); border-right: 4px solid var(--accent-primary);"></div>
                    <div style="position: absolute; bottom: -2px; left: -2px; width: 20px; height: 20px; border-bottom: 4px solid var(--accent-primary); border-left: 4px solid var(--accent-primary);"></div>
                    <div style="position: absolute; bottom: -2px; right: -2px; width: 20px; height: 20px; border-bottom: 4px solid var(--accent-primary); border-right: 4px solid var(--accent-primary);"></div>
                </div>
            </div>
            
            <div id="scannerStatus" style="text-align: center; color: var(--text-secondary); font-size: 0.875rem;">
                Position barcode within the frame
            </div>
            
            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Or enter manually:</div>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="manualBarcodeInput" class="form-input" placeholder="Enter barcode or TLC" style="flex: 1;">
                    <button onclick="submitManualBarcode()" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/barcode-scanner.js"></script>
<script>
let barcodeScanner = null;

function openBarcodeScanner(callback) {
    window.barcodeScanCallback = callback;
    document.getElementById('barcodeScannerModal').style.display = 'block';
    
    const video = document.getElementById('barcodeScannerVideo');
    barcodeScanner = new BarcodeScanner({
        videoElement: video,
        onScan: (barcode) => {
            handleScannedBarcode(barcode);
        },
        onError: (error) => {
            document.getElementById('scannerStatus').innerHTML = 
                '<span style="color: var(--error);">' + error + '</span>';
        }
    });
    
    barcodeScanner.start();
}

function closeBarcodeScanner() {
    if (barcodeScanner) {
        barcodeScanner.stop();
        barcodeScanner = null;
    }
    document.getElementById('barcodeScannerModal').style.display = 'none';
    document.getElementById('manualBarcodeInput').value = '';
}

async function handleScannedBarcode(barcode) {
    document.getElementById('scannerStatus').innerHTML = 
        '<span style="color: var(--accent-primary);">Processing: ' + barcode + '</span>';
    
    try {
        const result = await scanBarcode(barcode);
        
        if (result.success && window.barcodeScanCallback) {
            window.barcodeScanCallback(result.data);
            closeBarcodeScanner();
        } else {
            document.getElementById('scannerStatus').innerHTML = 
                '<span style="color: var(--error);">Barcode not recognized</span>';
        }
    } catch (error) {
        document.getElementById('scannerStatus').innerHTML = 
            '<span style="color: var(--error);">Error: ' + error.message + '</span>';
    }
}

function submitManualBarcode() {
    const barcode = document.getElementById('manualBarcodeInput').value.trim();
    if (barcode) {
        handleScannedBarcode(barcode);
    }
}

// Allow Enter key to submit manual barcode
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('manualBarcodeInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitManualBarcode();
            }
        });
    }
});
</script>
<?php /**PATH /home/fxiasdcg/root/resources/views/components/barcode-scanner-modal.blade.php ENDPATH**/ ?>