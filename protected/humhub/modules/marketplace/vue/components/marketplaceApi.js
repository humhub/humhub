import { apiUrl, client } from '@humhub/vue';

/**
 * The marketplace endpoints (`docs/api/src/marketplace.yaml`) as the island calls them. The
 * module list itself is read by `CardDirectory`.
 *
 * @since 1.20
 */

const moduleId = (id) => encodeURIComponent(id);

/**
 * The JSON body of a successful request. `humhub.client` resolves its `Response` wrapper
 * (`{status, xhr, response, ...body}`); the island works with the plain payload.
 */
export const payload = (response) => (response && response.response && typeof response.response === 'object' ? response.response : response);

export const installModule = (id) => client.post(apiUrl(`marketplace/module/${moduleId(id)}/install`)).then(payload);

export const updateModule = (id) => client.post(apiUrl(`marketplace/module/${moduleId(id)}/update`)).then(payload);

export const enableModule = (id) => client.post(apiUrl(`module/${moduleId(id)}/enable`)).then(payload);

export const fetchModules = (params) => client.get(apiUrl('marketplace/module', { pageSize: 100, ...params }))
    .then((response) => payload(response).results || []);

export const fetchCoreVersion = () => client.get(apiUrl('marketplace/core-version')).then(payload);

export const saveSettings = (data) => client.patch(apiUrl('marketplace/settings'), { data })
    .then(payload)
    .then((settings) => ({
        includeBetaUpdates: settings.includeBetaUpdates === true,
        includeCommunityModules: settings.includeCommunityModules === true,
    }));

export const registerLicenceKey = (licenceKey) => client.post(apiUrl('marketplace/licence-key'), { data: { licenceKey } }).then(payload);

/**
 * The text to show for a failed request: the first validation message of a `422`, else the
 * message of Yii's error body, else `fallback`.
 */
export const errorMessage = (response, fallback) => {
    const errors = response && response.errors;
    if (errors && typeof errors === 'object') {
        const first = Object.values(errors)[0];
        if (Array.isArray(first) && first.length) {
            return String(first[0]);
        }
    }
    if (response && typeof response.message === 'string' && response.message !== '') {
        return response.message;
    }
    return fallback;
};
