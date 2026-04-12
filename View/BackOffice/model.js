/**
 * model.js 
 * Client-side Model for CivicPortal BackOffice
 * Syncs with PHP backend API
 */

const model = {
    // Current state mirror
    state: {
        currentUser: null,
        users: [
            { id: 2, name: 'Alice Worker', role: 'worker', email: 'alice@cityhall.gov' },
            { id: 3, name: 'Admin User', role: 'admin', email: 'admin@cityhall.gov' }
        ],
        serviceRequests: [],
        complaints: [],
        programs: [],
        stats: null
    },

    async apiCall(action, data = {}) {
        try {
            let options = {
                method: 'POST',
                body: null
            };

            // Handle Multipart (File uploads)
            if (data instanceof FormData) {
                // Browser sets boundary automatically for FormData
                data.append('action', action);
                options.body = data;
            } else {
                // Standard JSON
                options.headers = { 'Content-Type': 'application/json' };
                options.body = JSON.stringify({ action, data });
            }

            const response = await fetch('../../Verification.php', options);
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
        
        const complaints = await this.apiCall('get_complaints');
        if (complaints) this.state.complaints = complaints;

        const programs = await this.apiCall('get_programs');
        if (programs) this.state.programs = programs;
    },

    getPrograms() {
        return this.state.programs;
    },

    getProgram(id) {
        return this.state.programs.find(p => p.id == id);
    },

    async saveProgram(data) {
        const id = (data instanceof FormData) ? data.get('id') : data.id;
        const action = id ? 'update_program' : 'add_program';
        const result = await this.apiCall(action, data);
        if (result) {
            await this.sync(); // Refresh list after change
            return true;
        }
        return false;
    },

    async deleteProgram(id) {
        const result = await this.apiCall('delete_program', { id });
        if (result) {
            await this.sync();
            return true;
        }
        return false;
    },

    async getPendingEnrollments() {
        return await this.apiCall('get_pending_enrollments');
    },

    async getEnrollmentsByProgram(programId) {
        return await this.apiCall('get_enrollments_by_program', { programId });
    },

    async getProgramDetail(id) {
        return await this.apiCall('get_program_detail', { id });
    },

    async getEnrollmentCounts() {
        return await this.apiCall('get_enrollment_counts');
    },

    async updateEnrollmentStatus(id, status) {
        const result = await this.apiCall('update_enrollment_status', { id, status });
        return !!result;
    },

    getServiceRequests() {
        return this.state.serviceRequests;
    },

    async updateRequestStatus(requestId, status) {
        const response = await this.apiCall('update_status', { id: requestId, status });
        if (response) {
            const request = this.state.serviceRequests.find(r => r.id === requestId);
            if (request) request.status = status;
        }
    },

    setCurrentUser(role) {
        this.state.currentUser = this.state.users.find(u => u.role === role);
    },

    getCurrentUser() {
        return this.state.currentUser;
    },

    getComplaints() {
        return this.state.complaints;
    },

    updateUser(data) {
        if(this.state.currentUser){
            this.state.currentUser.name = data.name;
            this.state.currentUser.email = data.email;
        }
    },

    async getStats() {
        const stats = await this.apiCall('get_stats');
        return stats || {
            usersCount: 3, // Total simulation
            programsCount: this.state.programsCount,
            requestsCount: this.state.serviceRequests.length,
            enrollmentsCount: this.state.enrollmentsCount,
            complaintsCount: this.state.complaints.length
        };
    }
};

export default model;
