import axios from 'axios';

function getAdminPrefix() {
    return window.CMS_CONFIG?.adminPrefix || 'admin';
}

function getApiPrefix() {
    return `/${getAdminPrefix()}/api`;
}

export async function getContexts() {
    const response = await axios.get(`${getApiPrefix()}/contexts`);
    return response.data;
}

export async function getUserSettingsPage(name, contextId) {
    const response = await axios.get(`${getApiPrefix()}/user-settings/pages/${name}`, {
        params: { context_id: contextId },
    });
    return response.data;
}

export async function updateUserSettingsPage(name, contextId, values) {
    const response = await axios.put(`${getApiPrefix()}/user-settings/pages/${name}`, {
        context_id: contextId,
        values,
    });
    return response.data;
}
