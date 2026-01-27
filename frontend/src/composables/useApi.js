import axios from 'axios'

const api = axios.create({
    baseURL: 'http://localhost:8000/api/v1',
    // withCredentials: true, // Not needed for Bearer token, can cause CORS issues if wildcard is used
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
})

// Request interceptor to add the auth token header to every request
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

export default api
