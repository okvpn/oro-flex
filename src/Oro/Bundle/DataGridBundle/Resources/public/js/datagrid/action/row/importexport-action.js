define([
    'jquery',
    '../model-action',
    'oroimportexport/js/importexport-manager'
], function($, ModelAction, ImportExportManager) {
    'use strict';

    // TODO: refactor in scope https://magecore.atlassian.net/browse/BAP-11703
    const ImportExportAction = ModelAction.extend({
        /** @property {Object} */
        configuration: {
            options: {
                datagridName: undefined,
                routeOptions: {}
            }
        },

        /** @property {String} */
        importProcessor: null,
        /** @property {String} */
        importJob: null,
        /** @property {String} */
        importValidateJob: null,

        /** @property {String} */
        exportProcessor: null,
        /** @property {String} */
        exportJob: null,
        /** @property {String} */
        exportTemplateJob: null,

        /** @property {ImportExportManager} */
        importExportManager: null,

        /**
         * @inheritdoc
         */
        constructor: function ImportExportAction(options) {
            ImportExportAction.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ImportExportAction.__super__.initialize.call(this, options);

            if (this.configuration.options.datagridName === null) {
                this.configuration.options.datagridName = this.datagrid.name;
            }

            const opts = $.extend({}, this.configuration.options, {
                entity: this.entity_class,
                importProcessor: this.importProcessor,
                importJob: this.importJob,
                importValidateJob: this.importValidateJob,
                exportProcessor: this.exportProcessor,
                exportJob: this.exportJob,
                exportTemplateJob: this.exportTemplateJob
            });

            this.importExportManager = new ImportExportManager(opts);
        },

        /**
         * @inheritdoc
         */
        execute: function() {
            switch (this.type) {
                case 'import':
                    this.importExportManager.handleImport();
                    break;
                case 'export':
                    this.importExportManager.handleExport();
                    break;
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.importExportManager;

            ImportExportAction.__super__.dispose.call(this);
        }
    });

    return ImportExportAction;
});
