@props([
    'name' => '', // Input name attribute
    'label' => 'Upload File', // Input label
    'multiple' => false, // Set to true for multiple file uploads
])

@push('styles')
    <!-- FilePond CSS -->
    <link rel="stylesheet" href="/assets/admin/libs/filepond/filepond.min.css" type="text/css" />
    <link rel="stylesheet" href="/assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
@endpush

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ $label }}</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <input type="file" class="filepond" name="{{ $name }}{{ $multiple ? '[]' : '' }}"
                    {{ $multiple ? 'multiple' : '' }} data-max-file-size="100MB"
                    data-max-files="{{ $multiple ? 5 : 1 }}"
                    accept="image/png, image/jpeg, image/gif, application/pdf, application/zip, application/vnd.openxmlformats-officedocument.wordprocessingml.document">
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>
<!-- end row -->

@pushonce('scripts')
    <!-- FilePond JS -->
    <script src="/assets/admin/libs/filepond/filepond.min.js"></script>
    <script src="/assets/admin/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
    <script src="/assets/admin/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
    <script src="/assets/admin/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js">
    </script>
    <script src="/assets/admin/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>

    <script>
        // Register FilePond plugins
        FilePond.registerPlugin(
            FilePondPluginFileEncode,
            FilePondPluginFileValidateSize,
            FilePondPluginImageExifOrientation,
            FilePondPluginImagePreview
        );

        // Initialize FilePond
        document.addEventListener('DOMContentLoaded', function() {
            const filepondInputs = document.querySelectorAll('.filepond');
            Array.from(filepondInputs).forEach(input => {
                FilePond.create(input, {
                    allowMultiple: input.hasAttribute(
                        'multiple'), // Enable/disable multiple files dynamically
                });
            });
        });
    </script>
@endpushonce
