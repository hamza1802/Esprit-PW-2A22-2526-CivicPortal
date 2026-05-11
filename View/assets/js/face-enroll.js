const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';

class FaceEnrollment {
    constructor() {
        this.video      = document.getElementById('enroll-video');
        this.canvas     = document.getElementById('enroll-canvas');
        this.statusEl   = document.getElementById('enroll-status');
        this.feedbackEl = document.getElementById('enroll-feedback');
        this.saveBtn    = document.getElementById('enroll-save');
        this.descriptor = null;
        this.interval   = null;
        this.ready      = false;

        if (this.video) this._init();
    }

    async _init() {
        if (!window.faceapi) {
            this._feedback('Face-API script not loaded — check your internet connection.', 'error');
            return;
        }
        this._status('Loading models…', 'scanning');
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            this.ready = true;
            this._status('Camera starting…', 'scanning');
            this._startCamera();
        } catch (err) {
            this._feedback('Model load error: ' + err.message, 'error');
        }
    }

    async _startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            this.video.srcObject = stream;
            this.video.onloadedmetadata = () => {
                this.video.play();
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
            if (!this.ready) return;

            const detections = await faceapi
                .detectAllFaces(this.video)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const ctx = this.canvas.getContext('2d');
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            faceapi.draw.drawDetections(this.canvas, faceapi.resizeResults(detections, size));

            if (detections.length > 0) {
                this.descriptor = detections[0].descriptor;
                this._status('Face detected — ready to save.', 'detected');
                if (this.saveBtn) this.saveBtn.disabled = false;
            } else {
                this.descriptor = null;
                this._status('Scanning for face…', 'scanning');
                if (this.saveBtn) this.saveBtn.disabled = true;
            }
        }, 150);
    }

    async save() {
        if (!this.descriptor) return;
        const endpoint = window.FACE_AUTH_URL;
        if (!endpoint) {
            this._feedback('Configuration error: face auth URL not set.', 'error');
            return;
        }

        this._feedback('Saving…', 'scanning');
        if (this.saveBtn) this.saveBtn.disabled = true;

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'enroll',
                    face_descriptor: Array.from(this.descriptor),
                }),
            });

            let result;
            const text = await res.text();
            try { result = JSON.parse(text); }
            catch { this._feedback('Server error: invalid response received.', 'error'); if (this.saveBtn) this.saveBtn.disabled = false; return; }

            if (result.success) {
                this._status('Enrolled!', 'matched');
                this._feedback('Face ID saved successfully!', 'success');
                if (window.renderToast) window.renderToast('Face ID saved!', 'success');
            } else {
                this._feedback(result.message || 'Enrollment failed — please try again.', 'error');
                if (this.saveBtn) this.saveBtn.disabled = false;
            }
        } catch (err) {
            this._feedback('Network error: ' + err.message, 'error');
            if (this.saveBtn) this.saveBtn.disabled = false;
        }
    }

    stop() {
        if (this.interval) { clearInterval(this.interval); this.interval = null; }
        if (this.video?.srcObject) {
            this.video.srcObject.getTracks().forEach(t => t.stop());
            this.video.srcObject = null;
        }
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
}

// Global instance for SPA lifecycle management
window.faceEnrollment = null;

function initFaceEnrollment() {
    if (!document.getElementById('enroll-video')) return;
    if (window.faceEnrollment) window.faceEnrollment.stop();
    window.faceEnrollment = new FaceEnrollment();

    const btn = document.getElementById('enroll-save');
    if (btn) btn.addEventListener('click', () => window.faceEnrollment.save());
}

window.initFaceEnrollment = initFaceEnrollment;
document.addEventListener('DOMContentLoaded', initFaceEnrollment);
