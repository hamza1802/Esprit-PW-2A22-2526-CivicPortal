/**
 * controller.js
 * BackOffice Event Handler — Next‑Level Parks & Recreation
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
        document.addEventListener('click', (e) => {
            // Walk up the DOM to find the nearest element with a data-action
            const actionEl = e.target.closest('[data-action]');
            if (!actionEl) {
                // Check for AI button separately (uses id, not data-action)
                if (e.target.id === 'btn-generate-image') {
                    this.handleImageGeneration(e.target);
                }
                return;
            }

            const action = actionEl.dataset.action;
            const id = actionEl.dataset.id;

            if (action === 'validate') {
                this.handleStatusUpdate(id, 'validated');
            } else if (action === 'reject') {
                this.handleStatusUpdate(id, 'rejected');
            } else if (action === 'new-program') {
                view.renderProgramForm();
            } else if (action === 'edit-program') {
                const program = model.getProgram(id);
                view.renderProgramForm(program);
            } else if (action === 'delete-program') {
                if (confirm('Are you sure you want to delete this program?')) {
                    this.handleProgramDelete(id);
                }
            } else if (action === 'view-program') {
                window.location.hash = `#program/${id}`;
            } else if (action === 'confirm-enroll') {
                const progId = actionEl.dataset.programId;
                this.handleEnrollmentUpdate(id, 'confirmed', progId);
            } else if (action === 'cancel-enroll') {
                const progId = actionEl.dataset.programId;
                this.handleEnrollmentUpdate(id, 'cancelled', progId);
            }
        });

        window.addEventListener('hashchange', () => {
            this.handleRouting();
        });

        document.addEventListener('submit', (e) => {
            e.preventDefault();
            if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            } else if (e.target.id === 'program-form') {
                this.handleProgramSave(new FormData(e.target));
            }
        });
    },

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();

        // Handle parameterized routes like #program/123
        if (hash.startsWith('#program/')) {
            const programId = hash.split('/')[1];
            await this.showProgramDetail(programId);
            return;
        }

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
            case '#manage-programs':
                if (user.role === 'admin' || user.role === 'worker') {
                    await model.sync(); // Refresh data
                    view.renderProgramsManager(model.getPrograms(), user.role);
                } else {
                    window.location.hash = '#home';
                }
                break;
            default:
                view.renderHome(user);
                break;
        }
    },

    async showProgramDetail(programId) {
        const user = model.getCurrentUser();
        const program = await model.getProgramDetail(programId);
        const enrollments = await model.getEnrollmentsByProgram(programId);
        if (program) {
            view.renderProgramDetail(program, enrollments || [], user.role);
        } else {
            view.renderToast('Program not found.', 'error');
            window.location.hash = '#manage-programs';
        }
    },

    async handleRoleChange(role, triggerRouting = true) {
        model.setCurrentUser(role);
        
        // Fetch enrollment counts for nav badge
        const counts = await model.getEnrollmentCounts();
        view.renderNavBar(role, counts);
        
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
    },

    async handleImageGeneration(btnElement) {
        const title = document.getElementById('prog-title').value.trim();
        const desc = document.getElementById('prog-desc').value.trim();
        const category = document.getElementById('prog-category').value;
        
        if (!title || !desc) {
            view.renderToast('Please enter both Title and Description before generating.', 'error');
            return;
        }

        const originalText = btnElement.textContent;
        btnElement.textContent = '⏳ GENERATING...';
        btnElement.disabled = true;

        try {
            const prompt = `A professional, high-quality photograph representing a community program. Theme: ${category}. Title: ${title}. ${desc}. Lighting is bright and inviting. No text in the image.`;
            
            const imgElement = await puter.ai.txt2img(prompt);
            
            const response = await fetch(imgElement.src);
            const blob = await response.blob();
            const file = new File([blob], 'generated_cover.jpg', { type: 'image/jpeg' });
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('prog-image').files = dataTransfer.files;
            
            const previewDiv = document.getElementById('prog-image-preview');
            previewDiv.innerHTML = `
                <img src="${imgElement.src}" style="width: 100%; max-width: 400px; height: 200px; object-fit: cover; border: var(--border-main);" />
                <p style="font-size: 0.8rem; color: var(--success); margin-top: 0.5rem; font-weight: bold;">AI Image generated and attached!</p>
            `;
            
            view.renderToast('AI Image generated successfully!');
        } catch (error) {
            console.error("AI Generation Error:", error);
            view.renderToast('Failed to generate image. Try again.', 'error');
        } finally {
            btnElement.textContent = originalText;
            btnElement.disabled = false;
        }
    },

    async handleProgramSave(formData) {
        const success = await model.saveProgram(formData);
        if (success) {
            view.renderToast(formData.get('id') ? 'Program updated!' : 'New program created!');
            window.location.hash = '#manage-programs';
        } else {
            view.renderToast('Failed to save program.', 'error');
        }
    },

    async handleProgramDelete(id) {
        const success = await model.deleteProgram(id);
        if (success) {
            view.renderToast('Program deleted.');
            window.location.hash = '#manage-programs';
        } else {
            view.renderToast('Failed to delete program.', 'error');
        }
    },

    async handleEnrollmentUpdate(enrollmentId, status, programId) {
        const success = await model.updateEnrollmentStatus(enrollmentId, status);
        if (success) {
            view.renderToast(`Enrollment ${status}.`);
            // Refresh the program detail view in-place
            await this.showProgramDetail(programId);
            // Also refresh nav badge
            const counts = await model.getEnrollmentCounts();
            const user = model.getCurrentUser();
            view.renderNavBar(user.role, counts);
        } else {
            view.renderToast('Failed to update enrollment.', 'error');
        }
    }
};

export default controller;
