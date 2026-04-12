/**
 * controller.js
 * FrontOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    profileEditMode: false,

    async init() {
        await model.sync();
        this.setupEventListeners();
        this.handleRouting();
        view.renderNavBar(model.getCurrentUser().role);
        
        if (window.SERVER_MESSAGES) {
            if (window.SERVER_MESSAGES.success) {
                view.renderToast(window.SERVER_MESSAGES.success);
            }
            if (window.SERVER_MESSAGES.errors && Object.keys(window.SERVER_MESSAGES.errors).length > 0) {
                const errorStr = Object.values(window.SERVER_MESSAGES.errors).join('\n');
                alert("Errors:\n" + errorStr);
            }
        }
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            const target = e.target;
            const action = target.dataset.action;
            const id = target.dataset.id;

            if (action === 'enroll') {
                this.handleEnrollment(id);
            }
            if (action === 'remove-friend') {
                this.handleFriendRemoval(parseInt(id, 10));
            }
            if (action === 'toggle-profile-edit') {
                this.toggleProfileEdit();
            }
            if (action === 'logout-btn') {
                window.location.href = 'index.php?action=logout';
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', async (e) => {
            if (e.target.id === 'profile-form') {
                return; // Allow native form submission
            }
            e.preventDefault();
            if (e.target.id === 'service-request-form') {
                await this.handleServiceRequest(new FormData(e.target));
            } else if (e.target.id === 'complaint-form') {
                await this.handleComplaintSubmission(new FormData(e.target));
            } else if (e.target.id === 'friend-form') {
                this.handleFriendSubmission(new FormData(e.target));
            }
        });
    },

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();

        if (hash !== '#profile') {
            this.profileEditMode = false;
        }

        switch (hash) {
            case '#home':
                view.renderHome(user);
                break;
            case '#documents':
                view.renderDocuments();
                break;
            case '#forum-posts':
                view.renderForumPosts();
                break;
            case '#transport':
                view.renderTransport();
                break;
            case '#programs':
                view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
                break;
            case '#service-requests':
                view.renderServiceRequestForm();
                break;
            case '#grievances':
                view.renderComplaintForm();
                break;
            case '#profile':
                view.renderProfile(user, this.profileEditMode);
                break;
            case '#request-service':
                view.renderServiceRequestForm();
                break;
            case '#complaints':
                view.renderComplaintForm();
                break;
            default:
                view.renderHome(user);
                break;
        }
    },

    toggleProfileEdit() {
        this.profileEditMode = !this.profileEditMode;
        view.renderProfile(model.getCurrentUser(), this.profileEditMode);
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

    async handleProfileUpdate(formData) {
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            bio: formData.get('bio') || '',
            phoneNumber: formData.get('phoneNumber') || '',
            dateOfBirth: formData.get('dateOfBirth') || ''
        };

        const avatarFile = formData.get('avatar');
        if (avatarFile && avatarFile.size > 0) {
            const avatarDataUrl = await this.readFileAsDataURL(avatarFile);
            model.setAvatar(avatarDataUrl);
        }

        model.updateUser(data);
        this.profileEditMode = false;
        view.renderToast('Profil mis à jour !');
        view.renderProfile(model.getCurrentUser(), this.profileEditMode);
    },

    readFileAsDataURL(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(new Error('Impossible de lire le fichier.'));
            reader.readAsDataURL(file);
        });
    },

    async handleComplaintSubmission(formData) {
        const user = model.getCurrentUser();
        const subject = formData.get('subject');
        const body = formData.get('body');
        await model.addComplaint(subject, body, user.id);
        view.renderToast('Grievance logged in PHP session.');
        window.location.hash = '#home';
    },

    handleFriendSubmission(formData) {
        model.addFriend({
            name: formData.get('name'),
            email: formData.get('email'),
            role: formData.get('role'),
            status: 'Active'
        });
        view.renderToast('Ami ajouté !');
        view.renderFriendsDashboard(model.getFriends());
    },

    handleFriendRemoval(friendId) {
        model.removeFriend(friendId);
        view.renderToast('Ami supprimé.');
        view.renderFriendsDashboard(model.getFriends());
    }
};

export default controller;
