const Encore = require('@symfony/webpack-encore');
const WebpackPwaManifest = require('webpack-pwa-manifest');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Directory where compiled assets will be stored
    .setOutputPath('public/build/')  // Dossier de sortie

    // Public path used by the web server to access the output path
    .setPublicPath('/public/build')  // Chemin public pour correspondre à votre serveur

    .setManifestKeyPrefix('public/build/')  // Préfixe utilisé dans le manifest

    // Enabling image loader for images referenced in CSS/JS
    .configureImageRule({
        type: 'asset',
        maxSize: 8 * 1024 // Taille max pour l'encodage inline en base64
    })

    // Add your app.js entry file
    .addStyleEntry('all-styles', [
        './assets/css/style.scss',
        'bootstrap/dist/css/bootstrap.min.css',
        '@fortawesome/fontawesome-free/css/all.min.css',
    ])

    
    
    .addEntry('all-scripts', [
        './assets/app.js',
        './assets/js/ajax.js',
        './assets/js/ajaxClient.js',
        './assets/js/filters.js',
        './assets/js/formatMontant.js',
        './assets/js/main.js',
        './assets/js/map.js',
        './assets/js/option.js',
        'bootstrap/dist/js/bootstrap.bundle.min.js',
        '@fortawesome/fontawesome-free/js/all.min.js',
    ])

    // Enable various features
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Configure Babel
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Enables Sass/SCSS support
    .enableSassLoader()
    .autoProvidejQuery()

    .addPlugin(new WebpackPwaManifest({
        name: 'Logescom-ms',
        short_name: 'Damko',
        description: 'Application de gestion commerciale.',
        background_color: '#ffffff',
        theme_color: '#317EFB',
        orientation: 'portrait',
        scope: '/',
        start_url: '/public/logescom/home/',
        id: '/appLogescom',  // Ajoute un ID unique pour ton application
        // display_override: ['fullscreen'],  // Spécifiez fullscreen ici
        // display: 'fullscreen',  // Définit le mode d'affichage principal sur fullscreen
        // display_override: ["fullscreen", "minimal-ui"],
        display: "standalone",
        display_override: ["standalone", "minimal-ui"],
        icons: [
            {
                src: path.resolve('public/images/config/logopng.png'),
                sizes: [192, 512],
                destination: path.join('icons'),
            },
            {
                src: path.resolve('public/images/config/logopng.png'),
                size: '256x256',
                destination: path.join('icons'),
            },
            {
                src: path.resolve('public/images/config/logopng.png'),
                size: '512x512',
                destination: path.join('icons'),
            },
        ],
        screenshots: [
            {
                src: '/public/images/config/screenshot-desktop.png',
                sizes: '1280x720',
                type: 'image/png',
                form_factor: 'wide'  // Capture d'écran pour desktop
            },
            {
                src: '/public/images/config/screenshot-mobile.png',
                sizes: '720x1280',
                type: 'image/png'
                // Pas de form_factor défini pour mobile
            }
        ],
        // Renomme le fichier manifest pour éviter les conflits
        filename: 'pwa-manifest.json'
    }))

    // Plugin pour copier le service worker
    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            // Le fichier `service-worker.js` sera copié dans `public/build/`
            // { from: './assets/service-worker.js', to: 'public/build/service-worker.js' }

            {
                from: path.resolve(__dirname, 'assets/service-worker.js'), // Chemin source
                to: path.resolve(__dirname, 'public/build/service-worker.js') // Chemin de destination
            }
        ]
        
    }))
;

module.exports = Encore.getWebpackConfig();
