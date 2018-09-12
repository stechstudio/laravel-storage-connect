module.exports = {
    title: 'Laravel Storage Connect',
    themeConfig: {
        sidebar: [
            {
                title: 'Setup',
                collapsable: false,
                children: [
                    '/',
                    '/getting-started',
                    '/configuration',
                    '/eloquent-managed-connections',
                    '/self-managed-connections'
                ]
            },
            {
                title: 'Cloud Storage Providers',
                collapsable: false,
                children: [
                    '/dropbox',
                    '/google-drive'
                ]
            },
            {
                title: 'How to Use',
                collapsable: false,
                children: [
                    '/uploading-files',
                    '/connection-api'
                ]
            }
        ]
    }
}