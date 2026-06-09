const STATISTICS_URL = API_URL + '/statistics';

class StatisticsApi {
    static async getData(filters = {}, group_by = 'severity') {
        const params = new URLSearchParams({ ...filters, group_by });
        return ApiHandler.request(`${STATISTICS_URL}?${params}`);
    }
}
