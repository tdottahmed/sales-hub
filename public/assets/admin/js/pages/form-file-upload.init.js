// Register FilePond plugins
FilePond.registerPlugin(
    FilePondPluginFileEncode,
    FilePondPluginFileValidateSize,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview
);

// Initialize FilePond for multiple input elements
const inputMultipleElements = document.querySelectorAll(
    "input.filepond-input-multiple"
);

if (inputMultipleElements) {
    Array.from(inputMultipleElements).forEach(function (element) {
        FilePond.create(element);
    });
}

// Initialize a FilePond instance with specific options for a circular input
FilePond.create(document.querySelector(".filepond-input-circle"), {
    labelIdle:
        'Drag & Drop your picture or <span class="filepond--label-action">Browse</span>',
    imagePreviewHeight: 170,
    imageCropAspectRatio: "1:1",
    imageResizeTargetWidth: 200,
    imageResizeTargetHeight: 200,
    stylePanelLayout: "compact circle",
    styleLoadIndicatorPosition: "center bottom",
    styleProgressIndicatorPosition: "right bottom",
    styleButtonRemoveItemPosition: "left bottom",
    styleButtonProcessItemPosition: "right bottom",
});
