import axios from 'axios'

const getMetaContent = (name, fallback = '') => {
    if (typeof document === 'undefined') {
        return fallback
    }

    return document.querySelector(`meta[name="${name}"]`)?.content || fallback
}

export const getCsrfToken = () => getMetaContent('csrf-token')

export const getCsrfParam = () => getMetaContent('csrf-param', '_csrf')

export const getDefaultHeaders = (headers = {}) => {
    const result = {
        ...headers,
        'X-Requested-With': 'XMLHttpRequest',
    }

    const csrfToken = getCsrfToken()
    if (csrfToken) {
        result['X-CSRF-Token'] = csrfToken
    }

    return result
}

const http = axios.create({
    withCredentials: true,
})

http.interceptors.request.use((config) => {
    config.withCredentials = true
    config.headers = getDefaultHeaders(config.headers || {})
    return config
})

export default http
