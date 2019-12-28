function selectProductAutocompleteItem(event, ui) {
    var $target = $(this);
    $target.val(ui.item.reference);
    var $row = $target.parents('tr');
    $row.find('[name$="[product]"]').val(ui.item[0].reference);
    $row.find('[name$="[nLote]"]').val(ui.item.nLote);
    $row.find('[name$="[unitary_cost]"]').val(ui.item[0].price).trigger('change');
    $row.find('[name$="[description]"]').val(ui.item[0].description);

    return false;
}

function renderProductAutocompleteItem(ul, item) {
    return $('<li>')
        .append('<a>' + item[0].reference + '</a>')
        .appendTo(ul);
}

function addProductNameAutocomplete(path) {
    $('.product-autocomplete-name:not(.ui-autocomplete-input)').each(function () {
        $(this).autocomplete({
            source: path,
            select: selectProductAutocompleteItem,
        }).autocomplete( "instance" )._renderItem = renderProductAutocompleteItem;
    });
}
