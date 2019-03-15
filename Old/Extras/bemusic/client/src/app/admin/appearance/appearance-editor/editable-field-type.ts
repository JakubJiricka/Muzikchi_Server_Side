export interface EditableField {
    name: string,
    selector?: string,
    value: any,
    defaultValue?: any,
    type?: 'image'|'color'|'text',
    key: string,
    config?: any,
    input_type?: string,
    image_type?: string,
    text_type?: string,
}