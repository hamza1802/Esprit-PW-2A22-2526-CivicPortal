/**
 * controller.js
 * BackOffice Event Handler
 */

import model from '../models/model.js';
import view from '../views/view.js';

const controller = {
    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRoleChange('worker', false); 
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
            e.preventDefault();
            if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
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
                    view.renderWorkerDashboard(model.getServiceRequests());
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
        window.location.hash = '#home';
    }
};

export default controller;
