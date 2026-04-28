/**
 * controller.js
 * BackOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    async init() {
        console.log("Controller: Synchronizing model...");
        await model.sync();
        this.setupEventListeners();
        console.log("Controller: Setting default role to admin");
        this.handleRoleChange('admin', false);
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'validate') {
                this.handleStatusUpdate(id, 'validated');
            } else if (action === 'reject') {
                this.handleStatusUpdate(id, 'rejected');
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', (e) => {
            if (e.target.id === 'profile-form') {
                e.preventDefault();
                this.handleProfileUpdate(new FormData(e.target));
            }
        });
    },

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();
        console.log(`Controller: Routing to ${hash} for user:`, user);

        if (!user) {
            console.warn("Controller: No current user set, rendering empty home");
            view.renderHome(null);
            return;
        }

        switch (hash) {
            case '#home':
                view.renderHome(user);
                break;
            case '#worker-dashboard':
                view.renderWorkerDashboard(model.getServiceRequests());
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
        console.log(`Controller: Role changing to ${role}`);
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
        await model.updateRequestStatus(parseInt(requestId), status);
        view.renderToast(`Request ${status} successfully.`);
        view.renderWorkerDashboard(model.getServiceRequests());
    },

    handleProfileUpdate(formData) {
        const data = {
            name: formData.get('name'),
            email: formData.get('email')
        };
        model.updateUser(data);
        view.renderToast('Staff profile updated!');
        view.renderProfile(model.getCurrentUser()); // Stay on profile or go home
    }
};

export default controller;