$(document).ready(function () {
    tinymce.init({
        selector: 'textarea.tinymce', // Apply to all textareas with class 'tinymce'
        readonly: 1, // Set readonly to true
        menubar: false, // Optional: Disable menubar
        toolbar: false, // Optional: Disable toolbar
        branding: false,
        statusbar: false,
    });
});