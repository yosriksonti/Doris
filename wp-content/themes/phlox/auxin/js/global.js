/**
 * Ajax install the Theme Core Plugin
 *
 */
(function($, window, document, undefined){
    "use strict";

    $(function(){

        $('.auxin-install-now').on( 'click', function( event ) {
            var $button = $( event.target );
            event.preventDefault();

            if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
                return;
            }

            /**
             * Install a plugin
             *
             * @return void
             */
            function installPlugin($data){

                $.ajax({
                    url : $data['data-install-url'],
                    type: 'GET',
                    data: {},
                    beforeSend: function () {
                        buttonStatusInProgress( $data['data-installing-label']  );
                    },
                    success: function( reposnse ) {
                        buttonStatusInstalled( wp.updates.l10n.pluginInstalled );
                        activatePlugin($data);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        // Installation failed
                        buttonStatusDisabled( wp.updates.l10n.installFailedShort );
                        return false;
                    }
                });
            }

            /**
             * Activate a plugin
             *
             * @return void
             */
            function activatePlugin($data){

                $.ajax({
                    url : $data['data-activate-url'],
                    type: 'GET',
                    data: {},
                    beforeSend: function () {
                        buttonStatusInProgress( $data['data-activating-label'] );
                    },
                    success: function( reposnse ) {
                        buttonStatusDisabled( wp.updates.l10n.installedMsg );
                        run($data['data-plugin-order']);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        // Activation failed
                        console.log( xhr.responseText );
                        buttonStatusDisabled( wp.updates.l10n.unknownError );
                        return false;
                    }
                });
            }

            /**
             * Change button status to in-progress
             *
             * @return void
             */
            function buttonStatusInProgress( message ){
                $button.addClass('updating-message').removeClass('button-disabled aux-not-installed installed').text( message );
            }

            /**
             * Change button status to disabled
             *
             * @return void
             */
            function buttonStatusDisabled( message ){
                $button.removeClass('updating-message aux-not-installed installed')
                        .addClass('button-disabled')
                        .text( message );
            }

            /**
             * Change button status to installed
             *
             * @return void
             */
            function buttonStatusInstalled( message ){
                $button.removeClass('updating-message aux-not-installed')
                        .addClass('installed')
                        .text( message );
            }

            const $plugins_info = $button.data('info');
            function run($key = 0) {
                if (typeof $plugins_info[$key] == 'undefined' || $plugins_info[$key]['data-plugin-order'] > $plugins_info[0]['data-num-of-required-plugins'] ) {
                    location.replace( $plugins_info[$plugins_info.length - 1]['data-redirect-url'] );
                    return;
                }
                let $this = $plugins_info[$key];
                if( $this['data-action'] === 'install' ){
                    installPlugin($this);
                } else if( $this['data-action'] === 'activate' ){
                    activatePlugin($this);
                }
            }
            run();

        });

    });

})(jQuery, window, document);
