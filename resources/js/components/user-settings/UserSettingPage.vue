<template>
    <div class="admin-page">
        <div class="page-header no-bottom">
            <h1>{{ page.label || page.name }}</h1>
            <div class="context-selector">
                <label for="context-select">{{ $t('user_settings_context') }}</label>
                <select id="context-select" v-model="selectedContextId" @change="loadPage" class="form-control">
                    <option v-for="context in contexts" :key="context.id" :value="context.id">
                        {{ context.name }}
                    </option>
                </select>
            </div>
        </div>

        <div v-if="schemaTabs.length > 0" class="tabs-container">
            <div class="tabs-nav">
                <button
                    v-for="tab in schemaTabs"
                    :key="tab.tab_key"
                    type="button"
                    class="tab-button"
                    :class="{ active: tab.tab_key === activeSchemaTab?.tab_key }"
                    @click="activeTabKey = tab.tab_key"
                >
                    {{ tab.name }}
                </button>
            </div>

            <div v-if="activeSchemaTab" class="tab-panel">
                <div v-if="activeTabFields.length > 0" class="form-section">
                    <div
                        v-for="(field, index) in activeTabFields"
                        :key="`${field.key}-${index}`"
                        class="form-group"
                    >
                        <label :for="`field-${field.key}`">
                            {{ field.name }}
                            <span v-if="field.is_required" class="required">*</span>
                        </label>
                        <component
                            v-if="fieldEditorComponent(field)"
                            :is="fieldEditorComponent(field)"
                            :key="`editor-${field.id || field.key}-${field.type}-${selectedContextId}`"
                            v-model="formValues[field.key]"
                            v-bind="getFieldEditorProps(field)"
                        />
                        <input
                            v-else
                            :id="`field-${field.key}`"
                            v-model="formValues[field.key]"
                            type="text"
                            class="form-control"
                        />
                        <p v-if="field.configuration?.note" class="field-note">
                            {{ field.configuration.note }}
                        </p>
                    </div>
                </div>
                <div v-else class="no-fields">
                    {{ $t('user_settings_no_fields_in_tab') }}
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-primary" :disabled="saving" @click="save">
                {{ saving ? $t('user_settings_saving') : $t('user_settings_save') }}
            </button>
        </div>

        <ErrorNotification :message="error" @close="error = null" />
        <SuccessNotification :message="successMessage" @close="successMessage = null" />
    </div>
</template>

<script>
import ErrorNotification from '@reno-cms/components/common/ErrorNotification';
import SuccessNotification from '@reno-cms/components/common/SuccessNotification';
import { getContexts, getUserSettingsPage, updateUserSettingsPage } from '../../api/user-settings';
import { loadComponent } from '@reno-cms/utils/componentLoader';
import { getEmptyFormValueForField } from '@reno-cms/utils/fieldSchema';

export default {
    name: 'UserSettingPage',
    components: {
        ErrorNotification,
        SuccessNotification,
    },
    data() {
        return {
            contexts: [],
            selectedContextId: null,
            page: {
                name: '',
                label: '',
                schema: [],
            },
            activeTabKey: null,
            formValues: {},
            saving: false,
            error: null,
            successMessage: null,
        };
    },
    computed: {
        pageName() {
            return this.$route.meta.page_name;
        },
        schemaTabs() {
            return (this.page.schema || [])
                .filter((item) => item.element === 'tab')
                .map((tab, index) => ({
                    ...tab,
                    schema: Array.isArray(tab.schema) ? tab.schema : [],
                    tab_key: this.getTabKey(tab, index),
                }));
        },
        activeSchemaTab() {
            if (this.schemaTabs.length === 0) {
                return null;
            }

            return this.schemaTabs.find((tab) => tab.tab_key === this.activeTabKey) || this.schemaTabs[0];
        },
        activeTabFields() {
            const tab = this.activeSchemaTab;
            if (!tab || !Array.isArray(tab.schema)) {
                return [];
            }

            return tab.schema.filter((element) => element.element === 'field');
        },
    },
    created() {
        this.$watch(
            () => [this.pageName, this.selectedContextId],
            () => {
                this.scheduleLoadPage();
            },
            { immediate: true, flush: 'post' },
        );
    },
    async mounted() {
        await this.loadContexts();
        if (this.contexts.length > 0) {
            this.selectedContextId = this.contexts[0].id;
        }

        await this.$nextTick();
        this.scheduleLoadPage();
    },
    watch: {
        '$route.fullPath': {
            handler() {
                this.scheduleLoadPage();
            },
        },
        schemaTabs: {
            immediate: true,
            handler(tabs) {
                if (!tabs.length) {
                    this.activeTabKey = null;
                    return;
                }

                if (!tabs.some((tab) => tab.tab_key === this.activeTabKey)) {
                    this.activeTabKey = tabs[0].tab_key;
                }
            },
        },
    },
    methods: {
        async loadContexts() {
            try {
                const response = await getContexts();
                this.contexts = response.data || [];
            } catch (error) {
                this.error = this.$t('user_settings_error_load_contexts');
            }
        },
        scheduleLoadPage() {
            if (!this.selectedContextId || !this.pageName) {
                return;
            }

            this.loadPage();
        },
        async loadPage() {
            if (!this.selectedContextId || !this.pageName) {
                return;
            }

            this.error = null;
            this.successMessage = null;

            try {
                const response = await getUserSettingsPage(this.pageName, this.selectedContextId);
                const page = response.data || {};
                this.page = page;
                this.formValues = { ...(page.values || {}) };
                this.ensureSchemaDefaults();
            } catch (error) {
                this.error = this.$t('user_settings_error_load_page');
            }
        },
        ensureSchemaDefaults() {
            for (const tab of this.schemaTabs) {
                for (const field of tab.schema.filter((element) => element.element === 'field')) {
                    if (!Object.prototype.hasOwnProperty.call(this.formValues, field.key)) {
                        this.formValues[field.key] = getEmptyFormValueForField(field);
                    }
                }
            }
        },
        async save() {
            if (!this.selectedContextId || !this.pageName) {
                return;
            }

            this.saving = true;
            this.error = null;
            this.successMessage = null;

            try {
                await updateUserSettingsPage(this.pageName, this.selectedContextId, this.formValues);
                this.successMessage = this.$t('user_settings_success_saved');
            } catch (error) {
                this.error = this.$t('user_settings_error_save');
            } finally {
                this.saving = false;
            }
        },
        getTabKey(tab, index) {
            return `${index}-${tab?.name || 'tab'}`;
        },
        fieldEditorComponent(field) {
            const jsModule = field?.js_module;
            if (!jsModule) {
                return null;
            }

            return this.getValueEditorComponent(jsModule);
        },
        getValueEditorComponent(jsModule) {
            const component = loadComponent(jsModule, {
                errorMessage: this.$t('user_settings_error_load_editor'),
                loadingMessage: this.$t('user_settings_loading_editor'),
            });
            if (!component) {
                console.error('Failed to load component:', jsModule);
            }

            return component;
        },
        getFieldEditorProps(field) {
            return {
                id: `field-${field.key}`,
                name: field.key,
                configuration: field.configuration || {},
                isRequired: field.is_required,
            };
        },
    },
};
</script>
