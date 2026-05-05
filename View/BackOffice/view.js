/**
 * view.js
 * BackOffice rendering logic
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
                CivicPortal Staff
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                ${role === 'worker' ? '<li><a href="#worker-dashboard">dashboard</a></li>' : ''}
                ${role === 'admin' ? '<li><a href="#admin-stats">statistics</a></li><li><a href="#admin-inbox">inbox</a></li>' : ''}
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                <div class="context-menu-wrapper">
                    <button id="context-toggle-btn" class="user-role-badge context-toggle-btn" type="button" title="Switch portal/role">
                        ${role}
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
        let content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>Staff Portal</h1>
                    <p>Welcome back, ${user.name}. Access your staff module.</p>
                </section>
            </div>
        `;

        if (user.role === 'worker') {
             content += `
            <section class="page-container">
                <h2 class="reveal">Operations Console</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Service Request Queue</h3>
                        <p>Process pending administrative filings. Validate or reject documents submitted by the citizens of CivicPortal.</p>
                        <a href="#worker-dashboard" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Open Dashboard</a>
                    </div>
                </div>
            </section>
            `;
        } else if (user.role === 'admin') {
             content += `
            <section class="page-container">
                <h2 class="reveal">Administrative Overview</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Platform Statistics</h3>
                        <p>View real-time aggregated data across all civic modules to monitor system health and engagement.</p>
                        <a href="#admin-stats" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Stats</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Grievance Inbox</h3>
                        <p>Review and process citizen feedback securely routed to the administrative branch.</p>
                        <a href="#admin-inbox" class="btn" style="align-self: flex-start; margin-top: auto;">Open Inbox</a>
                    </div>
                </div>
            </section>
            `;
        }

        this.app.innerHTML = content;
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
                        <div class="form-group" style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">UPDATE DETAILS</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderWorkerDashboard(requests, filters = { query: '', status: 'all', sortBy: 'date_desc' }) {
        const tableRows = requests.map(r => `
            <tr>
                <td><strong>#${r.id}</strong></td>
                <td>${r.title}</td>
                <td class="request-desc-cell">${r.description || '<em style="opacity:0.5">No description</em>'}</td>
                <td>${new Date(r.createdAt).toLocaleDateString()}</td>
                <td><span class="status-badge status-${(r.status || '').replace(/\s+/g, '-')}">${r.status}</span></td>
                <td>
                    <button class="btn btn-small" data-action="view-docs" data-id="${r.id}" style="margin-bottom: 5px;">DOCS</button>
                    <button class="btn btn-small btn-ai" data-action="ai-analyze" data-id="${r.id}" style="margin-bottom: 5px;">
                        <span class="ai-icon" aria-hidden="true">✨</span> ANALYSER IA
                    </button>
                    ${r.status === 'pending' ? `<button class="btn btn-small btn-primary" data-action="start-review" data-id="${r.id}" style="margin-bottom: 5px;">START REVIEW</button>` : ''}
                    ${r.status === 'under review' ? `
                        <button class="btn btn-small btn-success" data-action="validate" data-id="${r.id}" style="margin-bottom: 5px;">APPROVE</button>
                        <button class="btn btn-small btn-danger" data-action="reject" data-id="${r.id}">REJECT</button>
                    ` : ''}
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Worker Dashboard</h2>
                <div class="form-card reveal" style="margin-bottom:1.25rem;">
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:0.75rem;align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label for="dashboard-search">Search</label>
                            <input id="dashboard-search" type="text" placeholder="ID, service, status..." value="${filters.query || ''}">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label for="dashboard-status-filter">Status</label>
                            <select id="dashboard-status-filter">
                                <option value="all" ${filters.status === 'all' ? 'selected' : ''}>All</option>
                                <option value="pending" ${filters.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="under review" ${filters.status === 'under review' ? 'selected' : ''}>Under review</option>
                                <option value="approved" ${filters.status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="rejected" ${filters.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label for="dashboard-sort">Sort</label>
                            <select id="dashboard-sort">
                                <option value="date_desc" ${filters.sortBy === 'date_desc' ? 'selected' : ''}>Newest</option>
                                <option value="date_asc" ${filters.sortBy === 'date_asc' ? 'selected' : ''}>Oldest</option>
                                <option value="status_asc" ${filters.sortBy === 'status_asc' ? 'selected' : ''}>Status A-Z</option>
                                <option value="status_desc" ${filters.sortBy === 'status_desc' ? 'selected' : ''}>Status Z-A</option>
                            </select>
                        </div>
                        <button class="btn btn-small" data-action="reset-dashboard-filters">RESET</button>
                    </div>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Service Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="6" style="text-align:center;">no data available</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Documents Panel (hidden by default) -->
            <div id="docs-panel" class="docs-panel" style="display:none;">
                <section class="page-container">
                    <div class="documents-header">
                        <h3 id="docs-panel-title">Documents for Request</h3>
                        <button class="btn btn-small" data-action="close-docs">CLOSE</button>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>File Path</th>
                                    <th>Category</th>
                                    <th>Upload Date</th>
                                </tr>
                            </thead>
                            <tbody id="docs-panel-body">
                                <tr><td colspan="3" style="text-align:center;">No documents.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        `;
        this.triggerObserver();
    },

    showDocsPanel(requestId, documents, auditLogs = []) {
        const panel = document.getElementById('docs-panel');
        const title = document.getElementById('docs-panel-title');
        const tbody = document.getElementById('docs-panel-body');

        title.textContent = `Documents for Request #${requestId}`;

        if (documents && documents.length > 0) {
            tbody.innerHTML = documents.map(d => `
                <tr>
                    <td><strong>${d.filePath}</strong><br><a href="../../uploads/${d.filePath}" target="_blank" rel="noopener noreferrer">Open</a></td>
                    <td><span class="category-badge">${d.type}</span></td>
                    <td>${new Date(d.uploadedAt).toLocaleDateString()}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:2rem;">No documents attached.</td></tr>';
        }

        panel.style.display = 'block';
        panel.scrollIntoView({ behavior: 'smooth' });

        const existingTimeline = panel.querySelector('.docs-audit-timeline');
        if (existingTimeline) existingTimeline.remove();
        const timeline = document.createElement('div');
        timeline.className = 'docs-audit-timeline';
        timeline.style.marginTop = '1rem';
        timeline.innerHTML = `
            <h4>Activity</h4>
            <ul style="padding-left:1rem; margin-top:0.5rem;">
                ${(auditLogs.length > 0
                    ? auditLogs.map((log) => `
                        <li style="margin-bottom:0.35rem;">
                            ${new Date(log.createdAt).toLocaleString()} - ${log.action}
                            ${log.fromStatus ? `(${log.fromStatus} -> ${log.toStatus || '-'})` : ''}
                            ${log.note ? `<br><span style="opacity:0.8;">${log.note}</span>` : ''}
                        </li>
                    `).join('')
                    : '<li>No activity for this request.</li>'
                )}
            </ul>
        `;
        panel.querySelector('.page-container')?.appendChild(timeline);
    },

    hideDocsPanel() {
        const panel = document.getElementById('docs-panel');
        if (panel) panel.style.display = 'none';
    },

    renderAdminStats(stats) {
        const breakdown = stats.statusBreakdown || {};
        const topServices = stats.topServices || [];
        const recent = stats.recentActivity || [];
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">System Statistics</h2>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <h3>Total Users</h3>
                        <p class="stats-number">${stats.usersCount}</p>
                    </div>
                    <div class="editorial-card reveal editorial-highlight">
                        <h3>Service Requests</h3>
                        <p class="stats-number">${stats.requestsCount}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Active Programs</h3>
                        <p class="stats-number">${stats.programsCount}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Enrollments</h3>
                        <p class="stats-number">${stats.enrollmentsCount}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Documents</h3>
                        <p class="stats-number">${stats.documentsCount || 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Complaints</h3>
                        <p class="stats-number">${stats.complaintsCount}</p>
                    </div>
                </div>
                <div class="editorial-grid" style="margin-top:1rem;">
                    <div class="editorial-card reveal">
                        <h3>Status Breakdown</h3>
                        <p>Pending: <strong>${breakdown['pending'] || 0}</strong></p>
                        <p>Under review: <strong>${breakdown['under review'] || 0}</strong></p>
                        <p>Approved: <strong>${breakdown['approved'] || 0}</strong></p>
                        <p>Rejected: <strong>${breakdown['rejected'] || 0}</strong></p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Top Services</h3>
                        ${topServices.length > 0 ? topServices.map((s) => `<p>${s.title}: <strong>${s.total}</strong></p>`).join('') : '<p>No data yet.</p>'}
                    </div>
                </div>
                <div class="form-card reveal" style="margin-top:1rem;">
                    <h3 style="margin-bottom:0.75rem;">Recent Activity</h3>
                    <div style="max-height:260px; overflow:auto;">
                        ${recent.length > 0 ? recent.map((item) => `
                            <div style="padding:0.5rem 0; border-bottom:1px solid rgba(255,255,255,0.08);">
                                <strong>#${item.requestId}</strong> - ${item.action}
                                ${item.fromStatus ? `(${item.fromStatus} -> ${item.toStatus || '-'})` : ''}
                                ${item.note ? `<br><span style="opacity:0.8;">${item.note}</span>` : ''}
                            </div>
                        `).join('') : '<p>No recent activity.</p>'}
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    showRejectionReasonDialog() {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.id = 'reject-reason-modal';
            modal.innerHTML = `
                <div class="modal-card">
                    <div class="modal-header">
                        <h3>Reject Request</h3>
                        <button class="modal-close" id="reject-reason-close">&times;</button>
                    </div>
                    <div class="form-group">
                        <label for="reject-reason-input">Reason (required)</label>
                        <textarea id="reject-reason-input" rows="5" placeholder="Explain why this request is rejected..."></textarea>
                    </div>
                    <div style="display:flex; gap:0.75rem;">
                        <button class="btn btn-danger" id="reject-reason-submit" style="flex:1;">CONFIRM REJECTION</button>
                        <button class="btn" id="reject-reason-cancel" style="flex:1;">CANCEL</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            const close = () => {
                modal.remove();
                resolve(null);
            };

            modal.querySelector('#reject-reason-close')?.addEventListener('click', close);
            modal.querySelector('#reject-reason-cancel')?.addEventListener('click', close);
            modal.querySelector('#reject-reason-submit')?.addEventListener('click', () => {
                const reason = modal.querySelector('#reject-reason-input')?.value ?? '';
                modal.remove();
                resolve(reason);
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) close();
            });
        });
    },

    // ── AI Analysis Modal (Worker) ───────────────────────────────

    showAiAnalysisModal(requestId) {
        const existing = document.getElementById('ai-analysis-modal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.id = 'ai-analysis-modal';
        modal.innerHTML = `
            <div class="modal-card ai-analysis-card">
                <div class="modal-header">
                    <h3><span class="ai-icon" aria-hidden="true">✨</span> Analyse IA — Demande #${requestId}</h3>
                    <button class="modal-close" id="ai-analysis-close" type="button">&times;</button>
                </div>
                <div class="ai-analysis-body" id="ai-analysis-body">
                    <div class="ai-loading">
                        <span class="ai-spinner" aria-hidden="true"></span>
                        <span>Analyse en cours…</span>
                    </div>
                </div>
                <p class="ai-disclaimer">
                    L'IA est un assistant. La décision finale (approuver / rejeter)
                    reste à votre charge.
                </p>
            </div>
        `;
        document.body.appendChild(modal);

        const close = () => modal.remove();
        modal.querySelector('#ai-analysis-close')?.addEventListener('click', close);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) close();
        });
        return modal;
    },

    renderAiAnalysisResult(result) {
        const body = document.getElementById('ai-analysis-body');
        if (!body) return;

        if (!result) {
            body.innerHTML = `<p class="ai-error">L'analyse a échoué. Réessayez plus tard.</p>`;
            return;
        }

        const isFallback = result.status && result.status !== 'ok';
        const recLabel = {
            approve: 'APPROUVER',
            reject: 'REJETER',
            request_more_info: 'DEMANDER + D\'INFOS'
        }[result.recommendation] || 'À ÉVALUER';
        const recClass = {
            approve: 'rec-approve',
            reject: 'rec-reject',
            request_more_info: 'rec-info'
        }[result.recommendation] || '';

        const issues = (result.issues || []).map(i => `<li>${this._esc(i)}</li>`).join('');
        const score = Math.max(0, Math.min(100, result.validityScore || 0));
        const comment = this._esc(result.suggestedComment || '');

        body.innerHTML = `
            ${isFallback ? `<p class="ai-warning">${this._esc(result.message || 'AI service unavailable.')}</p>` : ''}

            <div class="ai-section">
                <div class="ai-section-title">Résumé</div>
                <p>${this._esc(result.summary || '—')}</p>
            </div>

            <div class="ai-section ai-grid">
                <div>
                    <div class="ai-section-title">Score de validité</div>
                    <div class="ai-score">
                        <div class="ai-score-bar"><span style="width:${score}%"></span></div>
                        <strong>${score}/100</strong>
                    </div>
                </div>
                <div>
                    <div class="ai-section-title">Recommandation</div>
                    <span class="ai-rec ${recClass}">${recLabel}</span>
                </div>
            </div>

            ${issues ? `
                <div class="ai-section">
                    <div class="ai-section-title">Problèmes détectés</div>
                    <ul class="ai-issues">${issues}</ul>
                </div>
            ` : ''}

            <div class="ai-section">
                <div class="ai-section-title">Commentaire pour le citoyen</div>
                <textarea class="ai-comment" id="ai-suggested-comment" rows="4" readonly>${comment}</textarea>
                <div class="ai-actions">
                    <button type="button" class="btn btn-small" id="ai-copy-comment">COPIER</button>
                    <button type="button" class="btn btn-small btn-danger" id="ai-use-as-rejection" data-recommendation="${result.recommendation || ''}">
                        UTILISER COMME MOTIF DE REJET
                    </button>
                </div>
            </div>
        `;
    },

    closeAiAnalysisModal() {
        const m = document.getElementById('ai-analysis-modal');
        if (m) m.remove();
    },

    _esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    },

    renderAdminInbox(complaints) {
        const tableRows = complaints.map(c => `
            <tr>
                <td><strong>#${c.id}</strong></td>
                <td>${c.date}</td>
                <td>${c.subject}</td>
                <td>${c.body}</td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Grievance Inbox</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="4" style="text-align:center;">inbox is empty</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
