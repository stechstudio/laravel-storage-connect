module.exports = {
    title: 'Laravel Storage Connect',
    base: '/laravel-storage-connect/',
    themeConfig: {
        nav: [
            { text: 'GitHub', link: 'https://github.com/stechstudio/laravel-storage-connect' },
        ],
        sidebar: [
            {
                title: 'Getting Started',
                collapsable: false,
                children: [
                    '/',
                    '/installation',
                    '/configuration'
                ]
            },
            {
                title: 'Setup Storage Providers',
                collapsable: false,
                children: [
                    '/dropbox',
                    '/google-drive',
                ]
            },
            {
                title: 'Basic Usage',
                collapsable: false,
                children: [
                    '/authorize-storage',
                    '/uploading-files',
                ]
            },
            {
                title: 'Advanced Usage',
                collapsable: false,
                children: [
                    '/storage-api',
                    '/events',
                    '/custom-managed-storage'
                ]
            }
        ]
    }
}
