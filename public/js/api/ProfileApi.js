const PROFILE_URL = API_URL + '/profile';

class ProfileApi {
    static async getProfile() {
        return ApiHandler.request(PROFILE_URL);
    }

    static async updateProfile(email, bio) {
        return ApiHandler.request(PROFILE_URL, {
            method: 'PATCH',
            body: JSON.stringify({ email, bio })
        });
    }
}
