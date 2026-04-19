/**
 * model.js 
 * Client-side Model for CivicPortal FrontOffice
 * Syncs with PHP backend API
 */

const model = {
    state: {
        currentUser: { id: 1, name: 'John Citizen', role: 'citizen', email: 'john@example.com' },
        programs: [
            { id: 101, title: 'Summer Pottery Workshop', category: 'Arts', description: 'Learn basic pottery techniques for all ages.', image: 'pottery.jpg' },
            { id: 102, title: 'Youth Swimming Program', category: 'Sports', description: 'Daily swimming lessons at the Municipal Pool.', image: 'swimming.jpg' },
            { id: 103, title: 'Community Gardening', category: 'Environment', description: 'Join our local group in the North Park garden.', image: 'gardening.jpg' }
        ],
        serviceRequests: [],
        enrollments: [],
        complaints: [],
        transportTypes: [],
        myTickets: []
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

    async transportApi(action, data = {}) {
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
            return [];
        }
    },

    async sync() {
        const requests = await this.apiCall('get_requests');
        if (requests) this.state.serviceRequests = requests;
        
        // Sync transport types for dynamic rendering
        const types = await this.transportApi('list_transport_types');
        if (types) this.state.transportTypes = types;
    },

    getPrograms() {
        return this.state.programs;
    },

    getTransportTypes() {
        return this.state.transportTypes;
    },

    getServiceRequests() {
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

    addEnrollment(userId, programId) {
        const exists = this.state.enrollments.find(e => e.userId === userId && e.programId === programId);
        if (!exists) {
            this.state.enrollments.push({ userId, programId });
        }
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
    },

    async getTrajetsByTypeAndSort(type, sortBy, order) {
        return await this.transportApi('list_trajets', { type, sortBy, order });
    },

    async bookTicket(idTrajet, citizenName, idUser) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'book_ticket', idTrajet, citizenName, idUser })
            });
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            return { success: false, error: 'Network error booking ticket.' };
        }
    },

    async getMyTickets() {
        return await this.transportApi('list_tickets_enriched');
    },

    async cancelTicket(idTicket) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'cancel_ticket', idTicket })
            });
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            return { success: false, error: 'Network error cancelling ticket.' };
        }
    }
};

export default model;
