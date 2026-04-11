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
        document.addEventListener('click', (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'enroll') {
                this.handleEnrollment(id);
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (e.target.id === 'service-request-form') {
                await this.handleServiceRequest(new FormData(e.target));
            } else if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            } else if (e.target.id === 'complaint-form') {
                await this.handleComplaintSubmission(new FormData(e.target));
            } else if (e.target.id === 'sort-transport-form') {
                const type = e.target.dataset.type;
                const formData = new FormData(e.target);
                const sort = formData.get('sort');
                const order = formData.get('order');
                window.location.hash = `#transport_list?type=${type}&sort=${sort}&order=${order}`;
            } else if (e.target.classList.contains('book-transport-form')) {
                const idTrajet = e.target.dataset.id;
                const currentUser = model.getCurrentUser();
                await this.handleTicketBooking(idTrajet, currentUser.name, currentUser.id);
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
            case '#programs':
                view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
                break;
            case '#request-service':
                view.renderServiceRequestForm();
                break;
            case '#profile':
                view.renderProfile(user);
                break;
            case '#complaints':
                view.renderComplaintForm();
                break;
            case '#transport':
                view.renderTransport();
                break;
            default:
                if (hash.startsWith('#transport_list')) {
                    const urlParams = new URLSearchParams(hash.split('?')[1]);
                    const type = urlParams.get('type') || 'Bus';
                    const sortBy = urlParams.get('sort') || 'departure';
                    const order = urlParams.get('order') || 'ASC';
                    
                    // Fetch from backend API
                    const trajets = await model.getTrajetsByTypeAndSort(type, sortBy, order);
                    view.renderTransportList(type, trajets, sortBy, order);
                } else {
                    view.renderHome(user);
                }
                break;
        }
    },

    handleEnrollment(programId) {
        const user = model.getCurrentUser();
        model.addEnrollment(user.id, parseInt(programId));
        view.renderToast('Enrolled in program!');
        view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
    },

    async handleServiceRequest(formData) {
        const user = model.getCurrentUser();
        const type = formData.get('type');
        await model.addServiceRequest({ type, userId: user.id });
        view.renderToast('Service Request Submitted Successfully!');
        window.location.hash = '#home';
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
    },

    async handleTicketBooking(idTrajet, citizenName, idUser) {
        const result = await model.bookTicket(idTrajet, citizenName, idUser);
        if (result && result.success) {
            view.renderToast('Ticket successfully booked!');
            this.handleRouting(); // Refresh the list to reflect occupancy
        } else {
            view.renderToast(result && result.error ? result.error : 'Failed to book ticket', 'danger');
        }
    }
};

export default controller;
