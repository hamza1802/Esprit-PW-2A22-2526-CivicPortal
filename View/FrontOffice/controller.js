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
            default:
                view.renderHome(user);
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
    }
};

export default controller;
