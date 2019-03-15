import {Component, ElementRef, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Subject} from "rxjs/Subject";
import {MailTemplatePreviewComponent} from "./mail-template-preview/mail-template-preview.component";
import {ToastService} from "../../shared/toast/toast.service";
import {ActivatedRoute} from "@angular/router";
import {CurrentUser} from "../../auth/current-user";
import {MailTemplate} from "../../shared/types/models/MailTemplate";
import {AppHttpClient} from "../../shared/app-http-client.service";
import {utils} from "../../shared/utils";

declare let ace;

@Component({
    selector: 'mail-templates',
    templateUrl: './mail-templates.component.html',
    styleUrls: ['./mail-templates.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class MailTemplatesComponent implements OnInit {
    @ViewChild('editor') editorEl: ElementRef;
    @ViewChild(MailTemplatePreviewComponent) preview: MailTemplatePreviewComponent;

    /**
     * Ace editor instance.
     */
    private editor: any;

    /**
     * All available email templates and their contents.
     */
    public templates: {model: MailTemplate, html: string, plain: string}[] = [];

    /**
     * Currently selected email template.
     */
    public selectedTemplate = {model: new MailTemplate, html: '', plain: ''};

    /**
     * Currently selected layout for template editor.
     */
    public selectedLayout = 'row';

    /**
     * Currently selected template type.
     */
    public selectedType: 'html'|'plain' = 'html';

    /**
     * Validation errors from backend.
     */
    public errors: {subject?: string, contents?: string} = {};

    /**
     * Fired when editor contents change.
     */
    private editorChange = new Subject();

    /**
     * MailTemplatesComponent Constructor.
     */
    constructor(
        private http: AppHttpClient,
        private toast: ToastService,
        private route: ActivatedRoute,
        public currentUser: CurrentUser,
        private utils: utils,
    ) {}

    /**
     * Called after data-bound properties of a component are initialized.
     */
    ngOnInit() {
        this.bindToEditorChange();

        this.route.data.subscribe(data => {
            this.templates = data['templates'];
            this.selectedTemplate = this.templates[0];
            this.initEditor().then(() => {
                this.setEditorValue();
            });
        });
    }

    /**
     * Toggle template type between "plain" and "html"
     */
    public toggleTemplateType() {
        this.selectedType = this.selectedType === 'html' ? 'plain' : 'html';
        this.setEditorValue();
    }

    /**
     * Set article list layout to specified one.
     */
    public setLayout(name: string) {
        this.selectedLayout = name;
    }

    /**
     * Check if specified layout is currently active.
     */
    public isLayoutActive(name: string) {
        return this.selectedLayout === name;
    }

    /**
     * Check if specified template type is currently active.
     */
    public isTypeActive(name: string) {
        return this.selectedType === name;
    }

    /**
     * Restore currently selected template contents to default values.
     */
    public restoreDefault() {
        let id = this.selectedTemplate.model.id;

        this.http.post('mail-templates/'+id+'/restore-default').subscribe((template: any) => {
            this.selectedTemplate.html = template.html;
            this.selectedTemplate.plain = template.plain;
            this.setEditorValue();
        });
    }

    /**
     * Update currently selected template.
     */
    public updateSelectedTemplate() {
        let payload = {
            subject: this.selectedTemplate.model.subject,
            contents: {
                html: this.selectedTemplate.html,
                plain: this.selectedTemplate.plain,
            }
        };

        this.http.put('mail-templates/'+this.selectedTemplate.model.id, payload).subscribe(() => {
            this.errors = {};
            this.toast.show('Mail template updated');
        }, errors => this.errors = errors.messages);
    }


    /**
     * Set ace editor value to currently selected template contents.
     */
    public setEditorValue() {
        let text = this.selectedType === 'html'
            ? this.selectedTemplate.html
            : this.selectedTemplate.plain;

        this.editor.setValue(text, -1);
    }

    /**
     * Initiate code editor with specified contents.
     */
    private initEditor(language = 'html') {
        return this.utils.loadScript('assets/js/ace/ace.js').then(() => {
            this.editor = ace.edit(this.editorEl.nativeElement);
            this.editor.getSession().setMode('ace/mode/'+language);
            this.editor.setTheme('ace/theme/chrome');
            this.editor.$blockScrolling = Infinity;

            //fire editor change observable, on editor content change
            this.editor.getSession().on('change', () => {
                this.editorChange.next(this.editor.getValue());
            });
        });
    }

    /**
     * Update template preview when editor content changes.
     */
    private bindToEditorChange() {
        this.editorChange
            .debounceTime(500)
            .subscribe(() => {
                this.selectedTemplate[this.selectedType] = this.editor.getValue();
                this.preview.update(this.selectedTemplate, this.selectedType);
            });
    }
}
