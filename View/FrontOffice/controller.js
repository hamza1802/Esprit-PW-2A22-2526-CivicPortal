/**
 * controller.js
 * FrontOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRouting();
        view.renderNavBar(model.getCurrentUser().role);
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
            this.showEditRequest(requestId);
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
                view.renderMyRequests(model.getServiceRequests());
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
        const requests = model.getServiceRequests();
        const request = requests.find(r => r.id === requestId);
        if (!request) {
            view.renderToast('Request not found.', 'danger');
            window.location.hash = '#my-requests';
            return;
        }
        const documents = await model.getDocuments(requestId);
        view.renderRequestDetail(request, documents || []);
    },

    showEditRequest(requestId) {
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
        view.renderEditRequestForm(request);
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
            await this.showRequestDetail(requestId);
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
