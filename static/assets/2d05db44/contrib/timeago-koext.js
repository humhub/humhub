ko.bindingHandlers.timeago = {
    init: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var value = valueAccessor();
        var valueUnwrapped = ko.unwrap(value);
        element.title = moment(valueUnwrapped).toISOString();
        $(element).timeago();
    },
    update: function (element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var value = valueAccessor();
        var valueUnwrapped = ko.unwrap(value);
        element.title = moment(valueUnwrapped).toISOString();
        $(element).timeago('update', element.title);
    }
}
