const MAP_URL = API_URL + '/map';

class MapApi {
    static async getPoints(sdate = '', fdate = '') {
        const params = new URLSearchParams({ sdate, fdate });
        return ApiHandler.request(`${MAP_URL}?${params}`);
    }
}
