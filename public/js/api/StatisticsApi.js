const STATISTICS_URL = API_URL + '/statistics';

class StatisticsApi {
    static async getData(sdate = '', fdate = '', severity = 'ALL', region = 'ALL', group_by = 'severity') {
        const params = new URLSearchParams({ sdate, fdate, severity, region, group_by });
        return ApiHandler.request(`${STATISTICS_URL}?${params}`);
    }
}
