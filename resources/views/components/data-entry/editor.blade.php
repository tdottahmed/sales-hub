@props([])
@php
    $atr = $attributes;
    $id = $atr->get('id') ?? uniqid('editor-');
@endphp

<textarea name="{{ $atr->get('name') }}" id="{{ $id }}-content" style="display: none">
{!! $slot !!}</textarea
>
<textarea {{ $atr->except('name')->merge([
    'id' => $id,
]) }}>{!! $slot !!}</textarea>

@pushOnce('styles')
    <style>
        figure.image {
            display: inline-block;
            border: 1px solid gray;
            margin: 0 2px 0 1px;
            background: #f5f2f0;
        }

        figure.align-left {
            float: left;
        }

        figure.align-right {
            float: right;
        }

        figure.image img {
            margin: 8px 8px 0 8px;
        }

        figure.image figcaption {
            margin: 6px 8px 6px 8px;
            text-align: center;
        }

        img.align-left {
            float: left;
        }

        img.align-right {
            float: right;
        }

        /* Basic styles for Table of Contents plugin (tableofcontents) */
        .mce-toc {
            border: 1px solid gray;
        }

        .mce-toc h2 {
            margin: 4px;
        }

        .mce-toc li {
            list-style-type: none;
        }

        .tox-promotion {
            display: none;
        }

        .tox-statusbar__branding {
            display: none;
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script>
        $(document).ready(function() {
            const isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
            let useDarkMode = $('html').data('bs-theme') == 'dark';
            tinymce.init({
                selector: '#{{ $id }}',
                setup: (editor) => {
                    editor.on('change', function(e) {
                        $('#{{ $id }}-content').html(editor.getContent());
                    });
                },
                init_instance_callback: function(editor) {
                    editor.focus();
                    // editor.setContent('..');
                },
                plugins: 'preview searchreplace autolink save directionality code visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
                editimage_cors_hosts: ['picsum.photos'],
                menubar: true,
                toolbar: `undo redo |
             link media image table|
             bold italic underline strikethrough removeformat |
            forecolor backcolor  |
            numlist bullist  |
            blocks fontsize |
            align lineheight outdent indent |
            ltr rtl |
            charmap emoticons | codesample preview | insertdatetime searchreplace
            `,
                toolbar_sticky: false,
                images_file_types: 'jpg,jpeg,gif,png,svg,webp',
                file_picker_types: 'file image media',

                image_advtab: true,
                link_list: [{
                        title: 'My page 1',
                        value: 'https://www.tiny.cloud',
                    },
                    {
                        title: 'My page 2',
                        value: 'http://www.moxiecode.com',
                    },
                ],
                image_list: [{
                        title: 'My page 1',
                        value: 'https://www.tiny.cloud',
                    },
                    {
                        title: 'My page 2',
                        value: 'http://www.moxiecode.com',
                    },
                ],
                image_class_list: [{
                        title: 'None',
                        value: '',
                    },
                    {
                        title: 'Some class',
                        value: 'class-name',
                    },
                ],
                importcss_append: true,
                file_picker_callback: (cb, value, meta) => {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept',
                        'image/*, video/*, audio/*, application/pdf, application/zip');

                    input.addEventListener('change', (e) => {
                        const file = e.target.files[0];

                        const reader = new FileReader();
                        reader.addEventListener('load', () => {
                            /*
                              Note: Now we need to register the blob in TinyMCEs image blob
                              registry. In the next release this part hopefully won't be
                              necessary, as we are looking to handle it internally.
                            */
                            const id = 'blobid' + new Date().getTime();
                            const blobCache = tinymce.activeEditor.editorUpload
                                .blobCache;
                            const base64 = reader.result.split(',')[1];
                            const blobInfo = blobCache.create(id, file, base64);
                            blobCache.add(blobInfo);

                            /* call the callback and populate the Title field with the file name */
                            cb(blobInfo.blobUri(), {
                                title: file.name,
                            });
                        });
                        reader.readAsDataURL(file);
                    });

                    input.click();
                },
                min_height: 450,
                max_height: 450,
                image_caption: true,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
                noneditable_class: 'mceNonEditable',
                toolbar_mode: 'sliding',
                contextmenu: 'link image table',
                skin: useDarkMode ? 'oxide-dark' : 'oxide',
                content_css: useDarkMode ? 'dark' : 'default',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            });
        });
    </script>
@endPushOnce
