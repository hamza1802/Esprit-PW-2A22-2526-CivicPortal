const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

class FaceLogin {
    constructor() {
        this.modal    = document.getElementById('face-id-modal');
        this.video    = document.getElementById('login-video');
        this.canvas   = document.getElementById('login-canvas');
        this.statusEl = document.getElementById('login-status');
        this.feedbackEl = document.getElementById('login-feedback');
        this.emailInput = document.getElementById('email');
        this.stream   = null;
        this.interval = null;
        this.verifying = false;
        this.ready     = false;

        if (this.modal) this._loadModels();
    }

    async _loadModels() {
        if (!window.faceapi) { console.error('face-api not loaded'); return; }
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            this.ready = true;
        } catch (err) {
            console.error('face-api model load failed:', err);
        }
    }

    open() {
        const email = this.emailInput?.value.trim();
        if (!email) {
            this._mainError('Please enter your email first.');
            return;
        }
        this.modal.classList.add('active');
        this._status('Starting camera…', 'scanning');
        this._feedback('', '');
        this._startCamera();
    }

    close() {
        this.modal.classList.remove('active');
        this._stop();
    }

    async _startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            this.video.srcObject = this.stream;
            this.video.onloadedmetadata = () => {
                this.video.play();
                this._status('Scanning for face…', 'scanning');
                this._startDetection();
            };
        } catch (err) {
            this._feedback('Camera access denied: ' + err.message, 'error');
        }
    }

    _startDetection() {
        const size = { width: this.video.offsetWidth, height: this.video.offsetHeight };
        faceapi.matchDimensions(this.canvas, size);

        this.interval = setInterval(async () => {
            if (!this.ready || this.verifying) return;

            const detections = await faceapi
                .detectAllFaces(this.video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const ctx = this.canvas.getContext('2d');
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            faceapi.draw.drawDetections(this.canvas, faceapi.resizeResults(detections, size));

            if (detections.length > 0) {
                this._status('Face detected — verifying…', 'detected');
                this._verify(detections[0].descriptor);
            } else {
                this._status('Scanning for face…', 'scanning');
            }
        }, 600);
    }

    async _verify(descriptor) {
        this.verifying = true;
        const endpoint = window.FACE_AUTH_URL;
        if (!endpoint) {
            this._feedback('Configuration error: face auth URL not set.', 'error');
            setTimeout(() => { this.verifying = false; }, 2000);
            return;
        }

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'verify',
                    email: this.emailInput.value.trim(),
                    face_descriptor: Array.from(descriptor),
                }),
            });

            let result;
            const text = await res.text();
            try { result = JSON.parse(text); }
            catch { this._feedback('Server error: invalid response received.', 'error'); setTimeout(() => { this.verifying = false; }, 2000); return; }

            if (result.match) {
                this._status('Match found!', 'matched');
                this._feedback('Success — redirecting…', 'success');
                this._stop();
                setTimeout(() => { window.location.href = result.redirect; }, 800);
            } else {
                this._status('No match', 'failed');
                this._feedback(result.message || 'Face not recognized — please try again.', 'error');
                setTimeout(() => { this.verifying = false; }, 2500);
            }
        } catch (err) {
            this._feedback('Network error: ' + err.message, 'error');
            setTimeout(() => { this.verifying = false; }, 2000);
        }
    }

    _stop() {
        if (this.interval) { clearInterval(this.interval); this.interval = null; }
        if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
        this.verifying = false;
    }

    _status(text, cls) {
        if (this.statusEl) {
            this.statusEl.textContent = text;
            this.statusEl.className   = 'face-id-status status-' + cls;
        }
    }

    _feedback(text, cls) {
        if (this.feedbackEl) {
            this.feedbackEl.textContent = text;
            this.feedbackEl.className   = cls ? 'face-id-feedback feedback-' + cls : 'face-id-feedback';
        }
    }

    _mainError(msg) {
        const el = document.getElementById('error-email');
        if (el) { el.textContent = msg; return; }
        if (window.renderToast) { window.renderToast(msg, 'error'); return; }
        alert(msg);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('btn-face-id-login')) return;
    const fl = new FaceLogin();
    document.getElementById('btn-face-id-login').addEventListener('click', () => fl.open());
    document.getElementById('close-face-modal').addEventListener('click', () => fl.close());
});
