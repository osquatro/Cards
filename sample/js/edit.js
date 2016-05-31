function initCardTable(stateObject) {

    var stateObject = stateObject;
    $('#canvasOne').ctvControl({
        imagePath : '../images',
        initExternalListeners: function (tableApi) {
            jQuery('.ctv-clear').unbind('click').bind('click', function () {
                var cards = tableApi.getConfiguration().cards;
                if (cards.length > 0
                    && (stateObject.state != '[]' || isTableChanged(stateObject, tableApi))) {
                    confirmDialog('Confirm', 'Are you really want to clear table?', function () {
                        tableApi.clear();
                    });
                }
            });

            jQuery('.ctv-export-frame').unbind('click').bind('click', function () {
                tableApi.toggleFrame();
            });

            jQuery('.ctv-load-saved').unbind('click').bind('click', function () {
                var action = function () {
                    if (stateObject.id == null) {
                        tableApi.clear();
                    } else {
                        loader(stateObject, tableApi);
                    }
                };

                if (isTableChanged(stateObject, tableApi)) {
                    confirmDialog('Confirm', 'Are you really want to discard unsaved changes?', action);
                } else {
                    action();
                }
            });

            jQuery('.ctv-close').unbind('click').bind('click', function () {
                var action = function () {
                    window.location.href = 'index.php';
                };

                if (isTableChanged(stateObject, tableApi)) {
                    confirmDialog('Confirm', 'Are you really want to close page and discard changes?', action);
                } else {
                    action();
                }
            });
            jQuery('.ctv-export').unbind('click').bind('click', function () {
                if (stateObject.id == null || isTableChanged(stateObject, tableApi)) {
                    errorMessage('Save configuration at first.');
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: 'rest.php',
                    data: {command: 'export_configuration_to_png_by_id', id: stateObject.id},
                    success: function (data, textStatus, jqXHR) {
                    //TODO : do something
                    },
                    dataType: 'json'
                });
            });

            jQuery('.ctv-save-as').unbind('click').bind('click', function () {
                if (jQuery('#configuration-name').val() == '') {
                    errorMessage('Name should not be empty.');
                    return;
                }

                var name = jQuery('#configuration-name').val();
                save(stateObject, name, tableApi);
            });

            if (stateObject.id != null) {
                loader(stateObject, tableApi);
            }
        }
    });

    function loader(stateObject, tableApi) {
        jQuery.ajax({
            type: "POST",
            url: 'rest.php',
            data: {command: 'get_configuration_by_id', id: stateObject.id},
            success: function (data, textStatus, jqXHR) {
                jQuery('#configuration-name').val(data.name);
                tableApi.loadConfiguration(data.configuration);
                stateObject.state = JSON.stringify(data.configuration.cards);
                console.log("Loaded conf: " + stateObject.state);
            },
            dataType: 'json'
        });
    }

    function save(stateObject, name, tableApi) {
        var command = stateObject.id == null ? 'save_configuration' : 'update_configuration_by_id';
        var configuration = JSON.stringify(tableApi.getConfiguration());

        jQuery.ajax({
            type: "POST",
            url: 'rest.php',
            data: {
                command: command,
                name: name,
                id: stateObject.id,
                configuration: configuration
            },
            success: function (data, textStatus, jqXHR) {
                if (data.success) {
                    stateObject.state = JSON.stringify(tableApi.getConfiguration().cards);
                    if (stateObject.id == null) {
                        stateObject.id = data.id;
                    }
                }
            },
            dataType: 'json'
        });
    }

    function isTableChanged(stateObject, tableApi) {
        var currentConfiguration = JSON.stringify(tableApi.getConfiguration().cards);
        return currentConfiguration != stateObject.state;
    }

    function confirmDialog(title, text, callback) {
        $('#dialog-confirm-title').html(title);
        $('#dialog-confirm-text').html(text);
        $('#dialog-confirm-ok').unbind().bind('click', function () {
            callback();
            $('#myModal').modal('toggle')
        });
        $('#myModal').modal({});
    }

    function errorMessage(error) {
        var close = $('<button/>').
            attr('type', 'button').
            attr('class', 'close').
            attr('data-dismiss', 'alert').
            attr('aria-label', 'Close').
            append($('<aria-hidden/>').attr('aria-hidden', 'true').html('?'));

        $('<div/>').
            addClass('alert').
            addClass('alert-danger').
            addClass('alert-dismissible').
            append($('<strong/>').html('Error :  ')).
            append(error).
            append(close).
            attr('role', 'alert').
            prependTo('#main-content');
    }
}