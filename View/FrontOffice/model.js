/**
 * model.js 
 * Client-side Model for CivicPortal FrontOffice
 * Syncs with PHP backend API
 */

const model = {
    state: {
        currentUser: { id: 1, name: 'John Citizen', role: 'citizen', email: 'john@example.com' },
        programs: [],
        serviceRequests: [],
        enrollments: [],
        complaints: []
    },

    async apiCall(action, data = {}) {
        try {
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, data })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error("API Error:", error);
            return null;
        }
    },

    async sync() {
        const requests = await this.apiCall('get_requests');
        if (requests) this.state.serviceRequests = requests;

        const programs = await this.apiCall('get_programs');
        if (programs) this.state.programs = programs;

        const enrollments = await this.apiCall('get_enrollments', { userId: this.state.currentUser.id });
        if (enrollments) this.state.enrollments = enrollments;
    },

    getPrograms() {
        return this.state.programs;
    },

    getServiceRequests() {
        // Filter requests for just this user (simulate isolation in UI)
        return this.state.serviceRequests.filter(r => r.userId === this.state.currentUser.id);
    },

    getEnrollments(userId) {
        return this.state.enrollments.filter(e => e.userId === userId);
    },

    async addServiceRequest(request) {
        const response = await this.apiCall('add_request', request);
        if (response) {
            this.state.serviceRequests.push(response);
            return response;
        }
    },

    async addEnrollment(userId, programId) {
        const result = await this.apiCall('enroll_user', { userId, programId });
        if (result) {
            this.state.enrollments.push({ user_id: userId, program_id: programId });
            return true;
        }
        return false;
    },

    getCurrentUser() {
        return this.state.currentUser;
    },

    async addComplaint(subject, body, userId) {
        const response = await this.apiCall('add_complaint', { subject, body, userId });
        if (response) {
            this.state.complaints.push(response);
            return response;
        }
    },

    updateUser(data) {
        this.state.currentUser.name = data.name;
        this.state.currentUser.email = data.email;
    },

    deleteUser() {
        this.state.currentUser = null;
    }
};

export default model;
