export const general = {
    name: 'General',
    route: '/',
    fields: [
        {
            name: 'Logo',
            type: 'image',
            key: 'branding.site_logo',
            image_type: 'src',
            selector: '.logo',
        },
        {
            name: 'Favicon',
            type: 'image',
            key: 'branding.favicon',
        },
        {
            name: 'Site Name',
            type: 'text',
            key: 'branding.site_name',
        },
        {
            name: 'Site Url',
            type: 'text',
            input_type: 'url',
            key: 'env.app_url',
        }
    ]
};