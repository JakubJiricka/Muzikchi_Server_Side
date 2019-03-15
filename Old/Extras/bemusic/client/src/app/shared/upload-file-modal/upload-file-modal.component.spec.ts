import {KarmaTest} from "../../../../testing/karma-test";
import {UploadFileModalComponent} from "./upload-file-modal.component";
import {fakeAsync, tick} from "@angular/core/testing";
import {UploadsService} from "../../shared/uploads.service";
import {FileValidator} from "../../shared/file-validator";
import {FileDropzoneDirective} from "../../shared/file-dropzone/file-dropzone.directive";
import {MapToIterable} from "../../shared/map-to-iterable";
import {BehaviorSubject, Observable} from "rxjs";
import {SettingsService} from "../../shared/settings.service";

describe('UploadFileModalComponent', () => {
    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [
                    UploadFileModalComponent, FileDropzoneDirective, MapToIterable
                ],
                providers: [UploadsService, FileValidator],
            },
            component: UploadFileModalComponent
        });
    });

    it('uploads files', () => {
        testBed.fixture.detectChanges();
        let eventFired: any = false;
        testBed.component.onDone.subscribe(data => eventFired = data);
        testBed.component.uploadType = 'foo';
        spyOn(testBed.get(UploadsService), 'uploadStaticImages').and.returnValue(new BehaviorSubject({data: [{url: 'foo-url'}]}));
        let files = [{name: 'foo'}, {name: 'bar'}];
        let baseUrl = testBed.get(SettingsService).get('base_url');

        testBed.getChildComponent(FileDropzoneDirective).onUpload.emit(files);

        //calls backend to upload files
        expect(testBed.get(UploadsService).uploadStaticImages).toHaveBeenCalledWith(files, 'foo');

        //inserts image
        expect(eventFired).toEqual(baseUrl+'/foo-url');

        //clears link model
        expect(testBed.component.linkModel).toBeNull();
    });

    it('validates files and renders errors', () => {
        spyOn(testBed.get(UploadsService), 'filesAreInvalid').and.returnValue({foo: 'bar', baz: 'qux'});
        spyOn(testBed.get(UploadsService), 'uploadFiles');

        testBed.component.uploadFiles('files');
        testBed.fixture.detectChanges();

        //validates files
        expect(testBed.get(UploadsService).filesAreInvalid).toHaveBeenCalledWith('files');

        //does not call backend
        expect(testBed.get(UploadsService).uploadFiles).not.toHaveBeenCalled();

        //renders errors
        expect(testBed.findAll('.error').length).toEqual(2);
        expect(testBed.findAll('.error')[0].textContent).toEqual('bar');
        expect(testBed.findAll('.error')[1].textContent).toEqual('qux');
    });

    it('renders errors from backend', () => {
        spyOn(testBed.get(UploadsService), 'uploadStaticImages').and.returnValue(Observable.throw({messages: {foo: 'bar', baz: 'qux'}}));

        testBed.component.uploadFiles([{name: 'foo'}]);
        testBed.fixture.detectChanges();

        expect(testBed.findAll('.error').length).toEqual(2);
        expect(testBed.findAll('.error')[0].textContent).toEqual('bar');
        expect(testBed.findAll('.error')[1].textContent).toEqual('qux');
    });

    it('inserts remote image', fakeAsync(() => {
        let eventFired;
        spyOn(testBed.component, 'validateImage').and.returnValue(new Promise(resolve => resolve()));
        testBed.component.onDone.subscribe(data => eventFired = data);
        testBed.component.activeTab = 'link';
        testBed.fixture.detectChanges();

        testBed.find('#image-link')['value'] = 'foo';
        testBed.dispatchEvent('#image-link', 'change');
        tick();

        //sets image url as link model
        expect(testBed.component.linkModel).toEqual('foo');

        //does not set errors
        expect(testBed.component.errors).toBeFalsy();

        //returns link model with "onDone" event
        testBed.component.confirm();
        tick(201);
        expect(eventFired).toEqual('foo');
    }));

    it('does not insert invalid remote image', fakeAsync(() => {
        spyOn(testBed.component, 'validateImage').and.returnValue(new Promise((resolve, reject) => reject()));
        testBed.component.activeTab = 'link';
        testBed.fixture.detectChanges();

        testBed.find('#image-link')['value'] = 'foo';
        testBed.dispatchEvent('#image-link', 'change');
        tick();

        //does not set image url as link model
        expect(testBed.component.linkModel).toBeFalsy();

        //sets error
        expect(testBed.component.errors['*']).toEqual(jasmine.any(String));
    }));

    it('does not insert image if there are any errors', () => {
        let eventFired;
        testBed.component.onDone.subscribe(() => eventFired = true);
        testBed.component.errors = {};

        testBed.component.confirm();

        //does not fire "onDone" event
        expect(eventFired).toBeFalsy();
    });

    it('closes modal', fakeAsync(() => {
        testBed.component.linkModel = 'foo';

        testBed.find('.close-button').click();
        tick(201);

        //closes modal
        expect(testBed.component.elementRef.nativeElement.classList.contains('hidden')).toEqual(true);

        //clears link model
        expect(testBed.component.linkModel).toBeNull();
    }));

    it('switches tabs', () => {
        testBed.fixture.detectChanges();

        //upload tab is active by default
        expect(testBed.component.activeTab).toEqual('upload');
        expect(testBed.find('.tab.upload')).toBeTruthy();
        expect(testBed.find('.tab.link')).toBeFalsy();

        //opens link tab
        testBed.find('.link-tab-button').click();
        testBed.fixture.detectChanges();
        expect(testBed.find('.tab.upload')).toBeFalsy();
        expect(testBed.find('.tab.link')).toBeTruthy();

        //opens upload tab
        testBed.find('.upload-tab-button').click();
        testBed.fixture.detectChanges();
        expect(testBed.find('.tab.upload')).toBeTruthy();
        expect(testBed.find('.tab.link')).toBeFalsy();
    });

    it('uploads files via drag and drop', fakeAsync(() => {
        testBed.fixture.detectChanges();
        spyOn(testBed.component, 'uploadFiles');
        spyOn(testBed.get(UploadsService), 'openUploadDialog').and.returnValue(new Promise(resolve => resolve('foo')));

        //adds class when file is hovering over dropzone
        testBed.dispatchEvent('.dropzone', 'dragenter');
        expect(testBed.find('.dropzone').classList.contains('file-over-dropzone')).toEqual(true);

        //removes class when file leaves dragzone
        testBed.find('.dropzone').classList.add('file-over-dropzone');
        testBed.dispatchEvent('.dropzone', 'dragleave');
        expect(testBed.find('.dropzone').classList.contains('file-over-dropzone')).toEqual(false);

        //uploads file that is dropped on dropzone
        testBed.find('.dropzone').classList.add('file-over-dropzone');
        testBed.dispatchEvent('.dropzone', 'drop', {dataTransfer: {files: 'foo'}});
        expect(testBed.find('.dropzone').classList.contains('file-over-dropzone')).toEqual(false);
        expect(testBed.component.uploadFiles).toHaveBeenCalledWith('foo');

        //removes class from dropzone when directive is destroyed
        testBed.find('.dropzone').classList.add('file-over-dropzone');
        testBed.getChildComponent(FileDropzoneDirective).ngOnDestroy();
        expect(testBed.find('.dropzone').classList.contains('file-over-dropzone')).toEqual(false);

        //uploads files on dropzone click
        let eventFired: any = false;
        testBed.getChildComponent(FileDropzoneDirective).onUpload.subscribe(files => eventFired = files);
        testBed.find('.dropzone').click();
        tick();
        expect(testBed.get(UploadsService).openUploadDialog).toHaveBeenCalledTimes(1);
        expect(eventFired).toEqual('foo');
    }));
});