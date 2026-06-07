class AuthApi {
    static async authenticate(endpoint, credentials) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(credentials)
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.error || 'Authentication failed.');
        }
        ApiHandler.setAccessToken(result.data.token);
        return result;
    }
}