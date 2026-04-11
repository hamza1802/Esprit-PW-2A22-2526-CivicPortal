/**
 * controller.js
 * BackOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRoleChange('worker', false); 
    },

    setupEventListeners() {
        document.addEventListener('click', async (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'validate') {
                this.handleStatusUpdate(id, 'validated');
            } else if (action === 'reject') {
                this.handleStatusUpdate(id, 'rejected');
            } else if (action === 'delete-transport') {
                if (confirm('Are you sure you want to delete this vehicle?')) {
                    const result = await model.deleteTransport(id);
                    if (result && result.success) {
                        view.renderToast('Vehicle deleted.');
                        this.handleRouting();
                    } else {
                        view.renderToast('Failed to delete vehicle.', 'danger');
                    }
                }
            } else if (action === 'edit-transport') {
                window.location.hash = `#edit-transport?id=${id}`;
            } else if (action === 'delete-trajet') {
                if (confirm('Delete this trajet and all linked tickets?')) {
                    const result = await model.deleteTrajet(id);
                    if (result && result.success) {
                        view.renderToast('Trajet deleted.');
                        this.handleRouting();
                    } else {
                        view.renderToast('Failed to delete trajet.', 'danger');
                    }
                }
            } else if (action === 'cancel-ticket') {
                if (confirm('Cancel this ticket?')) {
                    const result = await model.cancelTicket(id);
                    if (result && result.success) {
                        view.renderToast('Ticket cancelled.');
                        this.handleRouting();
                    } else {
                        view.renderToast('Failed to cancel ticket.', 'danger');
                    }
                }
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            } else if (e.target.id === 'add-transport-form') {
                const fd = new FormData(e.target);
                const result = await model.addTransport({
                    name: fd.get('name'),
                    type: fd.get('type'),
                    capacity: parseInt(fd.get('capacity')),
                    status: fd.get('status')
                });
                if (result && result.success) {
                    view.renderToast('Vehicle added successfully!');
                    window.location.hash = '#transports';
                } else {
                    view.renderToast('Failed to add vehicle.', 'danger');
                }
            } else if (e.target.id === 'edit-transport-form') {
                const fd = new FormData(e.target);
                const idTransport = e.target.dataset.id;
                const result = await model.updateTransport({
                    idTransport,
                    name: fd.get('name'),
                    type: fd.get('type'),
                    capacity: parseInt(fd.get('capacity')),
                    status: fd.get('status')
                });
                if (result && result.success) {
                    view.renderToast('Vehicle updated successfully!');
                    window.location.hash = '#transports';
                } else {
                    view.renderToast('Failed to update vehicle.', 'danger');
                }
            } else if (e.target.id === 'add-trajet-form') {
                const fd = new FormData(e.target);
                const result = await model.addTrajet({
                    departure: fd.get('departure'),
                    destination: fd.get('destination'),
                    idTransport: parseInt(fd.get('idTransport')),
                    departureTime: fd.get('departureTime'),
                    price: parseFloat(fd.get('price'))
                });
                if (result && result.success) {
                    view.renderToast('Trajet added successfully!');
                    window.location.hash = '#trajets';
                } else {
                    view.renderToast('Failed to add trajet.', 'danger');
                }
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
            case '#transports': {
                const transports = await model.getTransports();
                view.renderTransports(transports);
                break;
            }
            case '#add-transport':
                view.renderAddTransport();
                break;
            case '#trajets': {
                const trajets = await model.getTrajets();
                view.renderTrajets(trajets);
                break;
            }
            case '#add-trajet': {
                const transports = await model.getTransports();
                view.renderAddTrajet(transports);
                break;
            }
            case '#tickets': {
                const tickets = await model.getAllTickets();
                view.renderTickets(tickets);
                break;
            }
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
                if (hash.startsWith('#edit-transport')) {
                    const urlParams = new URLSearchParams(hash.split('?')[1]);
                    const id = urlParams.get('id');
                    const transport = await model.getTransport(id);
                    if (transport) {
                        view.renderEditTransport(transport);
                    } else {
                        view.renderToast('Vehicle not found.', 'danger');
                        window.location.hash = '#transports';
                    }
                } else {
                    view.renderHome(user);
                }
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
