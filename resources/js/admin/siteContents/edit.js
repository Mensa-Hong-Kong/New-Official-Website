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
import 'ckeditor5/ckeditor5.css';
import { post } from "../../submitForm";

const form = document.getElementById('form');
const content = document.getElementById('validationContent');
const contentFeedback = document.getElementById('contentFeedback');
const saveButton = document.getElementById('saveButton');
const savingButton = document.getElementById('savingButton');

ClassicEditor
    .create(
        content, {
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
            editor.model.document.on(
                'change:data', () => {
                    content.value = editor.getData();
                }
            );
        }
    ).catch( /* ... */ );

function validation() {
    if(content.validity.tooLong) {
        content.classList.add('is-invalid');
        contentFeedback.className = 'invalid-feedback';
        contentFeedback.innerText = `The content field must not be greater than ${content.maxLength} characters.`;
        return false;
    }
    return true;
}

function successCallback(response) {
    savingButton.hidden = true;
    saveButton.hidden = false;
    window.location.href = response.request.responseURL;
}

function failCallback(error) {
    content.classList.remove('is-valid');
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(error.status == 422 && error.response.data.errors.content) {
        content.classList.add('is-invalid');
        contentFeedback.className = "invalid-feedback";
        contentFeedback.innerText = error.response.data.errors.content;
    } else {
        alert('undefine feedback key');
    }
    if(!content.classList.contains('is-invalid')) {
        content.classList.add('is-valid');
    }
    savingButton.hidden = true;
    saveButton.hidden = false;
}

form.addEventListener(
    'submit', function(event) {
        event.preventDefault();
        if(savingButton.hidden) {
            if(validation()) {
                saveButton.hidden = true;
                savingButton.hidden = false;
                let data = {content: content.value,}
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
