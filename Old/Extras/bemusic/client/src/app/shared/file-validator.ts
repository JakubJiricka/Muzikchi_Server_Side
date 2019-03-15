import {Injectable} from '@angular/core';
import {ToastService} from "./toast/toast.service";

@Injectable()
export class FileValidator {

    /**
     * Rules that files under validation must pass.
     */
    private rules = {
        maxSize: 50000 * 1000, //kilobytes
        maxFiles: 5,
        blacklist: [],
        whitelist: [],
    };

    /**
     * FileValidator Constructor.
     */
    constructor(private toast: ToastService) {}

    /**
     * Validate specified files and optionally show error messages in toast window..
     */
    public validateFiles(fs: File[] | FileList, showErrors = false) {
        let errors = {};

        if (fs.length > this.rules.maxFiles) {
            errors['*'] = 'you can upload a maximum of '+this.rules.maxFiles+' files.';
        }

        for (let i = 0; i < fs.length; i++) {
            let fileErrors = this.validateFile(fs[i]);
            if (fileErrors) errors[fs[i].name] = fileErrors;
        }

        let hasErrors = Object.keys(errors).length;

        if (showErrors && hasErrors) {
            this.toast.show(errors[Object.keys(errors)[0]], {delay: 0, type: 'error'});
        }

        return hasErrors ? errors : false;
    }

    /**
     * Validate specified file against validation rules and return errors.
     */
    public validateFile(file: File) {
        let errors = [];
        let extension = FileValidator.getExtensionFromFileName(file.name);

        if (file.size > this.rules.maxSize) {
            errors.push(file.name+' is to large. Maximum file size is '+this.rules.maxSize);
        }

        if (this.rules.blacklist.indexOf(extension) > -1) {
            errors.push(file.name+' type is invalid. '+extension+' files are not allowed.');
        }

        if (this.rules.whitelist.length && this.rules.whitelist.indexOf(extension) === -1) {
            errors.push(file.name+' type is invalid. Only '+this.rules.whitelist.join(',')+' extensions are allowed.');
        }

        return errors.length ? errors : false;
    }

    /**
     * Get file extension from given string.
     */
    static getExtensionFromFileName(name) {
        let re = /(?:\.([^.]+))?$/;
        return re.exec(name)[1];
    }
}