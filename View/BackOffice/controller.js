/**
 * controller.js
 * BackOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    dashboardFilters: {
        query: '',
        status: 'all',
        sortBy: 'date_desc'
    },

    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRoleChange('worker', false); 
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

    setupEventListeners() {
        document.addEventListener('click', async (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'validate') {
                await this.handleStatusUpdate(id, 'approved');
            } else if (action === 'reject') {
                await this.handleStatusUpdate(id, 'rejected');
            } else if (action === 'start-review') {
                await this.handleStatusUpdate(id, 'under review');
            } else if (action === 'view-docs') {
                await this.handleViewDocuments(parseInt(id));
            } else if (action === 'close-docs') {
                view.hideDocsPanel();
            } else if (action === 'reset-dashboard-filters') {
                this.dashboardFilters = { query: '', status: 'all', sortBy: 'date_desc' };
                this.renderFilteredWorkerDashboard();
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', (e) => {
            e.preventDefault();
            if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'dashboard-search') {
                this.dashboardFilters.query = e.target.value;
                this.renderFilteredWorkerDashboard();
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.id === 'dashboard-status-filter') {
                this.dashboardFilters.status = e.target.value;
                this.renderFilteredWorkerDashboard();
            } else if (e.target.id === 'dashboard-sort') {
                this.dashboardFilters.sortBy = e.target.value;
                this.renderFilteredWorkerDashboard();
            }
        });
    },

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();

        switch (hash) {
            case '#home':
                view.renderHome(user);
                break;
            case '#worker-dashboard':
                if (user.role === 'worker') {
                    await model.sync();
                    this.renderFilteredWorkerDashboard();
                } else {
                    window.location.hash = '#home';
                }
                break;
            case '#profile':
                view.renderProfile(user);
                break;
            case '#admin-stats':
                if (user.role === 'admin') {
                    const stats = await model.getStats();
                    view.renderAdminStats(stats);
                } else {
                    window.location.hash = '#home';
                }
                break;
            case '#admin-inbox':
                if (user.role === 'admin') {
                    view.renderAdminInbox(model.getComplaints());
                } else {
                    window.location.hash = '#home';
                }
                break;
            default:
                view.renderHome(user);
                break;
        }
    },

    handleRoleChange(role, triggerRouting = true) {
        model.setCurrentUser(role);
        view.renderNavBar(role);
        
        if (triggerRouting) {
            if (window.location.hash !== '#home' && window.location.hash !== '') {
                window.location.hash = '#home';
            } else {
                this.handleRouting();
            }
        } else {
            this.handleRouting();
        }
    },

    async handleStatusUpdate(requestId, status) {
        const id = parseInt(requestId);
        const request = model.getServiceRequests().find(r => r.id === id);
        if (!request) return;

        let rejectionReason = null;
        if (status === 'rejected') {
            rejectionReason = await view.showRejectionReasonDialog();
            if (rejectionReason === null) return;
            if (!rejectionReason.trim()) {
                view.renderToast('Rejection reason is required.', 'danger');
                return;
            }
        }

        const updated = await model.updateRequestStatus(id, status, rejectionReason);
        if (!updated) {
            view.renderToast('Status transition refused by workflow.', 'danger');
            return;
        }

        await model.sync();
        view.renderToast(`Request moved to "${status}".`);
        this.renderFilteredWorkerDashboard();
    },

    async handleViewDocuments(requestId) {
        const documents = await model.getDocuments(requestId);
        const logs = await model.getRequestAuditLogs(requestId);
        view.showDocsPanel(requestId, documents || [], logs || []);
    },

    renderFilteredWorkerDashboard() {
        const all = model.getServiceRequests();
        const q = this.dashboardFilters.query.trim().toLowerCase();
        const statusFilter = this.dashboardFilters.status;
        const sortBy = this.dashboardFilters.sortBy;

        let rows = all.filter((r) => {
            const statusOk = statusFilter === 'all' ? true : (r.status === statusFilter);
            if (!statusOk) return false;
            if (!q) return true;
            return (
                String(r.id).includes(q) ||
                (r.title || '').toLowerCase().includes(q) ||
                (r.description || '').toLowerCase().includes(q) ||
                (r.status || '').toLowerCase().includes(q)
            );
        });

        rows.sort((a, b) => {
            const da = new Date(a.createdAt).getTime();
            const db = new Date(b.createdAt).getTime();
            if (sortBy === 'date_asc') return da - db;
            if (sortBy === 'status_asc') return (a.status || '').localeCompare(b.status || '');
            if (sortBy === 'status_desc') return (b.status || '').localeCompare(a.status || '');
            return db - da;
        });

        this.preserveInputFocus(() => {
            view.renderWorkerDashboard(rows, this.dashboardFilters);
        });
    },

    handleProfileUpdate(formData) {
        const data = {
            name: formData.get('name'),
            email: formData.get('email')
        };
        model.updateUser(data);
        view.renderToast('Staff profile updated!');
        window.location.hash = '#home';
    }
};

export default controller;
