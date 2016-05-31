$(document).ready(function () {

    $('#gif-export-button').unbind('click').bind('click', function() {
        var ids = [];
        $('.export-checkbox:checkbox:checked').each(function() {
           ids.push($(this).attr('id'));
        });
        if (ids.length > 0) {
            $.ajax({
                type: "POST",
                url: 'rest.php',
                data: {command: 'export_configuration_to_gif_by_id', id: ids},
                success: function (data, textStatus, jqXHR) {
                    window.open("export/" + data.file);
                },
                dataType: 'json'
            });
        }
    });

    function setupListeners() {

        $('#datatable td button.copy-configuration-link').each(function () {
            $(this).unbind('click').bind('click', function () {
                copyConfiguration($(this).attr('id'));
                return false;
            });
        });

        $('#datatable td button.remove-configuration-link').each(function () {
            $(this).unbind('click').bind('click', function () {
                removeConfiguration($(this).attr('id'));
                return false;
            });
        });

        $('#datatable td button.export-configuration-link').each(function () {
            $(this).unbind('click').bind('click', function () {
                exportConfiguration($(this).attr('id'));
                return false;
            });
        });

        $('#datatable td button.view-configuration').each(function () {
            $(this).unbind('click').bind('click', function () {
                window.open($(this).attr('href'));
            });
        });

        $('#datatable td button.edit-configuration-link').each(function () {
            $(this).unbind('click').bind('click', function () {
                window.location = $(this).attr('href');
            });
        });
    }

    function removeConfiguration(id) {
        $.ajax({
            type: "POST",
            url: 'rest.php',
            data: {command: 'remove_configuration_by_id', id: id},
            success: function (data, textStatus, jqXHR) {
                table.api().ajax.reload(setupListeners, false);
            },
            dataType: 'json'
        });
    }

    function copyConfiguration(id) {
        showDialog('Copy Configuration', 'Name : ', function() {
            var name = $('#configuration-name').val();
            if (name != '') {
                $.ajax({
                    type: "POST",
                    url: 'rest.php',
                    data: {command: 'copy_configuration_by_id', id: id, name: name},
                    success: function (data, textStatus, jqXHR) {
                        table.api().ajax.reload(setupListeners, false);
                    },
                    dataType: 'json'
                });
                $('#myModal').modal('toggle')
            }
        });
    }

    function exportConfiguration(id) {
        $.ajax({
            type: "POST",
            url: 'rest.php',
            data: {command: 'export_configuration_to_png_by_id', id: id},
            success: function (data, textStatus, jqXHR) {
                table.api().ajax.reload(setupListeners, false);
            },
            dataType: 'json'
        });
    }

    function showDialog(title, text, callback) {
        $('#dialog-confirm-title').html(title);
        $('#dialog-confirm-text').html(text);
        $('#dialog-confirm-ok').unbind().bind('click', callback);
        $('#myModal').modal({});
    }

    function createButton(link, title, clazz, id) {
        return $('<div/>').
            append($('<button/>').
                addClass('btn').
                addClass('btn-default').
                addClass(clazz).
                attr('id', id).
                attr('href', link).
                text(title)
            ).html();
    }

    var table = jQuery('#datatable').dataTable({
        sDom: 'CT<"clear">flprtip',
        "ajax": {
            "url": "rest.php?command=get_all_configurations",
            "dataSrc": function (json) {
                return json.configurations;
            },
            "method": 'GET'
        },
        "bPaginate": true,
        "bAutoWidth": false,
        "bLengthChange": false,
        order: [[ 1, 'asc' ]],
        "columns": [{
            sortable: false,
            "data": "id",
            "render": function (data, type, full, meta) {
                return $('<div/>').append($('<input/>').
                        attr('type', 'checkbox').
                        attr('id', data).
                        addClass('export-checkbox')
                    ).html();
            }
        }, {
            sortable: true,
            "data": "name"
        }, {
            sortable: false,
            "data": "file",
            "render": function (data, type, full, meta) {
                if (data != '' && data != null)
                    return createButton('export/' + data, 'View', 'view-configuration', full.id);
                else
                    return createButton('#', 'Export', 'export-configuration-link', full.id);
            }
        }, {
            sortable: false,
            "data": "id",
            "render": function (data, type, full, meta) {
                return createButton('edit.php?id=' + data, 'Edit', 'edit-configuration-link', full.id);
            }
        }, {
            sortable: false,
            "data": "id",
            "render": function (data, type, full, meta) {
                return createButton('#', 'Copy', 'copy-configuration-link', full.id);
            }
        }, {
            sortable: false,
            "data": "id",
            "render": function (data, type, full, meta) {
                return createButton('#', 'Remove', 'remove-configuration-link', data);
            }
        }],
        "paging": true,
        "displayLength": 50,
        "sPaginationType": "bootstrap",
        "fnInitComplete": function (oSettings, json) {
            setupListeners();
        }
    });

});