/**
 * controller.js
 * FrontOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    listFilters: {
        query: '',
        sortBy: 'date_desc'
    },

    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRouting();
        view.renderNavBar(model.getCurrentUser().role);
    },

    preserveInputFocus(renderFn) {
        const active = document.activeElement;
        const activeId = active?.id || null;
        const isInput = !!active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA'
        );
        const selectionStart = isInput ? active.selectionStart : null;
        const selectionEnd = isInput ? active.selectionEnd : null;

        renderFn();

        if (activeId) {
            const nextEl = document.getElementById(activeId);
            if (nextEl) {
                nextEl.focus();
                if (
                    typeof selectionStart === 'number' &&
                    typeof selectionEnd === 'number' &&
                    typeof nextEl.setSelectionRange === 'function'
                ) {
                    nextEl.setSelectionRange(selectionStart, selectionEnd);
                }
            }
        }
    },

    setFieldError(fieldId, message) {
        const errorEl = document.getElementById(`${fieldId}-error`);
        const fieldEl = document.getElementById(fieldId);
        if (errorEl) {
            errorEl.textContent = message || '';
            errorEl.style.display = message ? 'block' : 'none';
        }
        if (fieldEl) {
            if (message) fieldEl.classList.add('input-invalid');
            else fieldEl.classList.remove('input-invalid');
        }
    },

    validateServiceRequestForm(form) {
        let isValid = true;
        const titleEl = form.querySelector('#request-title');
        const descriptionEl = form.querySelector('#request-description');
        const fileInputs = form.querySelectorAll('.doc-file-input');

        const title = (titleEl?.value || '').trim();
        const description = (descriptionEl?.value || '').trim();

        this.setFieldError('request-title', '');
        this.setFieldError('request-description', '');

        if (!title) {
            this.setFieldError('request-title', 'Please select a service type.');
            isValid = false;
        }

        if (!description) {
            this.setFieldError('request-description', 'Description is required.');
            isValid = false;
        } else if (description.length < 10) {
            this.setFieldError('request-description', 'Description must be at least 10 characters.');
            isValid = false;
        } else if (description.length > 600) {
            this.setFieldError('request-description', 'Description must not exceed 600 characters.');
            isValid = false;
        }

        const allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        const maxSizeBytes = 5 * 1024 * 1024;
        fileInputs.forEach((input) => {
            const file = input.files?.[0];
            this.setFieldError(input.id, '');
            if (!file) {
                this.setFieldError(input.id, 'This supporting document is required.');
                isValid = false;
                return;
            }

            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowedExt.includes(ext)) {
                this.setFieldError(input.id, 'Only PDF, JPG, JPEG or PNG files are allowed.');
                isValid = false;
            } else if (file.size > maxSizeBytes) {
                this.setFieldError(input.id, 'File size must be 5MB or less.');
                isValid = false;
            }
        });

        return isValid;
    },

    setupEventListeners() {
        // ── Click Delegation ─────────────────────────────────────
        document.addEventListener('click', async (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'enroll') {
                this.handleEnrollment(id);
            }
            // Request actions
            else if (action === 'view-request') {
                window.location.hash = `#request-detail-${id}`;
            }
            else if (action === 'edit-request') {
                window.location.hash = `#edit-request-${id}`;
            }
            else if (action === 'delete-request') {
                await this.handleDeleteRequest(parseInt(id));
            }
            // Document actions (from detail view)
            else if (action === 'upload-document') {
                const requestId = target.dataset.requestId;
                view.showDocumentUploadModal(parseInt(requestId));
            }
            else if (action === 'replace-document') {
                const docId = target.dataset.docId;
                const requestId = target.dataset.requestId;
                const docType = target.dataset.docType;
                view.showDocumentUploadModal(parseInt(requestId), parseInt(docId), docType);
            }
            else if (action === 'delete-document') {
                const docId = target.dataset.docId;
                const requestId = target.dataset.requestId;
                await this.handleDeleteDocument(parseInt(docId), parseInt(requestId));
            }
            else if (action === 'close-modal') {
                view.closeModal();
            } else if (action === 'reset-request-filters') {
                this.listFilters = { query: '', sortBy: 'date_desc' };
                this.renderFilteredRequests();
            }

            // ── AI Assistant (FrontOffice) ───────────────────────
            const aiBtn = target.closest && target.closest('#ai-improve-btn, #ai-panel-close, #ai-dismiss, #ai-apply-description');
            if (aiBtn) {
                if (aiBtn.id === 'ai-improve-btn')        await this.handleAiImprove();
                else if (aiBtn.id === 'ai-panel-close' || aiBtn.id === 'ai-dismiss') view.closeAiPanel();
                else if (aiBtn.id === 'ai-apply-description') this.applyAiDescription();
            }
        });

        // ── Hash Routing ─────────────────────────────────────────
        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        // ── Form Submissions ─────────────────────────────────────
        document.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (e.target.id === 'service-request-form') {
                await this.handleServiceRequest(e.target);
            }
            else if (e.target.id === 'edit-request-form') {
                await this.handleEditRequest(e.target);
            }
            else if (e.target.id === 'document-upload-form') {
                await this.handleDocumentUpload(e.target);
            }
            else if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            }
            else if (e.target.id === 'complaint-form') {
                await this.handleComplaintSubmission(new FormData(e.target));
            }
        });

        // ── File input previews (delegated) ──────────────────────
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('doc-file-input')) {
                const file = e.target.files[0];
                const idx = e.target.id.replace('doc-file-', '');
                const preview = document.getElementById(`file-selected-${idx}`);
                if (file && preview) {
                    preview.style.display = 'flex';
                    preview.querySelector('.file-selected-name').textContent = file.name;
                    preview.querySelector('.file-selected-size').textContent = `(${(file.size / 1024).toFixed(1)} KB)`;
                }
            }
            if (e.target.id === 'request-sort') {
                this.listFilters.sortBy = e.target.value;
                this.renderFilteredRequests();
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'request-search') {
                this.listFilters.query = e.target.value;
                this.renderFilteredRequests();
            }
        });
    },

    // ═════════════════════════════════════════════════════════════
    //  ROUTING
    // ═════════════════════════════════════════════════════════════

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();

        // Dynamic routes: #request-detail-{id}, #edit-request-{id}
        if (hash.startsWith('#request-detail-')) {
            const requestId = parseInt(hash.replace('#request-detail-', ''));
            await this.showRequestDetail(requestId);
            return;
        }
        if (hash.startsWith('#edit-request-')) {
            const requestId = parseInt(hash.replace('#edit-request-', ''));
            await this.showEditRequest(requestId);
            return;
        }

        switch (hash) {
            case '#home':
                view.renderHome(user);
                break;
            case '#programs':
                view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
                break;
            case '#request-service':
                view.renderServiceRequestForm();
                break;
            case '#my-requests':
                await model.sync();
                this.renderFilteredRequests();
                break;
            case '#profile':
                view.renderProfile(user);
                break;
            case '#complaints':
                view.renderComplaintForm();
                break;
            default:
                view.renderHome(user);
                break;
        }
    },

    // ═════════════════════════════════════════════════════════════
    //  REQUEST HANDLERS
    // ═════════════════════════════════════════════════════════════

    async handleServiceRequest(form) {
        const isValid = this.validateServiceRequestForm(form);
        if (!isValid) {
            view.renderToast('Please correct highlighted fields.', 'danger');
            return;
        }

        const user = model.getCurrentUser();
        const title = form.querySelector('[name="title"]').value;
        const description = form.querySelector('[name="description"]').value;

        // 1. Create the request first
        const newRequest = await model.addServiceRequest({ title, description, userId: user.id });
        if (!newRequest) {
            view.renderToast('Failed to create request.', 'danger');
            return;
        }

        // 2. Upload all attached documents for this request
        const fileInputs = form.querySelectorAll('.doc-file-input');
        const files = [];
        const docTypes = [];

        for (const input of fileInputs) {
            const file = input.files[0];
            if (file) {
                files.push(file);
                docTypes.push(input.dataset.doctype || 'other');
            }
        }

        if (files.length > 0) {
            const uploadResult = await model.addDocuments(newRequest.id, files, docTypes);
            if (uploadResult) {
                view.renderToast(`Request submitted with ${files.length} document(s)!`);
            } else {
                view.renderToast('Request submitted, but some files failed to upload.', 'danger');
            }
        } else {
            view.renderToast('Request submitted successfully!');
        }
        window.location.hash = '#my-requests';
    },

    async showRequestDetail(requestId) {
        await model.sync();
        const requests = model.getServiceRequests();
        const request = requests.find(r => r.id === requestId);
        if (!request) {
            view.renderToast('Request not found.', 'danger');
            window.location.hash = '#my-requests';
            return;
        }
        const documents = await model.getDocuments(requestId);
        const logs = await model.getRequestAuditLogs(requestId);
        view.renderRequestDetail(request, documents || [], logs || []);
    },

    renderFilteredRequests() {
        const requests = model.getServiceRequests();
        const q = this.listFilters.query.trim().toLowerCase();
        const sortBy = this.listFilters.sortBy;

        let filtered = requests.filter((r) => {
            if (!q) return true;
            return (
                String(r.id).includes(q) ||
                (r.title || '').toLowerCase().includes(q) ||
                (r.description || '').toLowerCase().includes(q) ||
                (r.status || '').toLowerCase().includes(q)
            );
        });

        filtered.sort((a, b) => {
            const da = new Date(a.createdAt).getTime();
            const db = new Date(b.createdAt).getTime();

            if (sortBy === 'date_asc') return da - db;
            if (sortBy === 'status_asc') return (a.status || '').localeCompare(b.status || '');
            if (sortBy === 'status_desc') return (b.status || '').localeCompare(a.status || '');
            return db - da; // default date_desc
        });

        this.preserveInputFocus(() => {
            view.renderMyRequests(filtered, this.listFilters);
        });
    },

    async showEditRequest(requestId) {
        const requests = model.getServiceRequests();
        const request = requests.find(r => r.id === requestId);
        if (!request) {
            view.renderToast('Request not found.', 'danger');
            window.location.hash = '#my-requests';
            return;
        }
        if (request.status !== 'pending') {
            view.renderToast('Only pending requests can be edited.', 'danger');
            window.location.hash = '#my-requests';
            return;
        }
        const documents = await model.getDocuments(requestId);
        view.renderEditRequestForm(request, documents || []);
    },

    async handleEditRequest(form) {
        const requestId = parseInt(form.dataset.requestId);
        const description = form.querySelector('[name="description"]').value;
        const result = await model.updateServiceRequest(requestId, description);
        if (result) {
            view.renderToast('Request updated successfully!');
            window.location.hash = `#request-detail-${requestId}`;
        } else {
            view.renderToast('Failed to update request.', 'danger');
        }
    },

    async handleDeleteRequest(requestId) {
        const confirmed = await view.showConfirmDialog(
            'Are you sure you want to delete this request? All attached documents will also be removed.'
        );
        if (!confirmed) return;

        const result = await model.deleteServiceRequest(requestId);
        if (result) {
            view.renderToast('Request deleted successfully.');
            window.location.hash = '#my-requests';
            // Force re-render if already on that hash
            view.renderMyRequests(model.getServiceRequests());
        } else {
            view.renderToast('Failed to delete request.', 'danger');
        }
    },

    // ═════════════════════════════════════════════════════════════
    //  DOCUMENT HANDLERS (from detail view modal)
    // ═════════════════════════════════════════════════════════════

    async handleDocumentUpload(form) {
        const requestId = parseInt(form.dataset.requestId);
        const docId = form.dataset.docId ? parseInt(form.dataset.docId) : null;
        const docType = form.querySelector('[name="docType"]').value;
        const fileInput = form.querySelector('[name="file"]');
        const file = fileInput.files[0];

        if (!file) {
            view.renderToast('Please select a file.', 'danger');
            return;
        }

        let result;
        if (docId) {
            result = await model.replaceDocument(docId, requestId, file, docType);
        } else {
            result = await model.addDocuments(requestId, [file], [docType]);
        }

        view.closeModal();

        if (result) {
            view.renderToast(docId ? 'Document replaced successfully!' : 'Document uploaded successfully!');
            if ((window.location.hash || '').startsWith('#edit-request-')) {
                await this.showEditRequest(requestId);
            } else {
                await this.showRequestDetail(requestId);
            }
        } else {
            view.renderToast('Failed to process document.', 'danger');
        }
    },

    async handleDeleteDocument(docId, requestId) {
        const confirmed = await view.showConfirmDialog('Are you sure you want to delete this document?');
        if (!confirmed) return;

        const result = await model.deleteDocument(docId);
        if (result) {
            view.renderToast('Document deleted successfully.');
            await this.showRequestDetail(requestId);
        } else {
            view.renderToast('Failed to delete document.', 'danger');
        }
    },

    // ═════════════════════════════════════════════════════════════
    //  OTHER HANDLERS
    // ═════════════════════════════════════════════════════════════

    handleEnrollment(programId) {
        const user = model.getCurrentUser();
        model.addEnrollment(user.id, parseInt(programId));
        view.renderToast('Enrolled in program!');
        view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
    },

    handleProfileUpdate(formData) {
        const data = {
            name: formData.get('name'),
            email: formData.get('email')
        };
        model.updateUser(data);
        view.renderToast('Profile updated locally!');
        window.location.hash = '#home';
    },

    // ═════════════════════════════════════════════════════════════
    //  AI ASSISTANT (FrontOffice)
    // ═════════════════════════════════════════════════════════════

    /** Maximum per-file size we send inline to the AI (raw bytes). */
    _AI_INLINE_MAX_BYTES: 3 * 1024 * 1024,
    /** Cumulative budget across all attached files (raw bytes). */
    _AI_INLINE_TOTAL_BUDGET: 6 * 1024 * 1024,

    /** Read a File as base64 (without the data:...;base64, prefix). */
    _readFileAsBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const result = String(reader.result || '');
                const idx = result.indexOf(',');
                resolve(idx >= 0 ? result.slice(idx + 1) : result);
            };
            reader.onerror = () => reject(reader.error);
            reader.readAsDataURL(file);
        });
    },

    /** Best-effort MIME guess from the filename when File.type is empty. */
    _guessMime(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        return ({
            pdf:  'application/pdf',
            png:  'image/png',
            jpg:  'image/jpeg',
            jpeg: 'image/jpeg',
            webp: 'image/webp',
            gif:  'image/gif'
        })[ext] || 'application/octet-stream';
    },

    async handleAiImprove() {
        const titleEl = document.getElementById('request-title');
        const descEl  = document.getElementById('request-description');
        const btn     = document.getElementById('ai-improve-btn');
        if (!descEl) return;

        const serviceType = titleEl?.value || '';
        const description = descEl.value || '';

        if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
        view.renderAiSuggestionLoading();

        // Build a closed list of (label, provided?, fileName, type) plus the
        // inline file content (base64) so Gemini can actually inspect the
        // attachments and detect a wrong document.
        // We respect both a per-file cap and a cumulative budget so the
        // resulting JSON stays under typical PHP `post_max_size`.
        const inputs = Array.from(document.querySelectorAll('.doc-file-input'));
        let remainingBudget = this._AI_INLINE_TOTAL_BUDGET;
        const requiredDocuments = await Promise.all(inputs.map(async (input) => {
            const file = input.files?.[0] || null;
            const entry = {
                label:    input.dataset.label || '',
                type:     input.dataset.doctype || 'other',
                provided: !!file,
                fileName: file ? file.name : ''
            };
            if (file) {
                if (file.size > this._AI_INLINE_MAX_BYTES || file.size > remainingBudget) {
                    entry.tooLarge = true;
                } else {
                    try {
                        entry.mimeType   = file.type || this._guessMime(file.name);
                        entry.base64Data = await this._readFileAsBase64(file);
                        remainingBudget -= file.size;
                    } catch (_e) { /* ignore — fall back to filename-only */ }
                }
            }
            return entry;
        }));

        const result = await model.aiImproveDescription(serviceType, description, requiredDocuments);

        if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }
        view.renderAiSuggestion(result);

        this._lastAiResult = result;
    },

    applyAiDescription() {
        const result = this._lastAiResult;
        const descEl = document.getElementById('request-description');
        if (!result || !descEl) return;
        const improved = (result.improvedDescription || '').trim();
        if (!improved) {
            view.renderToast('Aucun texte à appliquer.', 'danger');
            return;
        }
        descEl.value = improved;
        descEl.dispatchEvent(new Event('input', { bubbles: true }));
        view.renderToast('Description mise à jour.');
        view.closeAiPanel();
    },

    async handleComplaintSubmission(formData) {
        const user = model.getCurrentUser();
        const subject = formData.get('subject');
        const body = formData.get('body');
        await model.addComplaint(subject, body, user.id);
        view.renderToast('Grievance logged in PHP session.');
        window.location.hash = '#home';
    }
};

export default controller;
