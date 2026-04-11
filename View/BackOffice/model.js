/**
 * model.js 
 * Client-side Model for CivicPortal BackOffice
 * Syncs with PHP backend API
 */

const model = {
    state: {
        currentUser: null,
        users: [
            { id: 2, name: 'Alice Worker', role: 'worker', email: 'alice@cityhall.gov' },
            { id: 3, name: 'Admin User', role: 'admin', email: 'admin@cityhall.gov' }
        ],
        serviceRequests: [],
        complaints: [],
        enrollmentsCount: 0,
        programsCount: 3, 
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

    async transportApiCall(action, data = {}) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, ...data })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error("Transport API Error:", error);
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

    async getTickets() {
        const tickets = await this.apiCall('get_tickets');
        return tickets || [];
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
            usersCount: 3,
            programsCount: this.state.programsCount,
            requestsCount: this.state.serviceRequests.length,
            enrollmentsCount: this.state.enrollmentsCount,
            complaintsCount: this.state.complaints.length
        };
    },

    // ============================================
    // TRANSPORT MODULE API CALLS
    // ============================================

    async getTransports() {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list_transports' })
            });
            const result = await response.json();
            return result.success ? result.data : [];
        } catch (e) { console.error(e); return []; }
    },

    async addTransport(data) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add_transport', ...data })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    },

    async deleteTransport(idTransport) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_transport', idTransport })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    },

    async updateTransport(data) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_transport', ...data })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    },

    async getTransport(idTransport) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_transport', idTransport })
            });
            const result = await response.json();
            return result.success ? result.data : null;
        } catch (e) { console.error(e); return null; }
    },

    async getTrajets() {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list_all_trajets' })
            });
            const result = await response.json();
            return result.success ? result.data : [];
        } catch (e) { console.error(e); return []; }
    },

    async addTrajet(data) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add_trajet', ...data })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    },

    async deleteTrajet(idTrajet) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_trajet', idTrajet })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    },

    async getAllTickets() {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'list_tickets' })
            });
            const result = await response.json();
            return result.success ? result.data : [];
        } catch (e) { console.error(e); return []; }
    },

    async cancelTicket(idTicket) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'cancel_ticket', idTicket })
            });
            return await response.json();
        } catch (e) { console.error(e); return { success: false }; }
    }
};

export default model;
