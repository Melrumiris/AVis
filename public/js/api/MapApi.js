const MAP_URL = API_URL + '/map';

class MapApi {
    static async getPoints(filters = {}) {
        const params = new URLSearchParams(filters);
        return ApiHandler.request(`${MAP_URL}?${params}`);
    }
}
