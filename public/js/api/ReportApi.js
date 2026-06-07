const REPORT_URL = API_URL + '/report';

class ReportApi {
    static async getData(sdate = '', fdate = '') {
        const params = new URLSearchParams({ sdate, fdate });
        return ApiHandler.request(`${REPORT_URL}?${params}`);
    }
}
