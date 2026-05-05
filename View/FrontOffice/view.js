/**
 * view.js
 * FrontOffice rendering logic
 */

// ── Required documents per request type ──────────────────────────
const REQUIRED_DOCS = {
    'Birth Certificate': [
        { label: "Copy of Parents' IDs", accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' }
    ],
    'ID Card Renewal': [
        { label: 'Old ID Card (scan/photo)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Recent Passport Photo', accept: '.jpg,.jpeg,.png', docType: 'photo' }
    ],
    'Residence Certificate': [
        { label: 'Proof of Address (utility bill / lease)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'proof' }
    ],
    'Building Permit': [
        { label: 'Property Deed', accept: '.pdf', docType: 'proof' },
        { label: 'Building Plans', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' }
    ],
    'Marriage Certificate': [],
    'Tax Declaration': [],

    'Death Certificate': [
        { label: "Applicant's valid ID (national ID or passport)", accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Proof of relationship to the deceased (family book, birth cert., etc.)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'certificate' },
        { label: 'Medical certificate of death or hospital attestation (if applicable)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' }
    ],
    'Divorce Certificate': [
        { label: 'Valid ID of applicant', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Original or certified copy of marriage certificate', accept: '.pdf,.jpg,.jpeg,.png', docType: 'certificate' },
        { label: 'Court judgment or mutual consent agreement (if already issued)', accept: '.pdf', docType: 'other' }
    ],
    'Passport': [
        { label: 'Valid national ID or previous passport', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Recent biometric photo (white background)', accept: '.jpg,.jpeg,.png', docType: 'photo' },
        { label: 'Proof of address (≤ 3 months)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'proof' }
    ],
    'Certificate of Nationality': [
        { label: 'Birth certificate (full copy)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'certificate' },
        { label: "Parents' birth certificates or nationality proof (if applicable)", accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' },
        { label: 'Valid ID of applicant', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' }
    ],
    'Income Certificate': [
        { label: 'Last 3 pay slips or employer certificate', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' },
        { label: 'Tax notice or tax return (last year)', accept: '.pdf', docType: 'other' },
        { label: 'Valid ID', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' }
    ],
    'Business Registration': [
        { label: 'ID of legal representative / manager', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Draft articles of association or company statutes', accept: '.pdf', docType: 'other' },
        { label: 'Proof of business address (lease, utility, domiciliation)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'proof' }
    ],
    'Property Ownership': [
        { label: 'Title deed or preliminary sale agreement', accept: '.pdf', docType: 'proof' },
        { label: 'Valid ID of owner or buyer', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Property tax notice or cadastral reference (if available)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' }
    ],
    'Land Registry Extract': [
        { label: 'Parcel / plot reference or full address', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' },
        { label: 'Valid ID of applicant', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Proof of legitimate interest (deed, mandate, court order)', accept: '.pdf', docType: 'proof' }
    ],
    'Criminal Record Extract (Bulletin n°3)': [
        { label: 'Valid ID (both sides)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Proof of address (≤ 3 months)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'proof' }
    ],
    'Court Judgments': [
        { label: 'Valid ID of applicant or legal representative', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Case / docket number or court reference', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' },
        { label: 'Power of attorney (if requesting on behalf of someone)', accept: '.pdf', docType: 'other' }
    ],
    'Legal Certificates': [
        { label: 'Valid ID', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Written request stating exact certificate needed', accept: '.pdf', docType: 'other' },
        { label: 'Supporting deed, contract, or prior court/administrative decision', accept: '.pdf', docType: 'certificate' }
    ],
    'Vehicle Registration': [
        { label: 'Valid ID of owner', accept: '.pdf,.jpg,.jpeg,.png', docType: 'identity' },
        { label: 'Certificate of conformity (COC) or purchase invoice', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' },
        { label: 'Proof of address', accept: '.pdf,.jpg,.jpeg,.png', docType: 'proof' },
        { label: 'Insurance certificate (green card / attestation)', accept: '.pdf,.jpg,.jpeg,.png', docType: 'other' }
    ]
};

const view = {
    app: document.getElementById('app'),

    triggerObserver() {
        if (window.initScrollObserver) {
            setTimeout(() => window.initScrollObserver(), 50);
        }
    },

    renderToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerText = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    renderNavBar(role) {
        const nav = document.querySelector('nav');
        const links = `
            <div class="nav-brand">
                CivicPortal
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="#request-service">new request</a></li>
                <li><a href="#my-requests">my requests</a></li>
                <li><a href="#complaints">grievances</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                <div class="context-menu-wrapper">
                    <button id="context-toggle-btn" class="user-role-badge context-toggle-btn" type="button" title="Switch portal/role">
                        Citizen
                    </button>
                    <div id="context-menu" class="context-menu" style="display:none;">
                        <button type="button" data-role="citizen" class="context-menu-item">Citizen</button>
                        <button type="button" data-role="worker" class="context-menu-item">Worker</button>
                        <button type="button" data-role="admin" class="context-menu-item">Admin</button>
                    </div>
                </div>
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        const content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>CivicPortal</h1>
                    <p>Welcome back, ${user.name}. Navigate municipal services with unmatched clarity and precision.</p>
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="search services, programs, documents...">
                        <button class="search-btn" onclick="alert('Search simulated!')">Search</button>
                    </div>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">Directory of Services</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Document Submission</h3>
                        <p>Submit critical civil documents securely online. Ensure civic records are updated without the need for physical visitation.</p>
                        <a href="#request-service" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Start Filing</a>
                    </div>
                    
                    <div class="editorial-card reveal">
                        <h3>My Requests</h3>
                        <p>Track and manage all your submitted service requests. View status, edit details, and attach supporting documents.</p>
                        <a href="#my-requests" class="btn" style="align-self: flex-start; margin-top: auto;">View Requests</a>
                    </div>

                    <div class="editorial-card reveal">
                        <h3>Community Programs</h3>
                        <p>Engage with local initiatives. Our Parks & Recreation catalog lists the latest activities sponsored by the city.</p>
                        <a href="#programs" class="btn" style="align-self: flex-start; margin-top: auto;">View Catalog</a>
                    </div>

                    <div class="editorial-card reveal">
                        <h3>Grievances & Feedback</h3>
                        <p>Your voice matters. Submit complaints or feedback directly to the administration for review.</p>
                        <a href="#complaints" class="btn" style="align-self: flex-start; margin-top: auto;">Submit Grievance</a>
                    </div>
                </div>
            </section>
        `;
        this.app.innerHTML = content;
        this.triggerObserver();
    },

    renderProgramCatalog(programs, userEnrollments) {
        const programCards = programs.map((p) => {
            const isEnrolled = userEnrollments.some(e => e.programId === p.id);
            return `
                <div class="program-card reveal">
                    <div class="program-img-wrapper">
                        <img src="../assets/images/${p.image || 'default.jpg'}" alt="${p.title}" class="program-img" onerror="this.src=''; this.style.backgroundColor='var(--primary-navy)';">
                    </div>
                    <div class="card-content">
                        <span class="category-badge">${p.category}</span>
                        <h3>${p.title}</h3>
                        <p>${p.description}</p>
                        <button class="btn ${isEnrolled ? 'btn-success' : 'btn-primary'}" 
                                style="width: 100%"
                                data-id="${p.id}" 
                                data-action="enroll"
                                ${isEnrolled ? 'disabled' : ''}>
                            ${isEnrolled ? 'ENROLLED' : 'ENROLL'}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Programs Catalog</h2>
                <div class="editorial-grid">
                    ${programCards}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── New Request Form (with type-specific document uploads) ───

    renderServiceRequestForm() {
        const titleOptions = Object.keys(REQUIRED_DOCS).map(title =>
            `<option value="${title}">${title}</option>`
        ).join('');

        // Build initial docs section for the first type
        const firstTitle = Object.keys(REQUIRED_DOCS)[0];
        const initialDocsHtml = this._buildDocsFields(firstTitle);

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">File a New Request</h2>
                <div class="form-card reveal">
                    <form id="service-request-form">
                        <div class="form-group reveal">
                            <label for="request-title">Service Type (Title)</label>
                            <select id="request-title" name="title" required aria-describedby="request-title-error">
                                ${titleOptions}
                            </select>
                            <small id="request-title-error" class="form-error" style="display:none;"></small>
                        </div>
                        <div class="form-group reveal">
                            <label for="request-description">Description</label>
                            <textarea id="request-description" name="description" rows="4" required
                                minlength="10"
                                maxlength="600"
                                aria-describedby="request-description-error"
                                placeholder="Describe the details of your request..."></textarea>
                            <small id="request-description-error" class="form-error" style="display:none;"></small>
                            <div class="ai-toolbar">
                                <button type="button" id="ai-improve-btn" class="btn btn-ai">
                                    <span class="ai-icon" aria-hidden="true">✨</span>
                                    Améliorer avec IA
                                </button>
                                <small class="ai-hint">L'IA reformule votre texte et propose les pièces utiles. Vous décidez.</small>
                            </div>
                            <div id="ai-suggestion-panel" class="ai-panel" hidden>
                                <div class="ai-panel-header">
                                    <strong>Suggestion IA</strong>
                                    <button type="button" class="ai-panel-close" id="ai-panel-close" aria-label="Fermer">×</button>
                                </div>
                                <div class="ai-panel-body" id="ai-panel-body"></div>
                            </div>
                        </div>

                        <div id="documents-section" class="reveal">
                            ${initialDocsHtml}
                        </div>

                        <button type="submit" class="btn btn-primary reveal" style="width: 100%; margin-top: 1rem;">SUBMIT REQUEST</button>
                    </form>
                </div>
            </section>
        `;

        // Listen for type change to swap document fields
        document.getElementById('request-title').addEventListener('change', (e) => {
            const docsSection = document.getElementById('documents-section');
            docsSection.innerHTML = this._buildDocsFields(e.target.value);
        });

        this.triggerObserver();
    },

    /** Build the file-input fields for a given request type */
    _buildDocsFields(title) {
        const docs = REQUIRED_DOCS[title] || [];
        if (docs.length === 0) {
            return `
                <div class="docs-notice">
                    <p style="opacity:0.7; font-style:italic;">✓ No supporting documents required for this request type.</p>
                </div>
            `;
        }
        let html = `<h4 class="docs-section-title">Required Documents</h4>`;
        docs.forEach((doc, idx) => {
            html += `
                <div class="form-group doc-upload-group">
                    <label for="doc-file-${idx}">${doc.label}</label>
                    <input type="file" id="doc-file-${idx}" name="doc-file-${idx}" accept="${doc.accept}" required
                        class="doc-file-input"
                        data-label="${doc.label}"
                        data-doctype="${doc.docType}">
                    <small id="doc-file-${idx}-error" class="form-error" style="display:none;"></small>
                    <div class="file-selected" id="file-selected-${idx}" style="display:none;">
                        <span class="file-selected-name"></span>
                        <span class="file-selected-size"></span>
                    </div>
                </div>
            `;
        });
        return html;
    },

    // ── My Requests List ─────────────────────────────────────────

    renderMyRequests(requests, filters = { query: '', sortBy: 'date_desc' }) {
        const requestCards = requests.length > 0 ? requests.map(r => {
            const statusClass = r.status === 'approved' ? 'status-approved' :
                                r.status === 'rejected' ? 'status-rejected' :
                                r.status === 'under review' ? 'status-under-review' :
                                'status-pending';
            return `
                <div class="request-card reveal">
                    <div class="request-card-header">
                        <div class="request-card-id">#${r.id}</div>
                        <span class="status-badge ${statusClass}">${r.status}</span>
                    </div>
                    <div class="request-card-body">
                        <h3>${r.title}</h3>
                        <p class="request-description">${r.description || 'No description provided.'}</p>
                        <div class="request-meta">
                            <span class="request-date">📅 ${new Date(r.createdAt).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="request-card-actions">
                        <button class="btn btn-small" data-action="view-request" data-id="${r.id}">VIEW DETAILS</button>
                        ${r.status === 'pending' ? `
                            <button class="btn btn-small btn-primary" data-action="edit-request" data-id="${r.id}">EDIT</button>
                            <button class="btn btn-small btn-danger" data-action="delete-request" data-id="${r.id}">DELETE</button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('') : `
            <div class="empty-state reveal">
                <h3>No Requests Yet</h3>
                <p>You haven't submitted any service requests. Start by filing a new one.</p>
                <a href="#request-service" class="btn btn-primary">FILE A REQUEST</a>
            </div>
        `;

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">My Requests</h2>
                <div class="form-card reveal" style="margin-bottom:1.25rem;">
                    <div style="display:grid;grid-template-columns:2fr 1fr auto;gap:0.75rem;align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label for="request-search">Search (ID / keyword)</label>
                            <input id="request-search" type="text" placeholder="Ex: 12, birth, rejected..." value="${filters.query || ''}">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label for="request-sort">Sort by</label>
                            <select id="request-sort">
                                <option value="date_desc" ${filters.sortBy === 'date_desc' ? 'selected' : ''}>Newest first</option>
                                <option value="date_asc" ${filters.sortBy === 'date_asc' ? 'selected' : ''}>Oldest first</option>
                                <option value="status_asc" ${filters.sortBy === 'status_asc' ? 'selected' : ''}>Status A-Z</option>
                                <option value="status_desc" ${filters.sortBy === 'status_desc' ? 'selected' : ''}>Status Z-A</option>
                            </select>
                        </div>
                        <button class="btn btn-small" data-action="reset-request-filters">RESET</button>
                    </div>
                </div>
                <div class="requests-list">
                    ${requestCards}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── Request Detail with Documents ────────────────────────────

    renderRequestDetail(request, documents, auditLogs = []) {
        const statusClass = request.status === 'approved' ? 'status-approved' :
                            request.status === 'rejected' ? 'status-rejected' :
                            request.status === 'under review' ? 'status-under-review' :
                            'status-pending';

        const docRows = documents && documents.length > 0 ? documents.map(d => `
            <tr>
                <td>
                    <strong>${d.filePath}</strong><br>
                    <a href="../../uploads/${d.filePath}" target="_blank" rel="noopener noreferrer">Open</a>
                </td>
                <td><span class="category-badge">${d.type}</span></td>
                <td>-</td>
                <td>${new Date(d.uploadedAt).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-small btn-danger" data-action="delete-document" data-doc-id="${d.id}" data-request-id="${request.id}" style="margin-left:5px;">DELETE</button>
                </td>
            </tr>
        `).join('') : `<tr><td colspan="5" style="text-align:center; padding: 2rem;">No documents attached yet.</td></tr>`;

        const timeline = auditLogs.length > 0
            ? auditLogs.map((log) => `
                <li style="margin-bottom:0.5rem;">
                    <strong>${new Date(log.createdAt).toLocaleString()}</strong> -
                    ${log.action}
                    ${log.fromStatus ? `(${log.fromStatus} -> ${log.toStatus || '-'})` : ''}
                    ${log.note ? `<br><span style="opacity:0.85;">${log.note}</span>` : ''}
                </li>
            `).join('')
            : '<li>No activity logged yet.</li>';

        this.app.innerHTML = `
            <section class="page-container">
                <div class="detail-header reveal">
                    <a href="#my-requests" class="btn btn-small" style="margin-bottom: 1.5rem; display:inline-block;">← BACK TO REQUESTS</a>
                    <h2>Request #${request.id}</h2>
                </div>

                <div class="detail-grid reveal">
                    <div class="detail-info-card">
                        <div class="detail-row">
                            <span class="detail-label">SERVICE TYPE</span>
                            <span class="detail-value">${request.title}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">STATUS</span>
                            <span class="status-badge ${statusClass}">${request.status}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">DATE FILED</span>
                            <span class="detail-value">${new Date(request.createdAt).toLocaleDateString()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">DESCRIPTION</span>
                            <span class="detail-value">${request.description || 'No description provided.'}</span>
                        </div>
                        ${request.status === 'rejected' && request.rejectionReason ? `
                        <div class="detail-row">
                            <span class="detail-label">REJECTION REASON</span>
                            <span class="detail-value" style="color:var(--danger);">${request.rejectionReason}</span>
                        </div>
                        ` : ''}
                        ${request.status === 'pending' ? `
                        <div class="detail-actions detail-actions-spaced">
                            <button class="btn btn-small btn-primary" data-action="edit-request" data-id="${request.id}">EDIT REQUEST</button>
                            <button class="btn btn-small btn-danger" data-action="delete-request" data-id="${request.id}">DELETE REQUEST</button>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <div class="documents-section reveal">
                    <div class="documents-header">
                        <h3>Attached Documents</h3>
                        <button class="btn btn-small btn-primary" data-action="upload-document" data-request-id="${request.id}">+ ADD DOCUMENT</button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${docRows}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="documents-section reveal" style="margin-top:1.25rem;">
                    <h3>Request History</h3>
                    <ul style="padding-left:1rem; margin-top:0.75rem;">
                        ${timeline}
                    </ul>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── Edit Request Form ────────────────────────────────────────

    renderEditRequestForm(request, documents = []) {
        const docsRows = documents.length > 0 ? documents.map((d) => `
            <tr>
                <td>
                    <strong>${d.filePath}</strong><br>
                    <a href="../../uploads/${d.filePath}" target="_blank" rel="noopener noreferrer" class="doc-open-link">Open</a>
                </td>
                <td><span class="category-badge">${d.type}</span></td>
                <td>${new Date(d.uploadedAt).toLocaleDateString()}</td>
                <td>
                    <button type="button" class="btn btn-small" data-action="replace-document" data-doc-id="${d.id}" data-request-id="${request.id}" data-doc-type="${d.type}">REPLACE</button>
                    <button type="button" class="btn btn-small btn-danger" data-action="delete-document" data-doc-id="${d.id}" data-request-id="${request.id}" style="margin-left:5px;">DELETE</button>
                </td>
            </tr>
        `).join('') : '<tr><td colspan="4" style="text-align:center; padding:1.5rem;">No documents attached.</td></tr>';

        this.app.innerHTML = `
            <section class="page-container">
                <a href="#my-requests" class="btn btn-small reveal" style="margin-bottom: 1.5rem; display:inline-block;">← BACK TO REQUESTS</a>
                <h2 class="reveal">Edit Request #${request.id}</h2>
                <div class="form-card reveal">
                    <form id="edit-request-form" data-request-id="${request.id}">
                        <div class="form-group">
                            <label for="edit-request-title">Service Type (Title)</label>
                            <input type="text" id="edit-request-title" value="${request.title}" disabled 
                                style="opacity: 0.6; cursor: not-allowed;">
                            <small style="display:block; margin-top:0.5rem; opacity:0.7;">Service type cannot be changed after submission.</small>
                        </div>
                        <div class="form-group">
                            <label for="edit-request-description">Description</label>
                            <textarea id="edit-request-description" name="description" rows="6" required>${request.description || ''}</textarea>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">SAVE CHANGES</button>
                            <a href="#my-requests" class="btn" style="flex:1; text-align:center;">CANCEL</a>
                        </div>
                    </form>
                </div>

                <div class="documents-section reveal" style="margin-top:1rem;">
                    <div class="documents-header">
                        <h3>Manage Documents</h3>
                        <button type="button" class="btn btn-small btn-primary" data-action="upload-document" data-request-id="${request.id}">+ ADD DOCUMENT</button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${docsRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── Document Upload Modal (for adding extra docs from detail view) ───

    showDocumentUploadModal(requestId, existingDocId = null, docType = 'other') {
        const existing = document.getElementById('document-modal');
        if (existing) existing.remove();

        const isReplace = existingDocId !== null;
        const title = isReplace ? 'Replace Document' : 'Upload Document';
        
        let typeHtml = '';
        if (isReplace) {
            typeHtml = `<input type="hidden" name="docType" value="${docType}">`;
        } else {
            typeHtml = `
            <div class="form-group" style="margin-top: 1rem;">
                <label for="doc-type">Document Type</label>
                <select id="doc-type" name="docType" required>
                    <option value="identity">Identity</option>
                    <option value="proof">Proof of Address</option>
                    <option value="photo">Photo</option>
                    <option value="certificate">Certificate</option>
                    <option value="other" selected>Other</option>
                </select>
            </div>`;
        }

        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.id = 'document-modal';
        modal.innerHTML = `
            <div class="modal-card">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="modal-close" data-action="close-modal">&times;</button>
                </div>
                <form id="document-upload-form" data-request-id="${requestId}" data-doc-id="${existingDocId || ''}">
                    <div class="form-group">
                        <label for="doc-file">Select File (PDF / JPG / PNG)</label>
                        <input type="file" id="doc-file" name="file" accept=".pdf,.jpg,.jpeg,.png" required 
                            style="border:none; padding:1.5rem 0;">
                    </div>
                    ${typeHtml}
                    <div class="file-preview" id="file-preview" style="display:none;">
                        <span id="file-preview-name"></span>
                        <span id="file-preview-size"></span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:1rem;">${isReplace ? 'REPLACE FILE' : 'UPLOAD FILE'}</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);

        // File preview
        const fileInput = modal.querySelector('#doc-file');
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (file) {
                document.getElementById('file-preview').style.display = 'flex';
                document.getElementById('file-preview-name').textContent = file.name;
                document.getElementById('file-preview-size').textContent = `(${(file.size / 1024).toFixed(1)} KB)`;
            }
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    },

    closeModal() {
        const modal = document.getElementById('document-modal');
        if (modal) modal.remove();
    },

    // ── Confirmation Dialog ──────────────────────────────────────

    showConfirmDialog(message) {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.id = 'confirm-modal';
            modal.innerHTML = `
                <div class="modal-card confirm-card">
                    <h3>Confirm Action</h3>
                    <p style="margin: 1.5rem 0; font-size: 1.1rem;">${message}</p>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn btn-danger" id="confirm-yes" style="flex:1;">CONFIRM</button>
                        <button class="btn" id="confirm-no" style="flex:1;">CANCEL</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('#confirm-yes').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
            modal.querySelector('#confirm-no').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve(false);
                }
            });
        });
    },

    // ── Profile ──────────────────────────────────────────────────

    renderProfile(user) {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Account Profile</h2>
                <div class="form-card reveal">
                    <form id="profile-form">
                        <div class="form-group">
                            <label for="profile-name">Full Name</label>
                            <input type="text" id="profile-name" name="name" value="${user.name}" required>
                        </div>
                        <div class="form-group">
                            <label for="profile-email">Email Address</label>
                            <input type="email" id="profile-email" name="email" value="${user.email}" required>
                        </div>
                        <div class="form-group" style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">UPDATE DETAILS</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── Complaint Form ───────────────────────────────────────────

    renderComplaintForm() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Submit a Grievance</h2>
                <div class="form-card reveal">
                    <form id="complaint-form">
                        <div class="form-group">
                            <label for="complaint-subject">Subject</label>
                            <input type="text" id="complaint-subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="complaint-body">Details</label>
                            <textarea id="complaint-body" name="body" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">SUBMIT GRIEVANCE</button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ── AI Assistant Panel ───────────────────────────────────────

    renderAiSuggestionLoading() {
        const panel = document.getElementById('ai-suggestion-panel');
        const body  = document.getElementById('ai-panel-body');
        if (!panel || !body) return;
        panel.hidden = false;
        panel.classList.remove('ai-panel-error');
        body.innerHTML = `
            <div class="ai-loading">
                <span class="ai-spinner" aria-hidden="true"></span>
                <span>L'IA analyse votre demande…</span>
            </div>
        `;
    },

    renderAiSuggestion(result) {
        const panel = document.getElementById('ai-suggestion-panel');
        const body  = document.getElementById('ai-panel-body');
        if (!panel || !body) return;
        panel.hidden = false;

        if (!result) {
            panel.classList.add('ai-panel-error');
            body.innerHTML = `<p class="ai-error">L'assistant IA n'a pas répondu. Réessayez plus tard.</p>`;
            return;
        }

        const isFallback = result.status && result.status !== 'ok';
        panel.classList.toggle('ai-panel-error', !!isFallback);

        const issues = (result.issues || []).map(i => `<li>${this._esc(i)}</li>`).join('');
        const improved = this._esc(result.improvedDescription || '');

        const docList = result.documentStatus || [];
        const docsHtml = docList.map((d) => `
            <li class="ai-check-item ${d.ok ? 'is-ok' : 'is-missing'}">
                <span class="ai-check-mark" aria-hidden="true">${d.ok ? '✓' : '✗'}</span>
                <div class="ai-check-text">
                    <strong>${this._esc(d.label)}</strong>
                    <small>${this._esc(d.comment || (d.ok ? 'Document joint.' : 'Document manquant.'))}</small>
                </div>
            </li>
        `).join('');

        const ready = !!result.readyToSubmit;
        const overall = this._esc(result.overallComment || (ready
            ? 'Votre demande est prête à être envoyée.'
            : 'Complétez les éléments signalés avant l\'envoi.'));

        body.innerHTML = `
            ${isFallback ? `<p class="ai-warning">${this._esc(result.message || 'AI service unavailable.')}</p>` : ''}

            <div class="ai-readiness ${ready ? 'is-ready' : 'is-not-ready'}">
                <span class="ai-readiness-icon" aria-hidden="true">${ready ? '✓' : '!'}</span>
                <div>
                    <div class="ai-readiness-title">${ready ? 'Demande prête à être envoyée' : 'Demande incomplète'}</div>
                    <div class="ai-readiness-text">${overall}</div>
                </div>
            </div>

            <div class="ai-section">
                <div class="ai-section-title">Description reformulée</div>
                <p class="ai-improved-text" id="ai-improved-text">${improved || '<em>(aucune)</em>'}</p>
                <div class="ai-actions">
                    <button type="button" class="btn btn-primary btn-small" id="ai-apply-description">UTILISER CE TEXTE</button>
                    <button type="button" class="btn btn-small" id="ai-dismiss">REJETER</button>
                </div>
            </div>

            ${issues ? `
                <div class="ai-section">
                    <div class="ai-section-title">Points à corriger</div>
                    <ul class="ai-issues">${issues}</ul>
                </div>
            ` : ''}

            ${docsHtml ? `
                <div class="ai-section">
                    <div class="ai-section-title">Documents requis</div>
                    <ul class="ai-checklist">${docsHtml}</ul>
                </div>
            ` : ''}
        `;
    },

    closeAiPanel() {
        const panel = document.getElementById('ai-suggestion-panel');
        if (panel) panel.hidden = true;
    },

    _esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
};

export default view;
