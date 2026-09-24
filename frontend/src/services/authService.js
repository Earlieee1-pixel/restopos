import api from './api'

// Tanan API calls para sa authentication
const authService = {
  // Mag-login — ipadala ang credentials sa backend
  async login(credentials) {
    return api.post('/login', credentials)
  },

  // Mag-logout — tangtangon ang token sa server
  async logout() {
    return api.post('/logout')
  },

  // Kuha sa imong user info
  async getMe() {
    return api.get('/me')
  },
}

export default authService
