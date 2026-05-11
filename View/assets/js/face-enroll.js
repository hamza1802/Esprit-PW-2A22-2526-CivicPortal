const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

class FaceEnrollment {
    constructor() {
        this.video = document.getElementById('enroll-video');
        this.canvas = document.getElementById('enroll-canvas');
        this.statusEl = document.getElementById('enroll-status');
        this.feedbackEl = document.getElementById('enroll-feedback');
        this.saveBtn = document.getElementById('enroll-save');
        this.descriptor = null;
        this.isModelLoaded = false;
        
        if (this.video) {
            this.init();
        }
    }

    stop() {
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
        }
        if (this.video && this.video.srcObject) {
            const tracks = this.video.srcObject.getTracks();
            tracks.forEach(track => track.stop());
            this.video.srcObject = null;
        }
    }

    async init() {
        if (!window.faceapi) {
            this.showFeedback('Face-API script not loaded. Please check your internet connection.', 'error');
            return;
        }
        this.updateStatus('Loading models...', 'scanning');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            this.isModelLoaded = true;
            this.updateStatus('Models loaded. Starting camera...', 'scanning');
            this.startCamera();
        } catch (err) {
            this.showFeedback('Error loading models: ' + err.message, 'error');
        }
    }

    async startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            this.video.srcObject = stream;
            this.video.onplay = () => this.onPlay();
        } catch (err) {
            this.showFeedback('Camera access denied: ' + err.message, 'error');
        }
    }

    async onPlay() {
        const displaySize = { width: this.video.offsetWidth, height: this.video.offsetHeight };
        faceapi.matchDimensions(this.canvas, displaySize);

        this.detectionInterval = setInterval(async () => {
            if (!this.isModelLoaded) return;

            const detections = await faceapi.detectAllFaces(this.video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            this.canvas.getContext('2d').clearRect(0, 0, this.canvas.width, this.canvas.height);
            faceapi.draw.drawDetections(this.canvas, resizedDetections);

            if (detections.length > 0) {
                this.descriptor = detections[0].descriptor;
                this.updateStatus('Face detected. Ready to save.', 'detected');
                this.saveBtn.disabled = false;
            } else {
                this.descriptor = null;
                this.updateStatus('Scanning for face...', 'scanning');
                this.saveBtn.disabled = true;
            }
        }, 100);
    }

    updateStatus(text, className) {
        if (this.statusEl) {
            this.statusEl.textContent = text;
            this.statusEl.className = 'face-id-status status-' + className;
        }
    }

    showFeedback(text, type) {
        if (this.feedbackEl) {
            this.feedbackEl.textContent = text;
            this.feedbackEl.className = 'face-id-feedback feedback-' + type;
        }
    }

    async saveFace() {
        if (!this.descriptor) return;

        this.showFeedback('Saving face data...', 'scanning');
        this.saveBtn.disabled = true;
        
        try {
            const response = await fetch('../../face_auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'enroll',
                    face_descriptor: Array.from(this.descriptor)
                })
            });

            const result = await response.json();
            if (result.success) {
                this.showFeedback('Face enrolled successfully!', 'success');
                this.updateStatus('Enrolled', 'matched');
                if (window.renderToast) {
                    window.renderToast('Face ID saved successfully!');
                } else {
                    alert('Face ID saved successfully!');
                }
            } else {
                this.showFeedback(result.message, 'error');
                this.saveBtn.disabled = false;
            }
        } catch (err) {
            this.showFeedback('Server error: ' + err.message, 'error');
            this.saveBtn.disabled = false;
        }
    }
}

// Global instance
window.faceEnrollment = null;

function initFaceEnrollment() {
    if (document.getElementById('enroll-video')) {
        // Clean up previous instance if exists
        if (window.faceEnrollment && window.faceEnrollment.detectionInterval) {
            clearInterval(window.faceEnrollment.detectionInterval);
        }
        window.faceEnrollment = new FaceEnrollment();
        document.getElementById('enroll-save').addEventListener('click', () => window.faceEnrollment.saveFace());
    }
}

// Still keep DOMContentLoaded for non-SPA pages
document.addEventListener('DOMContentLoaded', initFaceEnrollment);

// Export to window for SPA use
window.initFaceEnrollment = initFaceEnrollment;
