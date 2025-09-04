<script>
    import Layout from '@/Pages/Layouts/App.svelte';
    import { onMount } from 'svelte';
    import { Row, Input, Button, Spinner } from '@sveltestrap/sveltestrap';
    import { post } from "@/submitForm.svelte";
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
    import { router } from '@inertiajs/svelte';
    import "ckeditor5/ckeditor5.css";
    
    let {content} = $props();
    let contentInput;
    let contentFeedback = $state('');
    let submitting = $state(false);
    let updating = $state(false);

    onMount(
        () => {
            let editorInstance = null;
            ClassicEditor
                .create(
                    contentInput, {
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
                                content.value = editor.getData();
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

    function validation() {
        if(contentInput.validity.tooLong) {
            contentFeedback = `The content field must not be greater than ${contentInput.maxLength} characters.`;
            return false;
        }
        return true;
    }

    function successCallback(response) {
        updating = false;
        submitting = false;
        router.get(response.request.responseURL);
    }

    function failCallback(error) {
        if(error.status == 422 && error.response.data.errors.content) {
            contentFeedback = error.response.data.errors.content;
        }
        updating = false;
        submitting = false;
    }

    function update(event) {
        event.preventDefault();
        if(submitting == '') {
            let submitAt = Date.now();
            submitting = 'update'+submitAt;
            if(submitting == 'update'+submitAt) {
                if(validation()) {
                    updating = true;
                    post(
                        route(
                            'admin.site-contents.update',
                            {site_content: content.id}
                        ),
                        successCallback,
                        failCallback,
                        'put', {content: contentInput.value}
                    );
                } else {
                    submitting = false;
                }
            }
        }
    }
</script>

<svelte:head>
    <title>Administration Edit Site Content | {import.meta.env.VITE_APP_NAME}</title>
</svelte:head>

<Layout>
    <form method="POST" novalidate onsubmit="{update}">
        <h2 class="mb-2 fw-bold text-uppercase">
            Edit Site Content -
            {content.page.name} -
            {content.name}
        </h2>
        <Row class="mb-3 g-3 form-outline">
            <Input type="textarea" label="Content" name="content" maxlength="65535" required
                feedback={contentFeedback} valid={contentFeedback == 'Looks good!'}
                invalid={contentFeedback != '' && contentFeedback != 'Looks good!' }
                disabled={updating} bind:inner={contentInput} value={content.content} />
        </Row>
        <Button color="primary" class="form-control" disabled={submitting}>
            {#if updating}
                <Spinner type="border" size="sm" />
                Saving...
            {:else}
                Save
            {/if}
        </Button>
    </form>
</Layout>