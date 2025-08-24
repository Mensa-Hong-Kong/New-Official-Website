<script>
    import { onMount } from 'svelte';
    import { Row, InputGroup, Label, InputGroupText, Input } from '@sveltestrap/sveltestrap';
    import "ckeditor5/ckeditor5.css";
    import {
        ClassicEditor, Essentials, TextTransformation, GeneralHtmlSupport, AutoImage, AutoLink,
        PasteFromOffice, Clipboard,
        SourceEditing, ShowBlocks, FindAndReplace,
        TextPartLanguage, Heading, Alignment, Font,
        Bold, Italic, Strikethrough, Subscript, Superscript, Underline, RemoveFormat,
        SpecialCharacters, SpecialCharactersEssentials, HorizontalLine, Link, Image, ImageInsert, MediaEmbed, Table, BlockQuote, CodeBlock, HtmlEmbed,
        List, TodoList,
        Indent, IndentBlock,
        TableToolbar, TableProperties, TableCellProperties, TableColumnResize,
    } from 'ckeditor5';
    import coreTranslations from 'ckeditor5/translations/zh.js';
    let {inputs = $bindable(), feedbacks = $bindable(), page = {}, submitting} = $props();

    onMount(
        () => {
            let editorInstance = null;
            ClassicEditor
                .create(
                    inputs.content, {
                        licenseKey: 'GPL',
                        placeholder: 'Type some content here!',
                        plugins: [
                            Essentials, TextTransformation, GeneralHtmlSupport, AutoImage, AutoLink,
                            PasteFromOffice, Clipboard,
                            SourceEditing, ShowBlocks, FindAndReplace,
                            TextPartLanguage, Heading, Alignment, Font,
                            Bold, Italic, Strikethrough, Subscript, Superscript, Underline, RemoveFormat,
                            SpecialCharacters, SpecialCharactersEssentials, HorizontalLine, Link, Image, ImageInsert, MediaEmbed, Table, BlockQuote, CodeBlock, HtmlEmbed,
                            List, TodoList,
                            Indent, IndentBlock,
                            TableToolbar, TableProperties, TableCellProperties, TableColumnResize,
                        ],
                        toolbar: {
                            items: [
                                'SourceEditing', 'showBlocks', 'findAndReplace',
                                '|',
                                'textPartLanguage', 'heading', 'alignment', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                                '|',
                                'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'removeFormat',
                                '-',
                                'undo', 'redo',
                                '|',
                                'specialCharacters', 'horizontalLine', 'link', 'insertImageViaUrl', 'mediaEmbed', 'insertTable', 'blockQuote', 'codeBlock', 'htmlEmbed',
                                '|',
                                'bulletedList', 'numberedList', 'todoList',
                                '|',
                                'outdent', 'indent',
                            ],
                            shouldNotGroupWhenFull: true
                        },
                        table: {
                            contentToolbar: [
                                'tableColumn', 'tableRow', 'mergeTableCells',
                                'tableProperties', 'tableCellProperties'
                            ],

                            tableProperties: {
                                // The configuration of the TableProperties plugin.
                            },

                            tableCellProperties: {
                                // The configuration of the TableCellProperties plugin.
                            }
                        },
                        language: {
                            textPartLanguage: [
                                { title: 'English', languageCode: 'en' },
                                { title: 'Chinese', languageCode: 'zh' },
                            ]
                        },
                        translations: coreTranslations
                    }
                ).then(
                    editor => {
                        editorInstance = editor;
                        editor.model.document.on(
                            'change:data', () => {
                                inputs.content.value = editor.getData();
                            }
                        );
                    }
                ).catch(
                    error => {
                        console.error( 'Error initializing CKEditor:', error );
                    }
                );
            return () => {
                if ( editorInstance ) {
                    editorInstance.destroy().catch( error => console.error( error ) );
                }
            }
        }
    );

    function hasError() {
        for(let [key, feedback] of Object.entries(feedbacks)) {
            if(feedback != 'Looks good!') {
                return true;
            }
        }
        return false;
    }

    export function validation() {
        for(let key in feedbacks) {
            feedbacks[key] = 'Looks good!';
        }
        if(inputs.pathname.validity.valueMissing) {
            feedbacks.pathname = 'The pathname field is required.';
        } else if(inputs.pathname.validity.tooLong) {
            feedbacks.pathname = `The pathname field must not be greater than ${inputs.pathname.maxLength} characters.`;
        } else if(inputs.pathname.validity.patternMismatch) {
            feedbacks.pathname = 'The pathname field must only contain letters, numbers, dashes and slash.';
        }
        if(inputs.title.validity.valueMissing) {
            feedbacks.title = 'The title field is required.';
        } else if(inputs.title.validity.tooLong) {
            feedbacks.title = `The title field must not be greater than ${inputs.title.maxLength} characters.`;
        }
        if(inputs.openGraphImageUrl.value) {
            if(inputs.openGraphImageUrl.validity.tooLong) {
                feedbacks.openGraphImageUrl = `The open graph image url field must not be greater than ${inputs.openGraphImageUrl.maxLength} characters.`;
            } else if(inputs.openGraphImageUrl.validity.typeMismatch) {
                feedbacks.openGraphImageUrl = 'The open graph image url field is not a valid URL.';
            }
        }
        if(inputs.description.validity.valueMissing) {
            feedbacks.description = 'The description field is required.';
        } else if(inputs.description.validity.tooLong) {
            feedbacks.description = `The description field must not be greater than ${inputs.description.maxLength} characters.`;
        }
        if(inputs.content.validity.tooLong) {
            feedbacks.content = `The content field must not be greater than ${inputs.content.maxLength} characters.`;
        }
        return ! hasError();
    }
