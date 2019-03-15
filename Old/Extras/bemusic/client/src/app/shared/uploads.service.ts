import {Injectable} from '@angular/core';
import {Response} from "@angular/http";
import {utils} from "./utils";
import {Observable} from "rxjs";
import {AppHttpClient} from "./app-http-client.service";
import {FileValidator} from "./file-validator";
import {Upload} from "./types/models/Upload";

@Injectable()
export class Uploads {

    /**
     * UploadsService Constructor.
     */
    constructor(private httpClient: AppHttpClient, private validator: FileValidator) {}

    /**
     * Get contents of specified file.
     */
    public getFileContents(file: Upload): Observable<Response|string> {
        return this.httpClient.get('uploads/'+file.id);
    }

    /**
     * Download specified upload.
     */
    public downloadFile(file: Upload)  {
        //Uploads.downloadFileFromUrl(this.httpClient.makeUrl('uploads/'+file.id+'/download'), file.name);
    }

    /**
     * Download file from specified url.
     */
    public static downloadFileFromUrl(url: string, name: string) {
        let link = document.createElement('a');
        link.href = url;
        link.download = name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Delete file upload matching given id.
     */
    public delete(ids: number[]) {
        return this.httpClient.delete('uploads', {ids: ids});
    }

    /**
     * Upload specified files as regular 'uploads'
     */
    public uploadFiles(files: FileList | any[], params: {uri?: string, httpParams?: object} = {}): Observable<{data: {url: string}[]}> {
        if ( ! params.uri) params.uri = 'uploads';
        return this.upload(files, params.uri, params.httpParams) as any;
    }

    /**
     * Upload files to specified uri.
     */
    private upload(files: FileList | any[], uri: string, params = {}) {
        let data = new FormData();

        if ( ! utils.isIterable(files)) files = [files];

        //append files
        for (let i = 0; i < files.length; i++) {
            data.append('files[]', files[i]);
        }

        //append extra params
        for (let name in params) {
            data.append(name, params[name]);
        }

        return this.httpClient.post(uri, data);
    }

    /**
     * Validate specified files.
     */
    public filesAreInvalid(fl: File[] | FileList, showErrors: boolean = false) {
        return this.validator.validateFiles(fl, showErrors);
    }

    /**
     * Open browser dialog for uploading files and
     * resolve promise with uploaded files.
     */
    public openUploadDialog(options = {}): Promise<FileList> {
        return new Promise((resolve, reject) => {
            let input = Uploads.makeUploadInput(options);

            input.onchange = (e: Event) => {
                resolve(e.target['files'] as FileList);
                input.remove();
            };

            document.body.appendChild(input);
            input.click();
        });
    }

    /**
     * Create a html5 file upload input element.
     */
    static makeUploadInput(options = {}): HTMLElement {
        let input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = options['accept'];
        input.classList.add('hidden');
        input.id = 'hidden-file-upload-input';
        document.body.appendChild(input);

        return input;
    }
}