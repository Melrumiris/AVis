const ACCIDENT_URL = API_URL + '/admin/accident';
const ACCIDENT_FILE_URL = ACCIDENT_URL + '/file';

class AccidentApi {
    static async insertManual(data_ora, severitate, latitudine, longitudine) {
        return ApiHandler.request(ACCIDENT_URL, {
            method: 'POST',
            body: JSON.stringify({ data_ora, severitate, latitudine, longitudine })
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
}
