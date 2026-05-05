const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

class FaceLogin {
    constructor() {
        this.modal = document.getElementById('face-id-modal');
        this.video = document.getElementById('login-video');
        this.canvas = document.getElementById('login-canvas');
        this.statusEl = document.getElementById('login-status');
        this.feedbackEl = document.getElementById('login-feedback');
        this.emailInput = document.getElementById('email');
        
        this.isModelLoaded = false;
        this.isVerifying = false;
        this.stream = null;
        
        if (this.modal) {
            this.init();
        }
    }

    async init() {
        if (!window.faceapi) {
            console.error('Face-API script not loaded.');
            return;
        }
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            this.isModelLoaded = true;
        } catch (err) {
            console.error('Error loading models:', err);
        }
    }

    async openModal() {
        const email = this.emailInput.value.trim();
        if (!email) {
            this.showMainError('Please enter your email first.');
            return;
        }

        this.modal.classList.add('active');
        this.updateStatus('Starting camera...', 'scanning');
        this.startCamera();
    }

    showMainError(msg) {
        const errorEl = document.getElementById('error-email');
        if (errorEl) {
            errorEl.textContent = msg;
        } else {
            alert(msg);
        }
    }

    closeModal() {
        this.modal.classList.remove('active');
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
        }
    }

    async startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            this.video.srcObject = this.stream;
            this.video.onplay = () => this.onPlay();
        } catch (err) {
            this.showFeedback('Camera access denied: ' + err.message, 'error');
        }
    }

    async onPlay() {
        const displaySize = { width: this.video.offsetWidth, height: this.video.offsetHeight };
        faceapi.matchDimensions(this.canvas, displaySize);

        this.detectionInterval = setInterval(async () => {
            if (!this.isModelLoaded || this.isVerifying) return;

            const detections = await faceapi.detectAllFaces(this.video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            this.canvas.getContext('2d').clearRect(0, 0, this.canvas.width, this.canvas.height);
            faceapi.draw.drawDetections(this.canvas, resizedDetections);

            if (detections.length > 0) {
                this.updateStatus('Face detected. Verifying...', 'detected');
                this.verifyFace(detections[0].descriptor);
            } else {
                this.updateStatus('Scanning for face...', 'scanning');
            }
        }, 500);
    }

    async verifyFace(descriptor) {
        this.isVerifying = true;
        
        try {
            const response = await fetch('face_auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'verify',
                    email: this.emailInput.value.trim(),
                    face_descriptor: Array.from(descriptor)
                })
            });

            const result = await response.json();
            if (result.match) {
                this.updateStatus('Match Found!', 'matched');
                this.showFeedback('Success! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            } else {
                this.updateStatus('Match Failed', 'failed');
                this.showFeedback(result.message || 'Face not recognized.', 'error');
                // Wait 2 seconds before allowing next attempt
                setTimeout(() => {
                    this.isVerifying = false;
                }, 2000);
            }
        } catch (err) {
            this.showFeedback('Server error: ' + err.message, 'error');
            setTimeout(() => {
                this.isVerifying = false;
            }, 2000);
        }
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
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('btn-face-id-login')) {
        const faceLogin = new FaceLogin();
        document.getElementById('btn-face-id-login').addEventListener('click', () => faceLogin.openModal());
        document.getElementById('close-face-modal').addEventListener('click', () => faceLogin.closeModal());
    }
});
