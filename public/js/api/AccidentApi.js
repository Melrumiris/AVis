const ACCIDENT_URL = API_URL + '/admin/accident';
const ACCIDENT_FILE_URL = ACCIDENT_URL + '/file';

class AccidentApi {
    static async insertManual(data) {
        return ApiHandler.request(ACCIDENT_URL, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async uploadFile(file) {
        const formData = new FormData();
        formData.append('csv_file', file);
        return ApiHandler.request(ACCIDENT_FILE_URL, {
            method: 'POST',
            headers: { 'Content-Type': null },
            body: formData
        });
    }

    static async replaceFile(file, startTime, endTime) {
        const formData = new FormData();
        formData.append('csv_file', file);
        const encodedStart = encodeURIComponent(startTime);
        const encodedEnd   = encodeURIComponent(endTime);
        return ApiHandler.request(`${ACCIDENT_FILE_URL}/${encodedStart}/${encodedEnd}`, {
            method: 'PUT',
            headers: { 'Content-Type': null },
            body: formData
        });
    }
}
