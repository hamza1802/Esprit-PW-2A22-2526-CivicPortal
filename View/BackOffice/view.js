/**
 * view.js
 * BackOffice rendering logic — CivicPortal Staff Portal
 */

const view = {
    app: document.getElementById('app'),

    triggerObserver() {
        if (window.initScrollObserver) {
            setTimeout(() => window.initScrollObserver(), 50);
        }
    },

    renderToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast     = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    renderNavBar(role, enrollmentCounts = null) {
        const nav        = document.querySelector('nav');
        const totalBadge = enrollmentCounts && enrollmentCounts.pending > 0
            ? `<span class="nav-dot">${enrollmentCounts.pending}</span>` : '';

        const agentLinks = `
            <li><a href="#worker-dashboard"><i class="bi bi-clipboard2-check"></i> Requests</a></li>
            <li><a href="#appointments"><i class="bi bi-calendar-check"></i> Appointments</a></li>
            <li><a href="#manage-programs"><i class="bi bi-tree"></i> Programs ${totalBadge}</a></li>
        `;

        const adminLinks = `
            <li><a href="#admin-stats"><i class="bi bi-bar-chart-line"></i> Statistics</a></li>
            <li><a href="#worker-dashboard"><i class="bi bi-inbox-fill"></i> Requests</a></li>
            <li><a href="#manage-programs"><i class="bi bi-tree"></i> Programs ${totalBadge}</a></li>
            <li><a href="#appointments"><i class="bi bi-calendar-check"></i> Appointments</a></li>
            <li><a href="#transport-management"><i class="bi bi-bus-front"></i> Transport</a></li>
            <li><a href="#slot-management"><i class="bi bi-clock-history"></i> Slots</a></li>
            <li><a href="#user-management"><i class="bi bi-people"></i> Users</a></li>
            <li><a href="#forum-moderation"><i class="bi bi-chat-square-text"></i> Forum</a></li>
        `;

        nav.innerHTML = `
            <div class="nav-brand">
                <i class="bi bi-building"></i>
                <div>
                    CivicPortal
                    <span class="nav-brand-sub">STAFF PORTAL</span>
                </div>
            </div>
            <ul class="nav-links">
                <li><a href="#home"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                ${role === 'agent' ? agentLinks : ''}
                ${role === 'admin' ? adminLinks : ''}

            </ul>
            <div class="user-controls">
                <div class="user-role-badge">${role}</div>
                <a href="../FrontOffice/index.php" class="bo-frontoffice-btn">
                    <i class="bi bi-arrow-left-circle"></i> Front Office
                </a>
                <button class="bo-logout-btn"
                    onclick="fetch('../../Verification.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../FrontOffice/login.php')">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </div>
        `;

        setTimeout(() => {
            const hash = window.location.hash || '#home';
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.toggle('active', hash.startsWith(link.getAttribute('href')));
            });
        }, 50);
    },

    renderHome(user) {
        const agentCards = `
            <div class="editorial-card editorial-highlight reveal">
                <h3>Service Request Queue</h3>
                <p>Process pending administrative filings. Validate or reject documents submitted by citizens.</p>
                <a href="#worker-dashboard" class="btn btn-primary mt-auto" style="align-self:flex-start;">Open Dashboard</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Appointments</h3>
                <p>Manage citizen appointment requests. Confirm, reschedule, or complete scheduled service visits.</p>
                <a href="#appointments" class="btn mt-auto" style="align-self:flex-start;">View Appointments</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Parks &amp; Recreation</h3>
                <p>View community programs and manage citizen enrollment requests.</p>
                <a href="#manage-programs" class="btn mt-auto" style="align-self:flex-start;">View Programs</a>
            </div>
        `;

        const adminCards = `
            <div class="editorial-card editorial-highlight reveal">
                <h3>Platform Statistics</h3>
                <p>View real-time aggregated data across all civic modules to monitor system health.</p>
                <a href="#admin-stats" class="btn btn-primary mt-auto" style="align-self:flex-start;">View Stats</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Service Requests</h3>
                <p>Review and process all citizen service filings. Validate or reject submitted requests.</p>
                <a href="#worker-dashboard" class="btn mt-auto" style="align-self:flex-start;">View Requests</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Parks &amp; Recreation</h3>
                <p>Manage community programs, workshops, and facilities.</p>
                <a href="#manage-programs" class="btn mt-auto" style="align-self:flex-start;">Manage Programs</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Appointments</h3>
                <p>Oversee all citizen appointment bookings across all service types and agents.</p>
                <a href="#appointments" class="btn mt-auto" style="align-self:flex-start;">View Queue</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Transport</h3>
                <p>Manage vehicles, routes, and timetables. Monitor seat occupancy and ticket sales.</p>
                <a href="#transport-management" class="btn mt-auto" style="align-self:flex-start;">Manage Transport</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Slot Management</h3>
                <p>Configure agent availability windows. Define which agents handle which services and when.</p>
                <a href="#slot-management" class="btn mt-auto" style="align-self:flex-start;">Manage Slots</a>
            </div>
            <div class="editorial-card reveal">
                <h3>User Management</h3>
                <p>Manage citizen and staff accounts. Change roles, activate, deactivate, or create new accounts.</p>
                <a href="#user-management" class="btn mt-auto" style="align-self:flex-start;">Manage Users</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Forum Moderation</h3>
                <p>Manage citizen forum posts and comments. Pin important discussions, close resolved threads, and remove inappropriate content.</p>
                <a href="#forum-moderation" class="btn btn-primary mt-auto" style="align-self:flex-start;">Moderate Forum</a>
            </div>
        `;

        this.app.innerHTML = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>Staff Portal</h1>
                    <p>Welcome back, ${user.name}. Access your staff module.</p>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">${user.role === 'admin' ? 'Administrative Overview' : 'Operations Console'}</h2>
                <div class="editorial-grid">
                    ${user.role === 'admin' ? adminCards : agentCards}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderProfile(user) {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Staff Profile</h2>
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
                        <div class="form-group flex gap-16">
                            <button type="submit" class="btn btn-primary">UPDATE DETAILS</button>
                            <a href="#home" class="btn text-center" style="text-decoration:none;">CANCEL</a>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /**
     * Service request queue. Accepts an optional `filters` object so the
     * search/status/sort controls survive re-renders without losing input focus.
     *
     * @param {Array}  requests
     * @param {Object} [filters] { search, status, sort, order }
     */
    renderWorkerDashboard(requests, filters = {}) {
        const f = {
            search: filters.search ?? '',
            status: filters.status ?? '',
            sort:   filters.sort   ?? 'created_at',
            order:  filters.order  ?? 'DESC'
        };

        const sortOptions = [
            { v: 'created_at', l: 'Date created' },
            { v: 'id',         l: 'Reference #' },
            { v: 'title',      l: 'Title' },
            { v: 'status',     l: 'Status' },
            { v: 'category',   l: 'Category' },
            { v: 'user_name',  l: 'Citizen' }
        ].map(o => `<option value="${o.v}" ${o.v === f.sort ? 'selected' : ''}>${o.l}</option>`).join('');

        const statusOptions = ['', 'pending', 'in_progress', 'validated', 'rejected', 'resolved']
            .map(s => `<option value="${s}" ${s === f.status ? 'selected' : ''}>${s ? s.replace('_', ' ').toUpperCase() : 'All statuses'}</option>`)
            .join('');

        const tableRows = (requests || []).map(r => {
            const docCount = parseInt(r.documents_count ?? 0, 10);
            const hasMain  = parseInt(r.has_attachment  ?? 0, 10) === 1;
            const fileBadge = (hasMain || docCount > 0)
                ? `<span class="text-small" style="display:inline-flex;align-items:center;gap:4px;color:var(--accent-blue);">
                       <i class="bi bi-paperclip"></i>${(hasMain ? 1 : 0) + docCount}
                   </span>`
                : '<span class="text-small opacity-7">—</span>';
            return `
                <tr>
                    <td><strong>#${r.id}</strong></td>
                    <td>
                        ${r.title || '—'}
                        ${r.user_name ? `<div class="text-small opacity-7">by ${r.user_name}</div>` : ''}
                    </td>
                    <td>${r.category || '—'}</td>
                    <td>${r.created_at ? new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                    <td>${fileBadge}</td>
                    <td><span class="status-badge status-${r.status}">${r.status}</span></td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-small" data-action="view-request" data-id="${r.id}" style="margin-right:4px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.4);color:var(--primary-navy);">
                            <i class="bi bi-eye"></i> VIEW
                        </button>
                        <button class="btn btn-small btn-success" data-action="validate" data-id="${r.id}" style="margin-right:4px;"><i class="bi bi-check-lg"></i> VALIDATE</button>
                        <button class="btn btn-small btn-danger"  data-action="reject"   data-id="${r.id}"><i class="bi bi-x-lg"></i> REJECT</button>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Service Request Queue</h2>

                <div class="reveal" id="req-toolbar"
                     style="display:flex;flex-wrap:wrap;gap:0.6rem;align-items:flex-end;margin-bottom:1.2rem;">
                    <div style="flex:1 1 280px;min-width:240px;">
                        <label for="req-search" class="text-small text-bold">Search</label>
                        <input id="req-search" type="search" placeholder="Title, description, citizen, category..."
                               value="${this._escapeHtml(f.search)}"
                               style="width:100%;padding:0.55rem 0.7rem;border:var(--border-main);border-radius:8px;">
                    </div>
                    <div style="flex:0 0 180px;">
                        <label for="req-filter-status" class="text-small text-bold">Status</label>
                        <select id="req-filter-status" style="width:100%;padding:0.55rem 0.5rem;border:var(--border-main);border-radius:8px;">
                            ${statusOptions}
                        </select>
                    </div>
                    <div style="flex:0 0 180px;">
                        <label for="req-sort" class="text-small text-bold">Sort by</label>
                        <select id="req-sort" style="width:100%;padding:0.55rem 0.5rem;border:var(--border-main);border-radius:8px;">
                            ${sortOptions}
                        </select>
                    </div>
                    <div style="flex:0 0 110px;">
                        <label for="req-order" class="text-small text-bold">Order</label>
                        <select id="req-order" style="width:100%;padding:0.55rem 0.5rem;border:var(--border-main);border-radius:8px;">
                            <option value="DESC" ${f.order === 'DESC' ? 'selected' : ''}>Desc</option>
                            <option value="ASC"  ${f.order === 'ASC'  ? 'selected' : ''}>Asc</option>
                        </select>
                    </div>
                    <button id="req-reset" class="btn btn-small" type="button" style="height:42px;">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Service Type</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Files</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="7" class="text-center" style="padding:2rem;">No requests found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /**
     * Detail view of a single service request, with documents list, history
     * timeline and an "Analyze with AI" panel. Includes inline PDF / image preview.
     */
    renderRequestDetail(request) {
        const documents = request.documents || [];
        const history   = request.history   || [];
        const hasMain   = parseInt(request.has_attachment ?? 0, 10) === 1;

        const docList = documents.map(d => {
            const fileName = d.filePath || `doc-${d.id}`;
            const url    = `../../get_document.php?id=${d.id}`;
            const ext    = fileName.split('.').pop().toLowerCase();
            const isPdf  = ext === 'pdf';
            const isImg  = ['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext);
            const preview = isImg
                ? `<img src="${url}" alt="${this._escapeHtml(fileName)}" style="max-width:100%;max-height:160px;border:var(--border-main);border-radius:8px;display:block;margin-top:0.4rem;">`
                : isPdf
                    ? `<embed src="${url}" type="application/pdf" style="width:100%;height:240px;border:var(--border-main);border-radius:8px;display:block;margin-top:0.4rem;">`
                    : '';
            return `
                <li style="padding:0.6rem;border:var(--border-main);border-radius:8px;background:#fff;margin-bottom:0.5rem;">
                    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;align-items:center;">
                        <div>
                            <i class="bi bi-${isPdf ? 'file-earmark-pdf' : (isImg ? 'image' : 'file-earmark')}"></i>
                            <strong style="font-size:0.95rem;">${this._escapeHtml(fileName)}</strong>
                            <span class="text-small opacity-7">— ${this._escapeHtml(d.type || 'other')}</span>
                        </div>
                        <a href="${url}" target="_blank" class="btn btn-small">
                            <i class="bi bi-box-arrow-up-right"></i> Open
                        </a>
                    </div>
                    ${preview}
                </li>
            `;
        }).join('');

        const mainImg = hasMain
            ? `<div class="form-group">
                  <label class="text-small text-bold">Primary attached image</label>
                  <img src="../../get_image.php?type=service&id=${request.id}"
                       alt="Primary attachment"
                       style="max-width:100%;max-height:280px;border:var(--border-main);border-radius:8px;display:block;">
               </div>`
            : '';

        this.app.innerHTML = `
            <section class="page-container">
                <div class="flex-between mb-32 flex-wrap gap-16">
                    <h2 class="reveal mb-0">Request #${request.id} &mdash; ${this._escapeHtml(request.title || '')}</h2>
                    <a href="#worker-dashboard" class="btn reveal" style="text-decoration:none;">
                        <i class="bi bi-arrow-left"></i> Back to queue
                    </a>
                </div>

                <div class="editorial-grid" style="grid-template-columns:1fr 1fr;gap:1.5rem;">
                    <div class="form-card reveal">
                        <h3 style="font-size:1.1rem;margin-top:0;"><i class="bi bi-info-circle"></i> Request details</h3>
                        <table class="data-table" style="margin-bottom:1rem;">
                            <tbody>
                                <tr><th style="width:140px;">Status</th><td><span class="status-badge status-${request.status}">${request.status}</span></td></tr>
                                <tr><th>Citizen</th><td>${this._escapeHtml(request.user_name || '—')}</td></tr>
                                <tr><th>Category</th><td>${this._escapeHtml(request.category || '—')}</td></tr>
                                <tr><th>Created</th><td>${request.created_at ? new Date(request.created_at).toLocaleString() : '—'}</td></tr>
                                <tr><th>Assigned to</th><td>${this._escapeHtml(request.agent_name || '— unassigned')}</td></tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <label class="text-small text-bold">Description</label>
                            <div style="padding:0.7rem;border:var(--border-main);border-radius:8px;background:#fff;white-space:pre-wrap;">${this._escapeHtml(request.description || '—')}</div>
                        </div>

                        ${mainImg}

                        <div class="form-group">
                            <label class="text-small text-bold">
                                Additional documents (${documents.length})
                            </label>
                            ${documents.length === 0
                                ? '<div class="text-small opacity-7">No additional documents attached.</div>'
                                : `<ul style="list-style:none;padding:0;margin:0;">${docList}</ul>`}
                        </div>

                        <div class="flex gap-8 flex-wrap" style="margin-top:1rem;">
                            <button class="btn btn-small btn-success" data-action="validate" data-id="${request.id}">
                                <i class="bi bi-check-lg"></i> VALIDATE
                            </button>
                            <button class="btn btn-small btn-danger" data-action="reject" data-id="${request.id}">
                                <i class="bi bi-x-lg"></i> REJECT
                            </button>
                        </div>
                    </div>

                    <div class="form-card reveal">
                        <div class="flex-between flex-wrap gap-16">
                            <h3 style="font-size:1.1rem;margin-top:0;"><i class="bi bi-stars"></i> AI assistant</h3>
                            <button id="btn-ai-analyze-req" class="btn btn-small btn-primary" data-id="${request.id}">
                                <i class="bi bi-stars"></i> Analyze with AI
                            </button>
                        </div>
                        <p class="text-small opacity-7" style="margin-top:0.4rem;">
                            Asks Gemini to assess completeness, flag missing documents and suggest a next step.
                        </p>
                        <div id="ai-analyze-result" data-request-id="${request.id}"
                             style="margin-top:1rem;border:1px dashed rgba(99,102,241,0.4);border-radius:12px;padding:1rem;background:rgba(99,102,241,0.04);min-height:80px;color:var(--text-main);">
                            <span class="text-small opacity-7">Click "Analyze with AI" to inspect this request.</span>
                        </div>

                        <hr style="margin:1.2rem 0;border:none;border-top:1px dashed rgba(0,0,0,0.1);">

                        <h3 style="font-size:1.1rem;margin-top:0;">
                            <i class="bi bi-clock-history"></i> Request history
                        </h3>
                        ${this._renderHistoryTimeline(history)}
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /** Compact history-timeline renderer (used by BO request detail + admin stats). */
    _renderHistoryTimeline(history = []) {
        if (!history || history.length === 0) {
            return '<div class="text-small opacity-7">No activity recorded yet.</div>';
        }
        const iconFor = (action) => ({
            'created':            'bi-plus-circle',
            'status_changed':     'bi-arrow-repeat',
            'description_edited': 'bi-pencil-square',
            'document_added':     'bi-file-earmark-plus',
            'document_deleted':   'bi-file-earmark-x',
            'ai_analyzed':        'bi-stars',
        }[action] || 'bi-dot');

        const items = history.map(h => {
            const flow = (h.from_status || h.to_status)
                ? `<span class="text-small opacity-7"> &nbsp; ${this._escapeHtml(h.from_status || '∅')} → ${this._escapeHtml(h.to_status || '∅')}</span>`
                : '';
            return `
                <li style="display:flex;gap:0.7rem;padding:0.55rem 0;border-bottom:1px dashed rgba(0,0,0,0.08);list-style:none;">
                    <div style="flex:0 0 24px;text-align:center;color:var(--accent-blue);font-size:1.05rem;">
                        <i class="bi ${iconFor(h.action)}"></i>
                    </div>
                    <div style="flex:1 1 auto;min-width:0;">
                        <div>
                            <strong>${this._escapeHtml((h.action || '').replace(/_/g, ' '))}</strong>
                            ${flow}
                            ${h.request_title ? ` &nbsp; <span class="text-small opacity-7">on “${this._escapeHtml(h.request_title)}”</span>` : ''}
                        </div>
                        ${h.note ? `<div class="text-small" style="margin-top:0.15rem;">${this._escapeHtml(h.note)}</div>` : ''}
                        <div class="text-small opacity-7" style="margin-top:0.15rem;">
                            ${h.created_at ? new Date(h.created_at).toLocaleString() : ''}
                            ${h.actor_name ? ` &nbsp; · &nbsp; by ${this._escapeHtml(h.actor_name)}` : ''}
                            ${h.actor_role ? ` <span class="text-small opacity-7">(${this._escapeHtml(h.actor_role)})</span>` : ''}
                        </div>
                    </div>
                </li>
            `;
        }).join('');
        return `<ul style="padding:0;margin:0;">${items}</ul>`;
    },

    /** Render the result returned by AIService::analyzeRequest. */
    renderAIAnalyzeResult(result, requestId = null) {
        const box = document.getElementById('ai-analyze-result');
        if (!box) return;
        const rid = requestId != null && requestId !== ''
            ? parseInt(String(requestId), 10)
            : parseInt(box.getAttribute('data-request-id') || '0', 10);

        if (!result) {
            box.innerHTML = '<span style="color:var(--color-danger,#ef4444);">AI is currently unavailable.</span>';
            return;
        }

        const rec       = (result.recommendation || 'request_more_info').toLowerCase();
        const recLabel  = rec === 'approve' ? 'APPROVE' : rec === 'reject' ? 'REJECT' : 'REQUEST MORE INFO';
        const recColor  = {
            approve:           '#10b981',
            request_more_info: '#f59e0b',
            reject:            '#ef4444'
        }[rec] || '#6b7280';

        const scoreRaw = result.validityScore;
        const score    = Math.max(0, Math.min(100, parseInt(String(scoreRaw ?? 0), 10) || 0));
        let scoreBar   = '#6366f1';
        if (score >= 74) scoreBar = '#10b981';
        else if (score >= 60) scoreBar = '#f59e0b';
        else scoreBar = '#ef4444';

        const issues  = (result.issues || []).map(i => `<li>${this._escapeHtml(i)}</li>`).join('');
        const missing = (result.missingDocuments || []).map(m => `<li>${this._escapeHtml(m)}</li>`).join('');
        const docs    = (result.documentReview || []).map(d => `
            <li style="display:flex;gap:8px;align-items:flex-start;">
                <span>${d.acceptable ? '✅' : '⚠️'}</span>
                <span><strong>${this._escapeHtml(d.label || d.fileName || 'Document')}</strong>
                    &mdash; <span class="text-small">${this._escapeHtml(d.comment || '')}</span></span>
            </li>
        `).join('');

        const suggested = (result.suggestedComment || result.suggestedReply || '').trim();

        const status = result.status === 'ok' ? '' :
            `<div class="text-small" style="color:#b45309;margin-bottom:0.5rem;">
                <i class="bi bi-info-circle"></i> ${this._escapeHtml(result.message || 'AI fallback used.')}
             </div>`;

        /** One-click status: staff still confirm; primary button matches AI path. */
        const primaryValidate = rec === 'approve'
            ? `<button type="button" class="btn btn-small btn-success ai-suggested-action"
                    data-action="validate" data-id="${rid}" title="Apply AI suggestion">
                    <i class="bi bi-check-lg"></i> Validate (AI: approve)
                </button>`
            : `<button type="button" class="btn btn-small ai-suggested-action"
                    data-action="validate" data-id="${rid}" style="opacity:0.92;border:1px dashed var(--success);color:var(--success);background:transparent;"
                    title="Override AI — validate anyway">
                    <i class="bi bi-check-lg"></i> Validate anyway
                </button>`;

        const primaryReject = rec === 'reject'
            ? `<button type="button" class="btn btn-small btn-danger ai-suggested-action"
                    data-action="reject" data-id="${rid}" title="Apply AI suggestion">
                    <i class="bi bi-x-lg"></i> Reject (AI: reject)
                </button>`
            : `<button type="button" class="btn btn-small ai-suggested-action"
                    data-action="reject" data-id="${rid}" style="opacity:0.92;border:1px dashed var(--danger);color:var(--danger);background:transparent;"
                    title="Override AI — reject">
                    <i class="bi bi-x-lg"></i> Reject anyway
                </button>`;

        const infoNote = rec === 'request_more_info'
            ? `<p class="text-small" style="margin:0 0 0.6rem 0;color:#b45309;">
                <i class="bi bi-info-circle"></i> AI suggests contacting the citizen for clarification before accepting or rejecting.</p>`
            : '';

        const copyBtn = suggested !== ''
            ? `<button type="button" id="btn-copy-ai-suggestion" class="btn btn-small" style="margin-top:0.4rem;"
                    title="Copy suggested reply to clipboard"><i class="bi bi-clipboard"></i> Copy suggestion</button>`
            : '';

        box.innerHTML = `
            ${status}
            <div class="flex-between flex-wrap gap-16 mb-16" style="align-items:flex-start;">
                <h4 class="mb-0" style="font-size:1.05rem;"><i class="bi bi-stars"></i> AI analysis</h4>
                <span class="status-pill" style="padding:3px 10px;border-radius:99px;font-size:0.75rem;font-weight:800;
                             background:${recColor}22;color:${recColor};">
                    ${recLabel.replace('_', ' ')}
                </span>
            </div>

            <div class="flex flex-wrap gap-16 mb-16" style="align-items:center;">
                <div style="flex:0 0 auto;text-align:center;">
                    <div style="font-size:2rem;font-weight:900;line-height:1;color:${scoreBar};">${score}<span style="font-size:1rem;opacity:0.6;font-weight:700;">/100</span></div>
                    <div class="text-small opacity-7" style="max-width:9rem;margin-top:0.2rem;">Readiness score (forgiving&nbsp;curve)</div>
                </div>
                <div style="flex:1 1 200px;min-width:160px;">
                    <div style="height:10px;background:rgba(0,0,0,0.06);border-radius:99px;overflow:hidden;border:var(--border-main);">
                        <div style="height:100%;width:${score}%;background:${scoreBar};border-radius:99px;transition:width 0.3s ease;"></div>
                    </div>
                    <p class="text-small opacity-8" style="margin:0.4rem 0 0 0;">Higher is typical for routine filings; mid scores mean “probably fine after a quick look.”</p>
                </div>
            </div>

            ${result.summary ? `<p style="margin:0 0 0.8rem 0;">${this._escapeHtml(result.summary)}</p>` : ''}
            ${infoNote}
            ${issues  ? `<div class="form-group"><label class="text-small text-bold">Issues</label><ul style="margin:0;padding-left:1.2rem;">${issues}</ul></div>` : ''}
            ${missing ? `<div class="form-group"><label class="text-small text-bold">Missing documents</label><ul style="margin:0;padding-left:1.2rem;">${missing}</ul></div>` : ''}
            ${docs    ? `<div class="form-group"><label class="text-small text-bold">Document review</label><ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:6px;">${docs}</ul></div>` : ''}
            ${suggested ? `<div class="form-group"><label class="text-small text-bold">Suggested reply to citizen</label><div id="ai-suggestion-text" style="padding:0.7rem;border:var(--border-main);border-radius:8px;background:#fff;white-space:pre-wrap;">${this._escapeHtml(suggested)}</div>${copyBtn}</div>` : ''}

            <div class="form-group" style="margin-top:0.5rem;margin-bottom:0;">
                <label class="text-small text-bold">Decision (you are in charge)</label>
                <div class="flex gap-8 flex-wrap" style="margin-top:0.4rem;">
                    ${primaryValidate}
                    ${primaryReject}
                </div>
                <p class="text-small opacity-7" style="margin-top:0.45rem;margin-bottom:0;">
                    Filled buttons match the AI suggestion; outline buttons let you override.</p>
            </div>
        `;

        const copyEl = document.getElementById('btn-copy-ai-suggestion');
        if (copyEl && suggested) {
            copyEl.addEventListener('click', () => {
                navigator.clipboard.writeText(suggested).then(() => {
                    this.renderToast('Copied to clipboard.');
                }).catch(() => this.renderToast('Could not copy.', 'error'));
            });
        }
    },

    /** Tiny helper, keeps strings injected into innerHTML safe. */
    _escapeHtml(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    renderAdminStats(stats) {
        const breakdown = stats.statusBreakdown || {};
        const breakdownChips = Object.keys(breakdown).length === 0
            ? '<span class="text-small opacity-7">No service requests yet.</span>'
            : Object.entries(breakdown).map(([status, count]) => `
                <span class="status-badge status-${this._escapeHtml(status)}" style="margin-right:6px;margin-bottom:6px;">
                    ${this._escapeHtml(status)}: ${count}
                </span>
            `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">System Statistics</h2>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <i class="bi bi-people-fill mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Total Users</h3>
                        <p class="stats-number">${stats.usersCount ?? 0}</p>
                    </div>
                    <div class="editorial-card editorial-highlight reveal">
                        <i class="bi bi-file-earmark-arrow-up mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Service Requests</h3>
                        <p class="stats-number">${stats.requestsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-files mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Documents</h3>
                        <p class="stats-number">${stats.documentsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-calendar2-event mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Active Programs</h3>
                        <p class="stats-number">${stats.programsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-person-check mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Enrollments</h3>
                        <p class="stats-number">${stats.enrollmentsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-calendar-check mb-8" style="font-size:2rem;display:block;"></i>
                        <h3>Appointments</h3>
                        <p class="stats-number">${stats.appointmentsCount ?? 0}</p>
                    </div>
                </div>

                <div class="form-card reveal" style="margin-top:1.5rem;">
                    <h3 style="font-size:1.1rem;margin-top:0;">
                        <i class="bi bi-bar-chart-steps"></i> Request status breakdown
                    </h3>
                    <div style="margin-top:0.5rem;">${breakdownChips}</div>
                </div>

                <div class="form-card reveal" style="margin-top:1.2rem;">
                    <h3 style="font-size:1.1rem;margin-top:0;">
                        <i class="bi bi-clock-history"></i> Recent request activity
                    </h3>
                    <p class="text-small opacity-7" style="margin-top:0;">
                        Last ${(stats.recentHistory || []).length} events across all service requests.
                    </p>
                    ${this._renderHistoryTimeline(stats.recentHistory || [])}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       USER MANAGEMENT (admin only)
       ========================================================================= */
    renderUserManagement(users, f = { search: '', sort: 'u.id DESC' }) {
        const rows = users.map(u => {
            // Determine avatar
            let avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(u.username)}&background=1D2A44&color=fff`;
            
            // Fix for masked avatar paths ($2y$10$ + base64)
            if (u.profile?.avatar && u.profile.avatar.startsWith('$2y$10$')) {
                try {
                    avatar = atob(u.profile.avatar.substring(7));
                } catch(e) {
                    console.warn('[User Management] Failed to decode avatar for user:', u.id);
                }
            } else if (u.has_pic) {
                avatar = `../../get_image.php?type=profile&id=${u.id}&t=${u.id}`;
            }

            const role = (u.role || 'citizen').toLowerCase();
            const roleStyles = {
                admin: 'background: var(--primary-navy); color: #fff;',
                agent: 'background: #e9ecef; color: var(--primary-navy);',
                citizen: 'background: #f8f9fa; color: #6c757d;'
            };
            const roleStyle = roleStyles[role] || roleStyles.citizen;

            return `
                <tr data-id="${u.id}" class="admin-row">
                    <td style="font-weight: 800;"><span class="id-tag">#${u.id}</span></td>
                    <td>
                        <div class="name-cell" style="display: flex; align-items: center; gap: 1rem;">
                            <img src="${avatar}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-navy);">
                            <span style="font-weight: 800; font-size: 1.1rem; color: var(--primary-navy);">${u.username}</span>
                        </div>
                    </td>
                    <td style="font-weight: 600; opacity: 0.8;"><div class="email-cell">${u.email}</div></td>
                    <td><span class="role-badge" style="display: inline-block; padding: 0.4rem 1rem; border-radius: 6px; font-weight: 900; font-size: 0.75rem; text-transform: uppercase; ${roleStyle}">${role}</span></td>
                    <td style="font-weight: 600; opacity: 0.8;"><span class="date-cell">${(() => {
                        if (!u.created_at) return '-';
                        const d = new Date(u.created_at.replace(' ', 'T')); // Handle space between date and time
                        return isNaN(d) ? u.created_at : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    })()}</span></td>
                    <td style="text-align: right;">
                        <div class="action-flex" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button class="btn btn-small" data-action="edit-user" data-id="${u.id}" style="border: 1px solid #ddd; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 800; background: #fff;">EDIT</button>
                            <button class="btn btn-small btn-del" data-action="delete-user" data-id="${u.id}" data-name="${u.username}" style="border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 800; background: var(--primary-red); color: #fff;">DELETE</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container" style="padding-top: 1rem;">
                <div class="hero-section" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; border-bottom: 2px solid var(--primary-navy); padding-bottom: 2rem;">
                    <div>
                        <h1 style="font-size: 3rem; color: var(--primary-navy); font-weight: 900; letter-spacing: -1.5px;">User Management</h1>
                        <p style="font-size: 1.1rem; opacity: 0.8; margin-top: 10px; font-weight: 600;">Manage portal accounts and staff credentials.</p>
                    </div>
                    <button id="show-register-form" class="btn btn-primary reveal" data-action="toggle-create-user" style="padding: 1.2rem 2.5rem; font-weight: 900; border-radius: 4px; box-shadow: 8px 8px 0px var(--primary-navy);">+ NEW REGISTRATION</button>
                </div>

                <!-- Search and Sort Controls -->
                <div class="controls-bar reveal" style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
                    <div style="flex-grow: 1; position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: var(--primary-navy); opacity: 0.5;"></i>
                        <input type="text" id="user-search" placeholder="Search by name or email..." value="${this._escapeHtml(f.search)}" style="width: 100%; padding: 1.2rem 1.2rem 1.2rem 3.5rem; border: 2px solid var(--primary-navy); font-weight: 700; border-radius: 8px;">
                    </div>
                    <div style="width: 250px;">
                        <select id="user-sort" style="width: 100%; padding: 1.2rem; border: 2px solid var(--primary-navy); font-weight: 900; text-transform: uppercase; border-radius: 8px; appearance: none; background: white url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231D2A44%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22/%3E%3C/svg%3E') no-repeat right 1rem center; background-size: 0.65rem auto;">
                            <option value="u.id DESC" ${f.sort === 'u.id DESC' ? 'selected' : ''}>Latest First</option>
                            <option value="u.username ASC" ${f.sort === 'u.username ASC' ? 'selected' : ''}>Full Name (A-Z)</option>
                            <option value="u.username DESC" ${f.sort === 'u.username DESC' ? 'selected' : ''}>Full Name (Z-A)</option>
                            <option value="u.email ASC" ${f.sort === 'u.email ASC' ? 'selected' : ''}>Email Address</option>
                        </select>
                    </div>
                </div>

                <div class="reveal">
                    <div class="table-responsive" style="margin-bottom: 4rem; background: white; border: 2px solid var(--primary-navy); box-shadow: 12px 12px 0px rgba(29, 42, 68, 0.1);">
                        <table class="data-table" id="users-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Full Name</th>
                                    <th>Email Address</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="6" class="text-center" style="padding:2rem;">No users found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Registration/Edit Section -->
                <div id="user-registration-section" class="form-card reveal" style="display: none; margin-top: 4rem; border: 4px solid var(--primary-navy); background: white; padding: 3rem; box-shadow: 20px 20px 0px rgba(29, 42, 68, 0.05);">
                    <h2 id="form-title" style="margin-bottom: 2.5rem; color: var(--primary-navy); font-weight: 900; text-transform: uppercase; border-bottom: 4px solid var(--primary-navy); padding-bottom: 1rem;">
                        Portal Registration
                    </h2>

                    <form id="create-user-form" onsubmit="event.preventDefault();" novalidate>
                        <input type="hidden" name="id" id="edit-user-id" value="">
                        
                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Full Display Name</label>
                            <input id="name" name="name" type="text" placeholder="EX: JOHN SMITH" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border: 2px solid var(--primary-navy);" required>
                            <span class="inline-error" id="error-name" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"></span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 2rem;">
                            <div class="form-group">
                                <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">E-mail Address</label>
                                <input id="email" name="email" type="email" placeholder="email@cityhall.gov" style="width: 100%; border: 2px solid var(--primary-navy);" required>
                                <span class="inline-error" id="error-email" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"></span>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Assigned Access Role</label>
                                <select id="role" name="role" style="width: 100%; border: 2px solid var(--primary-navy); height: 60px;" required>
                                    <option value="citizen">Citizen (Public)</option>
                                    <option value="agent">Agent (Staff)</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                <span class="inline-error" id="error-role" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"></span>
                            </div>
                        </div>

                        <div id="password-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; margin-bottom: 3rem;">
                            <div class="form-group">
                                <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Password <small id="password-note" style="display:none;">(New only)</small></label>
                                <input id="password" name="password" type="password" placeholder="••••••••" style="width: 100%; border: 2px solid var(--primary-navy);" autocomplete="new-password">
                                <span class="inline-error" id="error-password" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"></span>
                            </div>

                            <div class="form-group">
                                <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Confirm Credentials</label>
                                <input id="confirm_password" name="confirm_password" type="password" placeholder="••••••••" style="width: 100%; border: 2px solid var(--primary-navy);" autocomplete="new-password">
                                <span class="inline-error" id="error-confirm_password" style="color: var(--primary-red); font-size: 0.8rem; margin-top: 0.5rem; display: block; font-weight: 700; text-transform: uppercase;"></span>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 3rem;">
                            <label style="font-weight: 900; text-transform: uppercase; display: block; margin-bottom: 0.8rem; font-size: 0.8rem;">Profile Picture (Optional)</label>
                            <input type="file" name="avatar" id="avatar-input" style="width: 100%; padding: 1rem; border: 2px dashed var(--primary-navy); border-radius: 8px;">
                            <small style="display: block; margin-top: 0.5rem; opacity: 0.7;">JPEG, PNG, or WebP. Max 2MB.</small>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <button id="submit-btn" class="btn btn-primary" type="submit" style="padding: 1.5rem; font-size: 1.2rem; font-weight: 900; letter-spacing: 1px;">
                                COMPLETE PORTAL REGISTRATION
                            </button>
                            <button type="button" id="cancel-user-btn" class="btn" data-action="toggle-create-user" style="padding: 1rem; font-weight: 800; background: transparent; border-color: transparent !important;">CANCEL AND DISCARD</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Custom Deletion Modal -->
            <div id="delete-confirm-overlay" style="display:none; position:fixed; inset:0; background:rgba(29, 42, 68, 0.9); backdrop-filter:blur(8px); z-index:10000; align-items:center; justify-content:center;">
                <div style="background:#fff; border:4px solid var(--primary-navy); padding:3rem; max-width:420px; width:90%; box-shadow: 20px 20px 0px rgba(0,0,0,0.2);">
                    <h3 style="margin-bottom:1.5rem; text-transform:uppercase; font-size:1.6rem; color:var(--primary-navy); font-weight: 900; letter-spacing: -1px;">Confirm Elimination</h3>
                    <p style="margin-bottom:2.5rem; font-weight:600; font-size: 1.1rem; line-height: 1.4; opacity: 0.8;">Are you sure you want to permanently remove this account? This action is irreversible.</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                        <button id="cancel-delete-btn" class="btn" style="font-weight: 900;">CANCEL</button>
                        <button id="confirm-delete-btn" class="btn btn-danger" style="background: var(--primary-red); color: white; border: none; font-weight: 900;">DELETE</button>
                    </div>
                </div>
            </div>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       APPOINTMENT QUEUE (agents + admin)
       ========================================================================= */
    renderAppointmentQueue(appointments, role) {
        const rows = appointments.map(a => {
            const dateStr = a.preferred_date
                ? new Date(a.preferred_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';
            const time      = a.preferred_time ? a.preferred_time.substring(0, 5) : '—';
            const isPending = a.status === 'pending';
            const isConf    = a.status === 'confirmed';

            return `
                <tr>
                    <td><strong>#${a.id}</strong></td>
                    <td>${a.username || '—'}</td>
                    <td>${a.service_type}</td>
                    <td>${dateStr} ${time}</td>
                    <td><span class="status-badge status-${a.status}">${a.status}</span></td>
                    <td style="max-width:180px;white-space:normal;">${a.notes || '—'}</td>
                    <td>
                        ${isPending ? `
                            <button class="btn btn-small btn-success" data-action="confirm-appointment" data-id="${a.id}" style="margin-right:4px;">CONFIRM</button>
                            <button class="btn btn-small btn-danger"  data-action="cancel-appointment"  data-id="${a.id}">CANCEL</button>
                        ` : ''}
                        ${isConf ? `
                            <button class="btn btn-small btn-success" data-action="complete-appointment" data-id="${a.id}">COMPLETE</button>
                        ` : ''}
                        ${!isPending && !isConf ? '<span class="opacity-5 text-small">—</span>' : ''}
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Appointment Queue</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Citizen</th>
                                    <th>Service</th>
                                    <th>Date &amp; Time</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="7" class="text-center" style="padding:2rem;">No appointments found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       PROGRAMS MANAGER — Premium Card Grid with Notification Dots
       ========================================================================= */
    renderProgramsManager(programs, role) {
        const totalEnrollments = programs.reduce((sum, p) => sum + parseInt(p.enrollment_count || 0), 0);

        const programCards = programs.map(p => {
            const enrolled = parseInt(p.enrollment_count || 0);
            const pending  = parseInt(p.pending_count   || 0);
            const confirmed= parseInt(p.confirmed_count || 0);
            const cap      = parseInt(p.capacity || 1);
            const fillPct  = Math.min(Math.round((enrolled / cap) * 100), 100);
            const isFull   = enrolled >= cap;

            return `
                <div class="program-mgmt-card reveal" data-action="view-program" data-id="${p.id}" style="cursor:pointer;">
                    <div class="program-mgmt-header">
                        <div class="program-mgmt-img" style="background-image:url('../../get_image.php?type=program&id=${p.id}');"></div>
                        ${enrolled > 0 ? `<span class="program-dot${pending > 0 ? ' has-pending' : ''}">${enrolled}</span>` : ''}
                    </div>
                    <div class="program-mgmt-body">
                        <span class="category-badge">${p.category || 'Uncategorized'}</span>
                        <h3 style="margin:0.5rem 0;">${p.title || 'Untitled Program'}</h3>
                        <p class="mb-16" style="font-size:0.95rem;flex-grow:1;">
                            ${(p.description || '').substring(0, 80)}${(p.description || '').length > 80 ? '...' : ''}
                        </p>
                        <div class="flex gap-8 text-small mb-8" style="align-items:center;">
                            <span class="text-bold"><i class="bi bi-geo-alt-fill"></i> ${p.location || 'No location'}</span>
                            <span class="text-bold" style="margin-left:auto;">${enrolled}/${cap} enrolled</span>
                        </div>
                        <div class="capacity-track">
                            <div class="capacity-fill${isFull ? ' full' : ''}" style="width:${fillPct}%;"></div>
                        </div>
                        <div class="flex gap-8 text-small" style="margin-top:1rem;">
                            ${pending   > 0 ? `<span class="mini-stat pending">${pending} pending</span>` : ''}
                            ${confirmed > 0 ? `<span class="mini-stat confirmed">${confirmed} confirmed</span>` : ''}
                            ${isFull ? '<span class="mini-stat full">FULL</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div class="flex-between mb-32 flex-wrap gap-16">
                    <h2 class="reveal no-border" style="margin:0;padding:0;">Parks &amp; Recreation</h2>
                    <div class="flex gap-16" style="align-items:center;">
                        <span class="reveal text-bold" style="font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;">${totalEnrollments} total enrollments</span>
                        ${role === 'admin' ? '<button class="btn reveal" data-action="manage-categories" style="border:2px solid var(--primary-navy);"><i class="bi bi-tags"></i> CATEGORIES</button>' : ''}
                        ${role === 'admin' ? '<button class="btn btn-primary reveal" data-action="new-program">+ NEW PROGRAM</button>' : ''}
                    </div>
                </div>
                <div class="programs-mgmt-grid">
                    ${programCards.length > 0 ? programCards : '<p class="reveal text-center" style="padding:3rem;">No programs found.</p>'}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       PROGRAM DETAIL — Full View with Enrollment Roster
       ========================================================================= */
    renderProgramDetail(program, enrollments, role) {
        const enrolled = parseInt(program.enrollment_count || 0);
        const pending  = parseInt(program.pending_count   || 0);
        const confirmed= parseInt(program.confirmed_count || 0);
        const cap      = parseInt(program.capacity || 1);
        const fillPct  = Math.min(Math.round((enrolled / cap) * 100), 100);

        const enrollmentRows = enrollments.map(e => {
            const isPending = e.status === 'pending';
            return `
                <tr>
                    <td><strong>#${e.id}</strong></td>
                    <td>${e.username}</td>
                    <td>${e.email}</td>
                    <td>${new Date(e.enrolled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                    <td><span class="status-badge status-${e.status}">${e.status}</span></td>
                    <td>
                        ${isPending ? `
                            <button class="btn btn-small btn-success" data-action="confirm-enroll" data-id="${e.id}" data-program-id="${program.id}" style="margin-right:4px;">CONFIRM</button>
                            <button class="btn btn-small btn-danger"  data-action="cancel-enroll"  data-id="${e.id}" data-program-id="${program.id}">REJECT</button>
                        ` : `<span class="opacity-7 text-small" style="font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">—</span>`}
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom:2rem;">
                    <a href="#manage-programs" style="font-weight:800;text-transform:uppercase;text-decoration:none;color:var(--primary-navy);font-size:0.9rem;letter-spacing:1px;"><i class="bi bi-arrow-left"></i> Back to Programs</a>
                </div>

                <div class="program-detail-hero reveal">
                    <div class="program-detail-img" style="background-image:url('../../get_image.php?type=program&id=${program.id}');"></div>
                    <div class="program-detail-info">
                        <span class="category-badge mb-16">${program.category}</span>
                        <h2 class="no-border" style="padding:0;margin:0 0 0.5rem 0;">${program.title}</h2>
                        <p class="mb-24">${program.description}</p>
                        <div class="flex-wrap gap-32 mb-24" style="display:flex;">
                            <div><span class="text-black text-small mb-8" style="text-transform:uppercase;letter-spacing:1px;display:block;">Location</span>${program.location}</div>
                            <div><span class="text-black text-small mb-8" style="text-transform:uppercase;letter-spacing:1px;display:block;">Capacity</span>${cap}</div>
                            <div><span class="text-black text-small mb-8" style="text-transform:uppercase;letter-spacing:1px;display:block;">Status</span><span class="status-badge status-${program.status}">${program.status}</span></div>
                        </div>
                        <div class="flex-wrap gap-32 mb-16" style="display:flex;">
                            <div class="detail-stat"><span class="detail-stat-number">${enrolled}</span><span class="detail-stat-label">ENROLLED</span></div>
                            <div class="detail-stat"><span class="detail-stat-number" style="color:var(--accent-blue);">${pending}</span><span class="detail-stat-label">PENDING</span></div>
                            <div class="detail-stat"><span class="detail-stat-number" style="color:var(--success);">${confirmed}</span><span class="detail-stat-label">CONFIRMED</span></div>
                        </div>
                        <div class="capacity-track" style="height:16px;">
                            <div class="capacity-fill${fillPct >= 100 ? ' full' : ''}" style="width:${fillPct}%;"></div>
                        </div>
                        <p class="text-small text-bold" style="margin-top:0.5rem;">${fillPct}% capacity filled</p>
                        ${role === 'admin' ? `
                            <div class="flex gap-8" style="margin-top:1.5rem;">
                                <button class="btn btn-small btn-primary" data-action="edit-program" data-id="${program.id}"><i class="bi bi-pencil-square"></i> EDIT PROGRAM</button>
                                <button class="btn btn-small btn-danger"  data-action="delete-program" data-id="${program.id}"><i class="bi bi-trash3"></i> DELETE</button>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div style="margin-top:3rem;">
                    <h2 class="reveal" style="font-size:clamp(1.5rem,3vw,2.5rem);">Enrollment Roster <span style="font-weight:400;font-size:0.7em;">(${enrollments.length})</span></h2>
                    <div class="reveal">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Citizen</th><th>Email</th><th>Enrolled</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${enrollmentRows.length > 0 ? enrollmentRows : '<tr><td colspan="6" class="text-center" style="padding:2rem;">No enrollments yet.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       PROGRAM FORM — Create / Edit with AI Image Generation
       ========================================================================= */
    renderProgramForm(program = null, categories = []) {
        const isEdit = !!program;
        const categoryOptions = categories.map(c =>
            `<option value="${c.name}" ${isEdit && program.category === c.name ? 'selected' : ''}>${c.name}</option>`
        ).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">${isEdit ? 'Edit Program' : 'New Community Program'}</h2>
                <div class="form-card reveal">
                    <form id="program-form" novalidate>
                        ${isEdit ? `<input type="hidden" name="id" value="${program.id}">` : ''}
                        <div class="form-group">
                            <label for="prog-title">Program Title</label>
                            <input type="text" id="prog-title" name="title" value="${isEdit ? program.title : ''}">
                        </div>
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="prog-category">Category</label>
                                <select id="prog-category" name="category">
                                    <option value="" disabled ${!isEdit ? 'selected' : ''}>Select a category</option>
                                    ${categoryOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="prog-capacity">Capacity</label>
                                <input type="number" id="prog-capacity" name="capacity" value="${isEdit ? program.capacity : '20'}">
                            </div>
                        </div>
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="prog-start-date">Start Date</label>
                                <input type="date" id="prog-start-date" name="start_date" value="${isEdit ? program.start_date : ''}">
                            </div>
                            <div class="form-group">
                                <label for="prog-end-date">End Date</label>
                                <input type="date" id="prog-end-date" name="end_date" value="${isEdit ? program.end_date : ''}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="prog-location">Location</label>
                            <input type="text" id="prog-location" name="location" value="${isEdit ? program.location : ''}">
                        </div>
                        <div class="form-group">
                            <label for="prog-desc">Description</label>
                            <textarea id="prog-desc" name="description" rows="4">${isEdit ? program.description : ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="prog-image">Program Image</label>
                            <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
                                <input type="file" id="prog-image" name="image" accept="image/*" style="flex:1;min-width:0;">
                                <select id="img-gen-provider" style="padding:0.75rem 1rem;font-size:0.85rem;border:var(--border-main);border-radius:var(--radius-sm);background:var(--white);color:var(--primary-navy);font-weight:600;cursor:pointer;">
                                    <option value="auto">Auto</option>
                                    <option value="gemini">Gemini (AI Image)</option>
                                    <option value="puter">Puter.ai</option>
                                    <option value="pollinations">Pollinations.ai</option>
                                </select>
                                <button type="button" class="btn" id="btn-generate-image" style="padding:0.8rem 1.5rem;font-size:0.9rem;white-space:nowrap;"><i class="bi bi-stars"></i> GENERATE WITH AI</button>
                                <button type="button" id="btn-cancel-image-gen" style="display:none;padding:0.8rem 1.2rem;font-size:0.9rem;border:var(--border-main);border-radius:var(--radius-sm);background:var(--white);color:var(--danger,#E74C3C);font-weight:700;cursor:pointer;white-space:nowrap;letter-spacing:0.5px;">✕ CANCEL</button>
                            </div>
                            <div id="prog-image-preview" style="margin-top:1rem;">
                                ${isEdit ? `<img src="../../get_image.php?type=program&id=${program.id}" style="max-width:200px;border:var(--border-main);" onerror="this.style.display='none'">` : ''}
                            </div>
                        </div>
                        ${isEdit ? `
                        <div class="form-group">
                            <label for="prog-status">Status</label>
                            <select id="prog-status" name="status">
                                <option value="active"     ${program.status === 'active'     ? 'selected' : ''}>Active</option>
                                <option value="cancelled"  ${program.status === 'cancelled'  ? 'selected' : ''}>Cancelled</option>
                                <option value="full"       ${program.status === 'full'       ? 'selected' : ''}>Full</option>
                            </select>
                        </div>
                        ` : ''}
                        <div style="display:flex;gap:1rem;margin-top:1rem;">
                            <button type="button" class="btn btn-primary" id="btn-save-program" data-action="save-program" style="flex:1;">
                                ${isEdit ? 'UPDATE PROGRAM' : 'CREATE PROGRAM'}
                            </button>
                            <a href="#manage-programs" class="btn" style="flex:1;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;">CANCEL</a>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       CATEGORY MANAGER — Admin only
       ========================================================================= */
    /* =========================================================================
       TRANSPORT MANAGEMENT (admin only)
       ========================================================================= */
    renderTransportManagement({ types, vehicles, trajets }) {
        // ── Transport Type cards ──────────────────────────────────────────────
        const typeCards = types.map(t => `
            <div style="display:flex;align-items:center;gap:1rem;padding:1rem;border:var(--border-main);background:var(--white);">
                <img src="../../get_image.php?type=transport_type&id=${t.idTransportType}"
                     style="width:64px;height:48px;object-fit:cover;border:var(--border-main);flex-shrink:0;"
                     onerror="this.style.display='none'">
                <div style="flex:1;min-width:0;">
                    <strong style="display:block;font-size:0.95rem;">${t.name}</strong>
                    ${t.description ? `<span style="font-size:0.82rem;opacity:0.65;">${t.description}</span>` : ''}
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-small" data-action="edit-transport-type" data-id="${t.idTransportType}">
                        <i class="bi bi-pencil-square"></i> EDIT
                    </button>
                    <button class="btn btn-small btn-danger" data-action="delete-transport-type" data-id="${t.idTransportType}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        `).join('');

        const typeOptions = types.map(t =>
            `<option value="${t.idTransportType}">${t.name}</option>`
        ).join('');

        const vehicleOptions = vehicles.map(v =>
            `<option value="${v.idTransport}" data-transport-type="${(v.type || '').toLowerCase()}">${v.name} (${v.type})</option>`
        ).join('');

        const transportTypeMap = Object.fromEntries(
            vehicles.map(v => [v.idTransport, (v.type || '').toLowerCase()])
        );

        const vehicleRows = vehicles.map(v => `
            <tr>
                <td>
                    <img src="../../get_image.php?type=transport&id=${v.idTransportType || 0}"
                         style="width:48px;height:32px;object-fit:cover;border:var(--border-main);"
                         onerror="this.style.display='none'">
                </td>
                <td><strong>${v.name}</strong></td>
                <td>${v.typeName || v.type}</td>
                <td>${v.capacity}</td>
                <td><span class="status-badge status-${v.status === 'Active' ? 'validated' : 'rejected'}">${v.status}</span></td>
                <td>
                    <button class="btn btn-small" data-action="edit-vehicle" data-id="${v.idTransport}" style="margin-right:4px;">
                        <i class="bi bi-pencil-square"></i> EDIT
                    </button>
                    <button class="btn btn-small btn-danger" data-action="delete-vehicle" data-id="${v.idTransport}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        const trajetRows = trajets.map(t => {
            const pct  = t.capacity > 0 ? Math.round((t.sold / t.capacity) * 100) : 0;
            const full = t.sold >= t.capacity && t.capacity > 0;
            return `
                <tr>
                    <td><strong>#${t.idTrajet}</strong></td>
                    <td>${t.departure}</td>
                    <td>${t.destination}</td>
                    <td>${t.departureTime ? t.departureTime.substring(0,5) : '—'}</td>
                    <td>
                        <div>${Number(t.price).toFixed(2)} TND</div>
                        <button type="button" class="btn btn-small" data-action="ai-price-row" data-id="${t.idTrajet}"
                            data-transport-type="${(t.transportType || t.type || '').toLowerCase()}"
                            data-dep-lat="${t.depLat || ''}"
                            data-dep-lng="${t.depLng || ''}"
                            data-dest-lat="${t.destLat || ''}"
                            data-dest-lng="${t.destLng || ''}"
                            data-destination="${(t.destAddress || t.destination || '').replace(/"/g, '&quot;')}">
                            AI
                        </button>
                        <div id="ai-suggestion-${t.idTrajet}" style="font-size:0.75rem;opacity:0.75;margin-top:0.25rem;"></div>
                    </td>
                    <td>${t.transportName || '—'}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <div style="flex:1;height:6px;background:var(--border-main);border-radius:3px;min-width:60px;">
                                <div style="width:${pct}%;height:100%;background:${full ? 'var(--danger)' : 'var(--success)'};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:0.8rem;font-weight:700;">${t.sold}/${t.capacity}</span>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-small" data-action="edit-trajet" data-id="${t.idTrajet}" style="margin-right:4px;">
                            <i class="bi bi-pencil-square"></i> EDIT
                        </button>
                        <button class="btn btn-small btn-danger" data-action="delete-trajet" data-id="${t.idTrajet}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Transport Management</h2>

                <!-- TRANSPORT TYPES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-tag"></i> Transport Types — ${types.length}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-type" style="border:2px solid var(--primary-navy);">+ ADD TYPE</button>
                </div>

                <div id="add-type-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-type-form" enctype="multipart/form-data">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Type Name</label>
                                    <input type="text" name="name" placeholder="e.g. Bus, Train, Metro">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" name="description" placeholder="Optional description">
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Icon / Photo (optional, max 2MB)</label>
                                    <input type="file" name="type_image" accept="image/*">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD TYPE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal" style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:3rem;">
                    ${typeCards || '<p style="opacity:0.6;padding:1rem 0;">No transport types yet.</p>'}
                </div>

                <!-- VEHICLES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:2rem 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-truck"></i> Fleet — ${vehicles.length} vehicle${vehicles.length !== 1 ? 's' : ''}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-vehicle" style="border:2px solid var(--primary-navy);">+ ADD VEHICLE</button>
                </div>

                <div id="add-vehicle-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-vehicle-form">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Vehicle Name</label>
                                    <input type="text" name="name" placeholder="e.g. City Express 01">
                                </div>
                                <div class="form-group">
                                    <label>Type Label</label>
                                    <input type="text" name="type" placeholder="Bus / Train / Metro">
                                </div>
                                <div class="form-group">
                                    <label>Capacity</label>
                                    <input type="number" name="capacity" min="1" placeholder="50">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="Active">Active</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Transport Type</label>
                                    <select name="idTransportType">
                                        <option value="">— None —</option>
                                        ${typeOptions}
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD VEHICLE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal" style="margin-bottom:3rem;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Photo</th><th>Name</th><th>Type</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>${vehicleRows || '<tr><td colspan="6" style="text-align:center;padding:2rem;">No vehicles.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>

                <!-- ROUTES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-signpost-split"></i> Routes — ${trajets.length} route${trajets.length !== 1 ? 's' : ''}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-trajet" style="border:2px solid var(--primary-navy);">+ ADD ROUTE</button>
                </div>

                <div id="add-trajet-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-trajet-form">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Departure</label>
                                    <input type="text" name="departure" placeholder="Search a location..." autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>Destination</label>
                                    <input type="text" name="destination" placeholder="Search a location..." autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>Departure Date</label>
                                    <input type="date" name="departureDate" value="${new Date().toISOString().split('T')[0]}">
                                </div>
                                <div class="form-group">
                                    <label>Departure Time</label>
                                    <input type="time" name="departureTime">
                                </div>
                                <div class="form-group" style="display:flex;flex-direction:column;gap:0.5rem;">
                                    <label>Price</label>
                                    <div style="display:flex;gap:0.5rem;align-items:flex-start;">
                                        <input type="number" name="price" id="route-price" min="0" step="0.01" placeholder="9.99" style="flex:1;">
                                        <button type="button" class="btn btn-primary" id="btn-ai-price" style="white-space:nowrap;">GET BEST PRICE</button>
                                    </div>
                                    <div id="ai-price-suggestion" style="font-size:0.85rem;opacity:0.75;line-height:1.3;">Click the button to compute the best route price in Tunisian Dinar.</div>
                                </div>
                                <input type="hidden" name="distance" id="routeDistance">
                                <input type="hidden" name="transportType" id="routeTransportType">
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Vehicle</label>
                                    <select name="idTransport">
                                        <option value="" disabled selected>Select vehicle</option>
                                        ${vehicleOptions}
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Route Map (Click or drag markers)</label>
                                    <div id="route-map" style="height:300px; border:2px solid var(--border-main); border-radius:8px; z-index: 1;"></div>
                                    <input type="hidden" name="depLat" id="depLat">
                                    <input type="hidden" name="depLng" id="depLng">
                                    <input type="hidden" name="destLat" id="destLat">
                                    <input type="hidden" name="destLng" id="destLng">
                                    <input type="hidden" name="depAddress" id="depAddress">
                                    <input type="hidden" name="destAddress" id="destAddress">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD ROUTE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Ref</th><th>From</th><th>To</th><th>Time</th><th>Price</th><th>Vehicle</th><th>Occupancy</th><th>Actions</th></tr></thead>
                            <tbody>${trajetRows || '<tr><td colspan="8" style="text-align:center;padding:2rem;">No routes.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        // Setup form validations for dynamically added forms
        if (window.setupTransportValidations) {
            setTimeout(() => window.setupTransportValidations(), 100);
        }
        this.triggerObserver();
    },

    /* =========================================================================
       SLOT MANAGEMENT — Admin only (agent availability configuration)
       ========================================================================= */
    renderSlotManagement(slots, agents, serviceTypes) {
        const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        const agentOptions = agents.map(a =>
            `<option value="${a.id}">${a.username}</option>`
        ).join('');

        const serviceOptions = serviceTypes.map(s =>
            `<option value="${s}">${s}</option>`
        ).join('');

        const dayOptions = DAYS.map((d, i) =>
            `<option value="${i}">${d}</option>`
        ).join('');

        const rows = slots.map(s => `
            <tr>
                <td><strong>#${s.id}</strong></td>
                <td>${s.agent_name || '—'}</td>
                <td>${s.service_type}</td>
                <td>${DAYS[s.day_of_week] ?? s.day_of_week}</td>
                <td>${s.start_time ? s.start_time.substring(0,5) : '—'} – ${s.end_time ? s.end_time.substring(0,5) : '—'}</td>
                <td><span class="status-badge status-${s.is_active ? 'validated' : 'rejected'}">${s.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn btn-small btn-danger" data-action="delete-slot" data-id="${s.id}"><i class="bi bi-trash3"></i></button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Slot Management</h2>
                <p class="reveal" style="margin-bottom:2rem;opacity:0.7;">Define when each agent is available to handle specific service types. Citizens can only book appointments during configured slots.</p>

                <div class="form-card reveal" style="margin-bottom:2rem;">
                    <h3 style="margin:0 0 1.5rem;font-size:1.1rem;text-transform:uppercase;letter-spacing:1px;">Add New Slot</h3>
                    <form id="slot-form">
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="slot-agent">Agent</label>
                                <select id="slot-agent" name="agent_id" required>
                                    <option value="" disabled selected>Select agent</option>
                                    ${agentOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="slot-service">Service Type</label>
                                <select id="slot-service" name="service_type" required>
                                    <option value="" disabled selected>Select service</option>
                                    ${serviceOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="slot-day">Day of Week</label>
                                <select id="slot-day" name="day_of_week" required>
                                    <option value="" disabled selected>Select day</option>
                                    ${dayOptions}
                                </select>
                            </div>
                            <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;align-items:end;">
                                <div>
                                    <label for="slot-start">Start Time</label>
                                    <input type="time" id="slot-start" name="start_time" required>
                                </div>
                                <div>
                                    <label for="slot-end">End Time</label>
                                    <input type="time" id="slot-end" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">+ ADD SLOT</button>
                    </form>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Agent</th><th>Service</th><th>Day</th><th>Hours</th><th>Status</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="7" style="text-align:center;padding:2rem;">No slots configured yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderCategoryManager(categories) {
        const rows = categories.map(c => `
            <tr>
                <td><strong>#${c.id}</strong></td>
                <td>
                    <span class="category-display-name" data-id="${c.id}">${c.name}</span>
                    <input type="text" class="category-edit-input" data-id="${c.id}" value="${c.name}" style="display:none;width:100%;padding:0.5rem;border:2px solid var(--primary-navy);font-weight:700;">
                </td>
                <td>${c.created_at ? new Date(c.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                <td>
                    <button class="btn btn-small" data-action="edit-category" data-id="${c.id}" style="margin-right:4px;"><i class="bi bi-pencil-square"></i> EDIT</button>
                    <button class="btn btn-small btn-success" data-action="save-category" data-id="${c.id}" style="display:none;margin-right:4px;"><i class="bi bi-check-lg"></i> SAVE</button>
                    <button class="btn btn-small btn-secondary" data-action="cancel-edit-category" data-id="${c.id}" style="display:none;margin-right:4px;">CANCEL</button>
                    <button class="btn btn-small btn-danger" data-action="delete-category" data-id="${c.id}"><i class="bi bi-trash3"></i></button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom:2rem;">
                    <a href="#manage-programs" style="font-weight:800;text-transform:uppercase;text-decoration:none;color:var(--primary-navy);font-size:0.9rem;letter-spacing:1px;"><i class="bi bi-arrow-left"></i> Back to Programs</a>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;border:none;padding:0;">Manage Categories</h2>
                </div>
                <div class="form-card reveal" style="margin-bottom:2rem;">
                    <form id="category-form" style="display:flex;gap:1rem;align-items:flex-end;">
                        <div class="form-group" style="flex:1;margin:0;">
                            <label for="new-category-name">New Category Name</label>
                            <input type="text" id="new-category-name" name="name" placeholder="e.g. Health &amp; Wellness" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height:fit-content;padding:0.85rem 2rem;">+ ADD</button>
                    </form>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>ID</th><th>Name</th><th>Created</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="4" style="text-align:center;padding:2rem;">No categories yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       FORUM MODERATION (admin only)
       ========================================================================= */
    renderForumModeration(posts, comments, stats) {
        const statusClass = s => s === 'open' ? 'pending' : s === 'pinned' ? 'validated' : 'rejected';
        const aiClass = f => f === 'flagged' ? 'danger' : f === 'review' ? 'warning' : 'success';

        // --- AI Stats Cards ---
        const postFlags = {};
        (stats?.post_flags || []).forEach(r => { postFlags[r.ai_flag || 'unscanned'] = parseInt(r.count); });
        const commentFlags = {};
        (stats?.comment_flags || []).forEach(r => { commentFlags[r.ai_flag || 'unscanned'] = parseInt(r.count); });

        const flaggedPosts    = stats?.flagged_posts    || [];
        const flaggedComments = stats?.flagged_comments || [];
        const urgencyAlerts   = stats?.urgency_alerts   || [];
        const totalFlagged    = flaggedPosts.length + flaggedComments.length;
        const highUrgency     = urgencyAlerts.reduce((s, r) => s + parseInt(r.count), 0);

        // Flagged alert banner
        const alertBanner = totalFlagged > 0 ? `
            <div class="reveal" style="margin-bottom:2rem;padding:1.2rem 1.5rem;border:2px solid var(--danger);border-radius:var(--radius-md);
                        background:rgba(231,76,60,0.06);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem;color:var(--danger);"></i>
                <div>
                    <strong style="color:var(--danger);font-size:1rem;">${totalFlagged} Flagged Content Item${totalFlagged !== 1 ? 's' : ''}</strong>
                    <span style="opacity:0.7;font-size:0.9rem;margin-left:0.5rem;">
                        (${flaggedPosts.length} post${flaggedPosts.length !== 1 ? 's' : ''}, ${flaggedComments.length} comment${flaggedComments.length !== 1 ? 's' : ''})
                    </span>
                    ${highUrgency > 0 ? `<span style="margin-left:1rem;padding:0.2rem 0.6rem;background:rgba(192,57,43,0.15);color:#922b21;border-radius:20px;font-size:0.75rem;font-weight:800;">
                        ${highUrgency} HIGH/CRITICAL URGENCY
                    </span>` : ''}
                </div>
            </div>
        ` : '';

        // Stats cards
        const statsHtml = `
            <div class="reveal" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:2.5rem;">
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;">
                    <div style="font-size:2rem;font-weight:900;color:var(--success);">${postFlags['clean'] || 0}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">Clean Posts</div>
                </div>
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;">
                    <div style="font-size:2rem;font-weight:900;color:#d68910;">${postFlags['review'] || 0}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">Under Review</div>
                </div>
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;${(postFlags['flagged'] || 0) > 0 ? 'border-color:var(--danger);background:rgba(231,76,60,0.04);' : ''}">
                    <div style="font-size:2rem;font-weight:900;color:var(--danger);">${postFlags['flagged'] || 0}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">Flagged Posts</div>
                </div>
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;">
                    <div style="font-size:2rem;font-weight:900;color:var(--accent-blue);">${(posts || []).length}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">Total Posts</div>
                </div>
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;">
                    <div style="font-size:2rem;font-weight:900;color:var(--primary-navy);">${(comments || []).length}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">Total Comments</div>
                </div>
                <div style="padding:1.2rem;border:var(--border-main);border-radius:var(--radius-md);text-align:center;${highUrgency > 0 ? 'border-color:#c0392b;background:rgba(192,57,43,0.04);' : ''}">
                    <div style="font-size:2rem;font-weight:900;color:#922b21;">${highUrgency}</div>
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">High Urgency</div>
                </div>
            </div>
        `;

        // --- Post Rows (with AI badge) ---
        const postRows = (posts || []).map(p => {
            const date = p.created_at
                ? new Date(p.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';
            const aiFlagBadge = p.ai_flag && p.ai_flag !== 'clean'
                ? `<span style="display:inline-block;padding:0.15rem 0.5rem;border-radius:20px;font-size:0.65rem;font-weight:800;text-transform:uppercase;
                            background:${p.ai_flag === 'flagged' ? 'rgba(231,76,60,0.12)' : 'rgba(243,156,18,0.12)'};
                            color:${p.ai_flag === 'flagged' ? '#c0392b' : '#d68910'};
                            border:1px solid ${p.ai_flag === 'flagged' ? 'rgba(231,76,60,0.3)' : 'rgba(243,156,18,0.3)'};"
                        title="${this._esc(p.ai_reason || '')}">
                        <i class="bi bi-robot"></i> ${p.ai_flag.toUpperCase()}
                    </span>`
                : '';
            const urgencyBadge = p.ai_urgency && p.ai_urgency !== 'low'
                ? `<span style="display:inline-block;padding:0.15rem 0.5rem;border-radius:20px;font-size:0.65rem;font-weight:800;text-transform:uppercase;
                            background:${p.ai_urgency === 'critical' ? 'rgba(192,57,43,0.12)' : p.ai_urgency === 'high' ? 'rgba(230,126,34,0.12)' : 'rgba(52,152,219,0.12)'};
                            color:${p.ai_urgency === 'critical' ? '#922b21' : p.ai_urgency === 'high' ? '#ca6f1e' : '#2471a3'};">
                        <i class="bi bi-exclamation-diamond"></i> ${p.ai_urgency.toUpperCase()}
                    </span>`
                : '';
            const rowBg = p.ai_flag === 'flagged' ? 'background:rgba(231,76,60,0.04);' : '';
            return `
                <tr style="${rowBg}">
                    <td><strong>#${p.post_id}</strong></td>
                    <td>
                        <strong>${this._esc(p.title)}</strong><br>
                        <span style="font-size:0.82rem;opacity:0.65;">${this._esc(p.author_name)}</span>
                        ${aiFlagBadge || urgencyBadge ? `<div style="margin-top:0.3rem;display:flex;gap:0.3rem;flex-wrap:wrap;">${aiFlagBadge}${urgencyBadge}</div>` : ''}
                    </td>
                    <td><span class="status-badge">${this._esc(p.category)}</span></td>
                    <td><span class="status-badge status-${statusClass(p.status)}">${p.status.toUpperCase()}</span></td>
                    <td style="font-size:0.82rem;">${p.comment_count ?? 0}</td>
                    <td style="font-size:0.82rem;">${date}</td>
                    <td style="white-space:nowrap;">
                        <select class="forum-status-select" data-post-id="${p.post_id}"
                                style="padding:0.3rem 0.5rem;border:2px solid var(--border-main);font-weight:700;font-size:0.8rem;margin-right:4px;">
                            <option value="open"   ${p.status === 'open'   ? 'selected' : ''}>Open</option>
                            <option value="pinned" ${p.status === 'pinned' ? 'selected' : ''}>Pinned</option>
                            <option value="closed" ${p.status === 'closed' ? 'selected' : ''}>Closed</option>
                        </select>
                        <button class="btn btn-small btn-success" data-action="forum-save-status" data-id="${p.post_id}"
                                style="margin-right:4px;padding:0.3rem 0.6rem;">✓</button>
                        <button class="btn btn-small btn-danger" data-action="forum-delete-post" data-id="${p.post_id}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        // --- Comment Rows (with AI badge) ---
        const commentRows = (comments || []).map(c => {
            const date = c.created_at
                ? new Date(c.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '—';
            const excerpt = c.content.length > 120 ? c.content.substring(0, 120) + '…' : c.content;
            const aiFlagBadge = c.ai_flag && c.ai_flag !== 'clean'
                ? `<span style="display:inline-block;padding:0.15rem 0.4rem;border-radius:20px;font-size:0.6rem;font-weight:800;text-transform:uppercase;margin-left:0.4rem;
                            background:${c.ai_flag === 'flagged' ? 'rgba(231,76,60,0.12)' : 'rgba(243,156,18,0.12)'};
                            color:${c.ai_flag === 'flagged' ? '#c0392b' : '#d68910'};"
                        title="${this._esc(c.ai_reason || '')}">
                        <i class="bi bi-robot"></i> ${c.ai_flag.toUpperCase()}
                    </span>`
                : '';
            const rowBg = c.ai_flag === 'flagged' ? 'background:rgba(231,76,60,0.04);' : '';
            return `
                <tr style="${rowBg}">
                    <td><strong>#${c.comment_id}</strong></td>
                    <td style="font-size:0.85rem;">${this._esc(excerpt)}${aiFlagBadge}</td>
                    <td style="font-size:0.82rem;">${this._esc(c.author_name)}</td>
                    <td style="font-size:0.82rem;">${this._esc(c.post_title || '—')}</td>
                    <td style="font-size:0.82rem;">${date}</td>
                    <td>
                        <button class="btn btn-small btn-danger" data-action="forum-delete-comment" data-id="${c.comment_id}">
                            <i class="bi bi-trash3"></i> REMOVE
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Forum Moderation</h2>
                <p class="reveal" style="margin-bottom:2rem;opacity:0.7;">Manage citizen forum posts and comments. Pin important discussions, close resolved threads, and remove inappropriate content.</p>

                ${alertBanner}
                ${statsHtml}

                <h3 class="reveal" style="text-transform:uppercase;letter-spacing:1px;font-size:1rem;margin-bottom:1rem;">
                    <i class="bi bi-megaphone"></i> Posts (${(posts || []).length})
                </h3>
                <div class="reveal" style="margin-bottom:3rem;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Title / Author</th><th>Category</th><th>Status</th><th>Comments</th><th>Date</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${postRows.length > 0 ? postRows : '<tr><td colspan="7" style="text-align:center;padding:2rem;">No forum posts yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>

                <h3 class="reveal" style="text-transform:uppercase;letter-spacing:1px;font-size:1rem;margin-bottom:1rem;">
                    <i class="bi bi-chat-dots"></i> Recent Comments (${(comments || []).length})
                </h3>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Content</th><th>Author</th><th>Post</th><th>Date</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${commentRows.length > 0 ? commentRows : '<tr><td colspan="6" style="text-align:center;padding:2rem;">No comments yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /** Minimal HTML escape helper */
    _esc(str) {
        if (!str) return '';
        const el = document.createElement('span');
        el.textContent = str;
        return el.innerHTML;
    }
};

export default view;


