const API_URL = '/api/v0';
const REFRESH_URL = API_URL + '/auth/refresh';

const ApiHandler = (() => {
    let accessToken = null;

    let isRefreshing = false;
    let refreshSubscribers = [];

    const onTokenRefreshed = (token) => {
        refreshSubscribers.forEach(callback => callback(token));
        refreshSubscribers = [];
    };

    const executeRequest = async (url, options = {}) => {

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        };

        Object.keys(headers).forEach(k => { if (headers[k] === null) delete headers[k]; });

        if (accessToken) {
            headers['Authorization'] = `Bearer ${accessToken}`;
        }

        const config = { ...options, headers };
        let response = await fetch(url, config);

        if (response.status === 401) {
            if (!isRefreshing) {
                isRefreshing = true;

                try {
                    console.log("Access token expired. Retrieving new token...");

                    const refreshResponse = await fetch(REFRESH_URL, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    });

                    const refreshData = await refreshResponse.json();

                    if (!refreshData.success) {
                        throw new Error('Refresh token expired or invalid.');
                    }

                    accessToken = refreshData.data.accessToken;
                    isRefreshing = false;

                    onTokenRefreshed(accessToken);

                    headers['Authorization'] = `Bearer ${accessToken}`;
                    response = await fetch(url, { ...config, headers });

                } catch (error) {
                    isRefreshing = false;
                    refreshSubscribers = [];
                    accessToken = null;

                    console.error("Authentication failed");
                    window.location.href = '/login';
                    return Promise.reject(error);
                }
            } else {
                return new Promise(resolve => {
                    refreshSubscribers.push((newToken) => {
                        headers['Authorization'] = `Bearer ${newToken}`;
                        resolve(fetch(url, { ...config, headers }));
                    });
                });
            }
        }

        return response;
    };

    return {
        /**
         * Used for all frontend API calls.
         */
        request: async (url, options = {}) => {
            try {
                const response = await executeRequest(url, options);
                return await response.json();
            } catch (error) {
                throw error;
            }
        },

        /**
         * Called after a successful login/register to load the initial token.
         */
        setAccessToken: (token) => {
            accessToken = token;
        },

        /**
         * Clears the access token from local memory
         */
        clearAuth: () => {
            accessToken = null;
        }
    };
})();