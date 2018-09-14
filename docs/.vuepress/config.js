module.exports = {
    title: 'Laravel Storage Connect',
    base: '/laravel-storage-connect/',
    themeConfig: {
        nav: [
            { text: 'GitHub', link: 'https://github.com/stechstudio/laravel-storage-connect' },
        ],
        sidebar: [
            {
                title: 'Setup',
                collapsable: false,
                children: [
                    '/',
                    '/getting-started',
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
                    '/connection-api',
                    '/events',
                    '/configuration',
                ]
            }
        ]
    }
}
