/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'core/logger',
    'util/url',
    'layout/actions/binder',
    'layout/loading-bar',
    'ui/feedback',
    'ui/taskQueue/taskQueue',
    'ui/taskQueueButton/treeButton'
], function ($, _, __, module, loggerFactory, urlHelper, binder, loadingBar, feedback, taskQueue, treeTaskButtonFactory) {
    'use strict';


    console.log('FE controller working');

    var logger = loggerFactory('controller/inspectResults');

    /**
     * Take care of errors
     * @param err
     */
    function reportError(err) {
        loadingBar.stop();

        logger.error(err);

        if (err instanceof Error) {
            feedback().error(err.message);
        }
    }
    return {

        /**
         * Controller entry point
         */
        start: function () {
            var config = module.config() || {};
            var taskButton;

            console.log(config);

            taskButton = treeTaskButtonFactory({
                replace : true,
                icon : 'desktop-preview',
                label : __('Proctorio Review')
            }).render($('#results-proctorio-review'));

            binder.register('proctorio_url_redirect', function remove(actionContext){
                console.log('binding done');
                var data = _.pick(actionContext, ['uri', 'classUri', 'id']);
                var uniqueValue = data.uri || data.classUri || '';
                taskButton.setTaskConfig({
                    taskCreationUrl : this.url,
                    taskCreationData : {uri : uniqueValue}
                }).start();
            });
        }
    };
});