</script>

<Row class='mb-3 g-3 form-outline'>
    <Label>Pathname</Label>
    <InputGroup>
        <InputGroupText>https://{import.meta.env.VITE_APP_URL}/</InputGroupText>
        <Input name="pathname" placeholder="abc/xyz-123" value={page.pathname ?? null}
            maxlength="768" pattern="[A-Za-z0-9-\/]+" required disabled={submitting}
            feedback={feedbacks.pathname} valid={feedbacks.pathname == 'Looks good!'}
            invalid={feedbacks.pathname != '' && feedbacks.pathname != 'Looks good!'}
            bind:inner={inputs.pathname} />
    </InputGroup>
</Row>
<Row class='mb-3 g-3 form-outline'>
    <Label>Title</Label>
    <Input name="title" placeholder="title..." value={page.title ?? null}
        maxlength="60" required bind:inner={inputs.title} disabled={submitting}
        feedback={feedbacks.title} valid={feedbacks.title == 'Looks good!'}
        invalid={feedbacks.title != '' && feedbacks.title != 'Looks good!'}/>
</Row>
<Row class='mb-3 g-3 form-outline'>
    <Label>Open Graph Image URL</Label>
    <Input name="og_image_url" placeholder="https://google.com"
        maxlength="8000" value={page.open_graph_image_url ?? null} disabled={submitting}
        feedback={feedbacks.openGraphImageUrl} valid={feedbacks.openGraphImageUrl == 'Looks good!'}
        invalid={feedbacks.openGraphImageUrl != '' && feedbacks.openGraphImageUrl != 'Looks good!'}
        bind:inner={inputs.openGraphImageUrl} />
</Row>
<Row class='mb-3 g-3 form-outline'>
    <Label>Description</Label>
    <Input name="description" placeholder="description..."
        maxlength="65" required value={page.description ?? null} disabled={submitting}
        feedback={feedbacks.description} valid={feedbacks.description == 'Looks good!'}
        invalid={feedbacks.description != '' && feedbacks.description != 'Looks good!'}
        bind:inner={inputs.description} />
</Row>
<Row class='mb-3 g-3 form-outline'>
    <Label>Content</Label>
    <Input type="textarea" name="description" value={page.content ?? null}
        maxlength="4194303" bind:inner={inputs.content} disabled={submitting}
        feedback={feedbacks.content} valid={feedbacks.content == 'Looks good!'}
        invalid={feedbacks.content != '' && feedbacks.content != 'Looks good!'} />
</Row>