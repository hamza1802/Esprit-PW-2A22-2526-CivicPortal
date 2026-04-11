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
        // Placeholder stats properties
        enrollmentsCount: 0,
        programsCount: 3, 
    },

    async apiCall(action, data = {}) {
        try {
            const response = await fetch('../api/index.php', {
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
        
        const complaints = await this.apiCall('get_complaints');
        if (complaints) this.state.complaints = complaints;
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
