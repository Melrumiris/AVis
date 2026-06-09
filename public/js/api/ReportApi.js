const REPORT_URL = API_URL + '/report';

class ReportApi {
    static async getData(filters = {}) {
        const params = new URLSearchParams(filters);
        return ApiHandler.request(`${REPORT_URL}?${params}`);
    }
}
