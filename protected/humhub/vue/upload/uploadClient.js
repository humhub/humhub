/**
 * The single place that talks to the file API for `UploadField.vue` — internal building
 * block of the upload field (a subdirectory of `vue/`, so `vue.build.mjs`'s
 * auto-registration scan skips it; see `form/formContext.js`'s docblock on that).
 *
 * Endpoint contract (see `docs/develop/concept-api.md` and
 * `humhub\modules\file\controllers\api\FileController`): one multipart POST carries the
 * whole batch and answers per file —
 * `{results: [<file>, …], errors: [{fileName, messages}]}` — or `422 {errors: {files: […]}}`
 * when the request carried no file at all.
 *
 * @since 1.20
 */
import { apiUrl, client } from '@humhub/vue';

/**
 * @param {File[]} files the browser File objects to upload
 * @param {(percent: number) => void} [onProgress] called with 0..100 while the body uploads
 * @returns {Promise} resolves with the client Response, rejects with it on an HTTP error
 */
export function uploadFiles(files, onProgress) {
    const formData = new FormData();
    files.forEach((file) => formData.append('files[]', file));

    return client.post(apiUrl('file'), {
        data: formData,
        // Hand the FormData to the browser untouched: jQuery must neither serialize it nor
        // set a Content-Type, or the multipart boundary is lost.
        processData: false,
        contentType: false,
        dataType: 'json',
        // The only reason this goes through a custom xhr factory: upload progress is an
        // XHR-level event jQuery does not surface. Everything else (CSRF header prefilter,
        // Response wrapping, error handling) stays with the platform client.
        xhr: () => {
            const xhr = jQuery.ajaxSettings.xhr();

            if (onProgress && xhr.upload) {
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable && event.total > 0) {
                        onProgress(Math.round((event.loaded / event.total) * 100));
                    }
                });
            }

            return xhr;
        },
    });
}

/**
 * The API's own file shape, as returned by `results[]`.
 *
 * @typedef {{
 *   id: number, guid: string, fileName: string, mimeType: string, size: number,
 *   mimeIcon: string, url: string, previewUrl: ?string
 * }} UploadedFile
 */
