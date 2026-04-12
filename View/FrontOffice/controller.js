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
            const id = target.dataset.id;
            const action = target.dataset.action;

            if (action === 'enroll') {
                const user = model.getCurrentUser();
                this.handleEnrollment(user.id, parseInt(id));
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'prog-search') {
                this.handleCatalogFilter();
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.id === 'prog-filter-cat') {
                this.handleCatalogFilter();
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

    async handleEnrollment(userId, programId) {
        const success = await model.addEnrollment(userId, programId);
        if (success) {
            view.renderToast('Enrollment requested (Pending validation).');
            await model.sync(); // Refresh programs with updated enrollment counts
            view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(userId));
        } else {
            view.renderToast('Enrollment failed or full.', 'error');
        }
    },

    handleCatalogFilter() {
        const search = document.getElementById('prog-search')?.value.toLowerCase() || '';
        const category = document.getElementById('prog-filter-cat')?.value || '';
        
        const allPrograms = model.getPrograms();
        const filtered = allPrograms.filter(p => {
            const matchesSearch = p.title.toLowerCase().includes(search);
            const matchesCat = category === '' || p.category === category;
            return matchesSearch && matchesCat;
        });

        const user = model.getCurrentUser();
        view.renderProgramCatalog(filtered, model.getEnrollments(user.id));
        
        // Preserve values
        if(document.getElementById('prog-search')) document.getElementById('prog-search').value = search;
        if(document.getElementById('prog-filter-cat')) document.getElementById('prog-filter-cat').value = category;
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
