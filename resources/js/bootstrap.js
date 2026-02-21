import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(response => response, async error => {
    if (error.status == 419 && ! error.config.skipInterceptor) {
        // Refresh our session token
        await axios.get('/csrf-token');

        // Return a new request using the original request's configuration
        error.config.skipInterceptor = true;
        return axios(error.response.config);
    }

    return Promise.reject(error.response);
})
