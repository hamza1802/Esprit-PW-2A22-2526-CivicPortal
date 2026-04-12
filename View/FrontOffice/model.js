/**
 * model.js 
 * Client-side Model for CivicPortal FrontOffice
 * Syncs with PHP backend API
 */

const model = {
    state: {
        currentUser: window.SERVER_USER ?? { id: 1, name: 'John Citizen', role: 'citizen', email: 'john@example.com', avatar: null, bio: '', phoneNumber: '', dateOfBirth: '' },
        programs: [
            { id: 101, title: 'Summer Pottery Workshop', category: 'Arts', description: 'Learn basic pottery techniques for all ages.', image: 'pottery.jpg' },
            { id: 102, title: 'Youth Swimming Program', category: 'Sports', description: 'Daily swimming lessons at the Municipal Pool.', image: 'swimming.jpg' },
            { id: 103, title: 'Community Gardening', category: 'Environment', description: 'Join our local group in the North Park garden.', image: 'gardening.jpg' }
        ],
        serviceRequests: [],
        enrollments: [],
        complaints: [],
        friends: [
            { id: 201, name: 'Julie Martin', role: 'citizen', email: 'julie.m@example.com', status: 'Active' },
            { id: 202, name: 'Olivier Dupont', role: 'citizen', email: 'olivier.d@example.com', status: 'Pending' },
            { id: 203, name: 'Leila Haddad', role: 'agent', email: 'leila.h@example.com', status: 'Active' }
        ]
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
        
        // Front office users generally don't get all complaints, but let's sync to emulate the backend state if needed.
        // Even better, avoid loading them if unneeded.
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

    getFriends() {
        return this.state.friends;
    },

    addFriend(friendData) {
        const nextId = this.state.friends.length ? Math.max(...this.state.friends.map(f => f.id)) + 1 : 1;
        this.state.friends.push({
            id: nextId,
            name: friendData.name,
            email: friendData.email,
            role: friendData.role || 'citizen',
            status: friendData.status || 'Active'
        });
    },

    removeFriend(friendId) {
        this.state.friends = this.state.friends.filter(f => f.id !== friendId);
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

    setAvatar(avatarDataUrl) {
        this.state.currentUser.avatar = avatarDataUrl;
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
        this.state.currentUser.bio = data.bio || '';
        this.state.currentUser.phoneNumber = data.phoneNumber || '';
        this.state.currentUser.dateOfBirth = data.dateOfBirth || '';
    },

    deleteUser() {
        this.state.currentUser = null;
    }
};

export default model;
