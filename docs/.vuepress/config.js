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
                    '/dropbox',
                    '/google-drive',
                    '/configuration'
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
