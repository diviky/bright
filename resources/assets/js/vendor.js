import ClipboardJS from '/node_modules/clipboard';
import * as tempusDominus from '/node_modules/@eonasdan/tempus-dominus';
import daterangepicker from '/node_modules/daterangepicker';
import * as FilePond from '/node_modules/filepond';
import FilePondPluginImagePreview from '/node_modules/filepond-plugin-image-preview';
import '/node_modules/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import FilePondPluginFileValidateSize from '/node_modules/filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from '/node_modules/filepond-plugin-file-validate-type';
import '/node_modules/jquery-pjax';
import intlTelInput from '/node_modules/intl-tel-input';

window.ClipboardJS = ClipboardJS;
window.tempusDominus = tempusDominus;
window.daterangepicker = daterangepicker;
window.FilePond = FilePond;
window.FilePondPluginImagePreview = FilePondPluginImagePreview;
window.FilePondPluginFileValidateSize = FilePondPluginFileValidateSize;
window.FilePondPluginFileValidateType = FilePondPluginFileValidateType;
window.intlTelInput = intlTelInput;
