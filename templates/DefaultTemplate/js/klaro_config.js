// By default, Klaro will load the config from  a global "klaroConfig" variable.
// You can change this by specifying the "data-config" attribute on your
// script take, e.g. like this:
// <script src="klaro.js" data-config="myConfigVariableName" />
// You can also disable auto-loading of the consent notice by adding
// data-no-auto-load=true to the script tag.
var klaroConfig = {
    // You can customize the ID of the DIV element that Klaro will create
    // when starting up. If undefined, Klaro will use 'klaro'.
    elementID: 'klaro',

    // How Klaro should store the user's preferences. It can be either 'cookie'
    // (the default) or 'localStorage'.
    storageMethod: 'cookie',

    // You can customize the name of the cookie that Klaro uses for storing
    // user consent decisions. If undefined, Klaro will use 'klaro'.
    cookieName: 'klaro',

    // You can also set a custom expiration time for the Klaro cookie.
    // By default, it will expire after 120 days.
    cookieExpiresAfterDays: 120,

    // You can change to cookie domain for the consent manager itself.
    // Use this if you want to get consent once for multiple matching domains.
    // If undefined, Klaro will use the current domain.
    //cookieDomain: '.github.com',

//    If set to `true`, Klaro will render the texts given in the
//    `consentModal.description` and `consentNotice.description` translations as HTML.
//    This enables you to e.g. add custom links or interactive content.
    htmlTexts: false,

    // Put a link to your privacy policy here (relative or absolute).
    privacyPolicy: '/de/datenschutz/',
    // Defines the default state for applications (true=enabled by default).
    default: false,
    // If "mustConsent" is set to true, Klaro will directly display the consent
    // manager modal and not allow the user to close it before having actively
    // consented or declines the use of third-party apps.
    mustConsent: false,
    // Show "accept all" to accept all apps instead of "ok" that only accepts
    // required and "default: true" apps
    acceptAll: true,
    // replace "decline" with cookie manager modal
    hideDeclineAll: false,
    // hide "learnMore" link
    hideLearnMore: false,
    // You can define the UI language directly here. If undefined, Klaro will
    // use the value given in the global "lang" variable. If that does
    // not exist, it will use the value given in the "lang" attribute of your
    // HTML tag. If that also doesn't exist, it will use 'en'.
    //lang: 'en',

    // You can overwrite existing translations and add translations for your
    // app descriptions and purposes. See `src/translations/` for a full
    // list of translations that can be overwritten:
    // https://github.com/KIProtect/klaro/tree/master/src/translations

    // Example config that shows how to overwrite translations:
    // https://github.com/KIProtect/klaro/blob/master/src/configs/i18n.js
    translations: {
        // If you erase the "consentModal" translations, Klaro will use the
        // bundled translations.
        de: {
            consentNotice: {
                extraHTML: "<p>Diese Webseite verwendet Cookies</p>",
            },
            consentModal: {
                title: 'Diese Webseite verwendet Cookies',
                description:
                    'Hier können Sie einsehen, welche Informationen wir sammeln.',
                extraHTML: '<a href="/de/impressum">Impressum</a>'
            },
            klaro: {
                description: 'Anzeige und Verwaltung der gespeicherten Informationen',
            },
            mathCaptcha: {
                description: 'Captcha für die Absicherung des Kontaktformulares',
            },
            purposes: {
                analytics: 'Besucher-Statistiken',
                security: 'Sicherheit',
                livechat: 'Live Chat',
                advertising: 'Anzeigen von Werbung',
                styling: 'Styling',
            },
        },
        en: {
            consentNotice: {
                // uncomment and edit this to add extra HTML to the consent notice below the main text
                // extraHTML: "<p>Please look at our <a href=\"#imprint\">imprint</a> for further information.</p>",
            },
            consentModal: {
                // uncomment and edit this to add extra HTML to the consent modal below the main text
                // extraHTML: "<p>This is additional HTML that can be freely defined.</p>",
                description:
                    'Here you can see the information that we collect.',
                extraHTML: '<a href="/en/impressum/">Imprint</a>'
            },
            klaro: {
                description: 'Manage consent on this website',
            },
            mathCaptcha: {
                description: 'Captcha to protect contact form',
            },
            purposes: {
                analytics: 'Analytics',
                security: 'Security',
                livechat: 'Livechat',
                advertising: 'Advertising',
                styling: 'Styling',
            },
        },
    },

    // This is a list of third-party apps that Klaro will manage for you.
    services: [
        // The apps will appear in the modal in the same order as defined here.
        {
            name: 'klaro',
            title: 'Klaro Consent',
            purposes: ['security'],
            required: true,
        },
        {
            name: 'mathCaptcha',
            title: 'Math Captcha',
            purposes: ['security'],
            required: true,
        },
    ],
};

/*
consentModal:
  title: Informationen, die wir speichern
  description: >
    Hier können Sie einsehen und anpassen, welche Information wir über Sie speichern.
  privacyPolicy:
    name: Datenschutzerklärung
    text: >
      Weitere Details finden Sie in unserer {privacyPolicy}.
consentNotice:
  changeDescription: Es gab Änderungen seit Ihrem letzten Besuch, bitte aktualisieren Sie Ihre Auswahl.
  description: >
    Wir speichern und verarbeiten Ihre personenbezogenen Informationen für folgende Zwecke: {purposes}.
  learnMore: Mehr erfahren
  privacyPolicy:
    name: Datenschutzerklärung
  imprint:
    name: Impressum
ok: OK
save: Speichern
decline: Ablehnen
close: Schließen
acceptSelected: Auswahl speichern
acceptAll: Allen zustimmen
app:
  disableAll:
    title: Alle Anwendungen aktivieren/deaktivieren
    description: Nutzen Sie diesen Schalter, um alle Apps zu aktivieren/deaktivieren.
  optOut:
    title: (Opt-Out)
    description: Diese Anwendung wird standardmäßig geladen (Sie können diese aber deaktivieren)
  required:
    title: (immer notwendig)
    description: Diese Anwendung wird immer benötigt
  purposes: Zwecke
  purpose: Zweck
poweredBy: Realisiert mit Klaro!
*/
