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
const pathname = document.getElementById('validationPathname');
const pathnameFeedback = document.getElementById('pathnameFeedback');
const title = document.getElementById('validationTitle');
const titleFeedback = document.getElementById('titleFeedback');
const OGImageUrl = document.getElementById('validationOGImageUrl');
const OGImageUrlFeedback = document.getElementById('OGImageUrlFeedback');
const description = document.getElementById('validationDescription');
const descriptionFeedback = document.getElementById('descriptionFeedback');
const content = document.getElementById('validationContent');
const contentFeedback = document.getElementById('contentFeedback');
const saveButton = document.getElementById('saveButton');
const savingButton = document.getElementById('savingButton');

const inputs = [pathname, title, OGImageUrl, description, content];
const feedbacks = [pathnameFeedback, titleFeedback, OGImageUrlFeedback, descriptionFeedback, contentFeedback];

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

function hasError() {
    for(let feedback of feedbacks) {
        if(feedback.className == 'invalid-feedback') {
            return true;
        }
    }
    return false;
}

function validation() {
    if(pathname.validity.valueMissing) {
        pathname.classList.add('is-invalid');
        pathnameFeedback.className = 'invalid-feedback';
        pathnameFeedback.innerText = 'The pathname field is required.';
    } else if(pathname.validity.tooLong) {
        pathname.classList.add('is-invalid');
        pathnameFeedback.className = 'invalid-feedback';
        pathnameFeedback.innerText = `The pathname field must not be greater than ${pathname.maxLength} characters.`;
    } else if(pathname.validity.patternMismatch) {
        pathname.classList.add('is-invalid');
        pathnameFeedback.className = 'invalid-feedback';
        pathnameFeedback.innerText = 'The pathname field must only contain letters, numbers, dashes and slash.';
    }
    if(title.validity.valueMissing) {
        title.classList.add('is-invalid');
        titleFeedback.className = 'invalid-feedback';
        titleFeedback.innerText = 'The title field is required.';
    } else if(title.validity.tooLong) {
        title.classList.add('is-invalid');
        titleFeedback.className = 'invalid-feedback';
        titleFeedback.innerText = `The title field must not be greater than ${title.maxLength} characters.`;
    }
    if(OGImageUrl.value) {
        if(OGImageUrl.validity.tooLong) {
            OGImageUrl.classList.add('is-invalid');
            OGImageUrlFeedback.className = 'invalid-feedback';
            OGImageUrlFeedback.innerText = `The open graph image url field must not be greater than ${OGImageUrl.maxLength} characters.`;
        } else if(OGImageUrl.validity.typeMismatch) {
            OGImageUrl.classList.add('is-invalid');
            OGImageUrlFeedback.className = 'invalid-feedback';
            OGImageUrlFeedback.innerText = 'The open graph image url field is not a valid URL.';
        }
    }
    if(description.validity.valueMissing) {
        description.classList.add('is-invalid');
        descriptionFeedback.className = 'invalid-feedback';
        descriptionFeedback.innerText = 'The description field is required.';
    } else if(description.validity.tooLong) {
        description.classList.add('is-invalid');
        descriptionFeedback.className = 'invalid-feedback';
        descriptionFeedback.innerText = `The description field must not be greater than ${description.maxLength} characters.`;
    }
    if(content.validity.tooLong) {
        content.classList.add('is-invalid');
        contentFeedback.className = 'invalid-feedback';
        contentFeedback.innerText = `The content field must not be greater than ${content.maxLength} characters.`;
    }
    return !hasError();
}

function successCallback(response) {
    savingButton.hidden = true;
    saveButton.hidden = false;
    window.location.href = response.request.responseURL;
}

function failCallback(error) {
    for(let input of inputs) {
        input.classList.remove('is-valid');
    }
    for(let feedback of feedbacks) {
        feedback.className = 'valid-feedback';
        feedback.innerText = 'Looks good!'
    }
    if(error.status == 422) {
        for(let key in error.response.data.errors) {
            let value = error.response.data.errors[key];
            let feedback;
            let input;
            switch(key) {
                case 'pathname':
                    input = pathname;
                    feedback = pathnameFeedback;
                    break;
                case 'title':
                    input = title;
                    feedback = titleFeedback;
                    break;
                case 'og_image_url':
                    input = OGImageUrl;
                    feedback = OGImageUrlFeedback;
                    break;
                case 'description':
                    input = description;
                    feedback = descriptionFeedback;
                    break;
                case 'content':
                    input = content;
                    feedback = contentFeedback;
                    break;
            }
            if(feedback) {
                input.classList.add('is-invalid');
                feedback.className = "invalid-feedback";
                feedback.innerText = value;
            } else {
                alert('undefine feedback key');
            }
        }
    }
    for(let input of inputs) {
        if(!input.classList.contains('is-invalid')) {
            input.classList.add('is-valid');
        }
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
                let data = {
                    pathname: pathname.value,
                    title: title.value,
                    og_image_url: OGImageUrl.value,
                    description: description.value,
                    content: content.value,
                }
                post(form.action, successCallback, failCallback, 'put', data);
            }
        }
    }
);
