/*
Standardmäßig lädt Klaro die Konfiguration aus einer globalen
'klaroConfig'-Variablen. Sie können dies ändern, indem Sie das Attribut 'data-
klaro-config' in Ihrem Skript angeben: <script src="klaro.js" data-klaro-
config="myConfigVariableName"
*/
var klaroConfig = {
    /*
    Die Einstellung von "testing" auf "true" bewirkt, dass Klaro die
    Einverständniserklärung oder modal standardmäßig nicht anzeigt, außer wenn ein
    spezieller Hash-Tag an die URL angehängt ist (#klaro-testing). Dadurch ist es
    möglich, Klaro auf Ihrer Live-Website zu testen, ohne normale Besucher zu
    beeinträchtigen.
    */
    testing: false,

    /*
    Sie können die ID des DIV-Elements, das Klaro beim Start erstellt, anpassen.
    Standardmäßig wird Klaro 'klaro' verwenden.
    */
    elementID: 'klaro',

    /*
    Sie können anpassen, wie Klaro die Zustimmungsinformationen im Browser
    speichert. Geben Sie entweder 'cookie' (Standard) oder 'localStorage' an.
    */
    storageMethod: 'cookie',

    /*
    Sie können den Namen des Cookies oder des localStorage-Eintrags anpassen, den
    Klaro zum Speichern der Zustimmungsinformationen verwenden wird. Standardmäßig
    wird Klaro 'klaro' verwenden.
    */
    storageName: 'klaro',

    /*
    Wenn auf `true` gesetzt, gibt Klaro die in den `consentModal.description` und
    `consentNotice.description` Übersetzungen angegebenen Texte als HTML wieder. Auf
    diese Weise können Sie z.B. benutzerdefinierte Links oder interaktive Inhalte
    hinzufügen.
    */
    htmlTexts: false,

    /*
    Sie können die Cookie-Domäne für den consent manager selbst ändern. Verwenden
    Sie dies, wenn Sie die Zustimmung einmal für mehrere übereinstimmende Domänen
    erhalten möchten. Standardmäßig wird Klaro die aktuelle Domäne verwenden. Nur
    relevant, wenn 'storageMethod' auf 'cookie' gesetzt ist.
    */
    cookieDomain: '.example.com',

    /*
    Sie können auch eine benutzerdefinierte Verfallszeit für das Klaro-Cookie
    festlegen. Standardmäßig läuft es nach 30 Tagen ab. Nur relevant, wenn
    'storageMethod' auf 'cookie' gesetzt ist.
    */
    cookieExpiresAfterDays: 30,

    /*
    Definiert den Standardzustand für Dienste im Zustimmungsmodus (standardmäßig
    true=aktiviert). Sie können diese Einstellung in jedem Dienst außer Kraft
    setzen.
    */
    default: false,

    /*
    Wenn 'mustConsent' auf 'true' gesetzt ist, zeigt Klaro den consent manager modal
    direkt an und erlaubt dem Benutzer nicht, ihn zu schließen, bevor er der Nutzung
    von Diensten Dritter nicht aktiv zugestimmt oder diese abgelehnt hat.
    */
    mustConsent: false,

    /*
    Wenn Sie "acceptAll" auf "true" setzen, wird in der Benachrichtigung und im
    Modal eine Schaltfläche "accept all" angezeigt, die alle Dienste von
    Drittanbietern aktiviert, wenn der Benutzer darauf klickt. Wenn auf "false"
    gesetzt, wird eine Schaltfläche "accept" angezeigt, die nur die Dienste
    aktiviert, die im Zustimmungsmodus aktiviert sind.
    */
    acceptAll: true,

    /*
    Wenn Sie 'hideDeclineAll' auf 'true' setzen, wird die Schaltfläche "decline" im
    Zustimmungsmodal ausgeblendet und der Benutzer gezwungen, das Modal zu öffnen,
    um seine Zustimmung zu ändern oder alle Dienste Dritter zu deaktivieren. Wir
    raten Ihnen dringend davon ab, diese Funktion zu verwenden, da sie den
    Prinzipien "privacy by default" und "privacy by design" des GDPR widerspricht
    (aber in anderen Gesetzgebungen, wie z.B. dem CCPA, akzeptabel sein könnte).
    */
    hideDeclineAll: false,

    /*
    Wenn Sie 'hideLearnMore' auf 'true' setzen, wird der Link "mehr erfahren /
    anpassen" in der Einverständniserklärung ausgeblendet. Wir raten dringend davon
    ab, dies unter den meisten Umständen zu verwenden, da es den Benutzer davon
    abhält, seine Einverständniserklärung anzupassen.
    */
    hideLearnMore: false,

    /*
    Sie können vorhandene Übersetzungen überschreiben und Übersetzungen für Ihre
    Dienstbeschreibungen und Zwecke hinzufügen. Eine vollständige Liste der
    Übersetzungen, die überschrieben werden können, finden Sie unter
    `src/translations/` :
    https://github.com/KIProtect/klaro/tree/master/src/translations
    */
    translations: {
        /*
        Der Schlüssel `zz` enthält Standardübersetzungen, die als Ersatzwerte verwendet
            werden. Dies kann z.B. nützlich sein, um eine Fallback-URL für
            Datenschutzrichtlinien zu definieren.
        */
        zz: {
            privacyPolicyUrl: '/privacy',

        }
        de: {
            /*
            Sie können hier einen sprachspezifischen Link zu Ihrer Datenschutzrichtlinie
            angeben.
            */
            privacyPolicyUrl: '/datenschutz',
            consentNotice: {
                description: 'Dieser Text wird in der Einwilligungsbox erscheinen.',
            },
            consentModal: {
                description:
                    'Hier können Sie einsehen und anpassen, welche Information wir über Sie ' + 
                    'sammeln. Einträge die als "Beispiel" gekennzeichnet sind dienen lediglich ' + 
                    'zu Demonstrationszwecken und werden nicht wirklich verwendet.',
            },
            /*
            Sie sollten auch Übersetzungen für jeden Zweck, den Sie im Abschnitt
            "Dienstleistungen" definieren, definieren. Sie können einen Titel und eine
            (optionale) Beschreibung definieren.
            */
            purposes: {
                analytics: {
                    title: 'Besucher-Statistiken'
                },
                security: {
                    title: 'Sicherheit'
                },
                livechat: {
                    title: 'Live Chat'
                },
                advertising: {
                    title: 'Anzeigen von Werbung'
                },
                styling: {
                    title: 'Styling'
                },
            },
        },
        en: {
            privacyPolicyUrl: '/privacy',
            consentModal: {
                description:
                    'Here you can see and customize the information that we collect about you. ' + 
                    'Entries marked as "Example" are just for demonstration purposes and are not ' + 
                    'really used on this website.',
            },
            purposes: {
                analytics: {
                    title: 'Analytics'
                },
                security: {
                    title: 'Security'
                },
                livechat: {
                    title: 'Livechat'
                },
                advertising: {
                    title: 'Advertising'
                },
                styling: {
                    title: 'Styling'
                },
            },
        },
    },

    /*
    Hier geben Sie an, welche Drittanbieterdienste Klaro für Sie verwalten wird.
    */
    services: [
        {

            /*
            Jeder Dienst muss einen eindeutigen Namen haben. Klaro sucht nach HTML-Elementen
            mit einem passenden Attribut "data-name", um Elemente zu identifizieren, die zu
            diesem Dienst gehören.
            */
            name: 'matomo',

            /*
            Wenn 'default' auf 'true' gesetzt ist, wird der Dienst standardmäßig aktiviert.
            Dadurch wird die globale 'Standard'-Einstellung außer Kraft gesetzt.
            */
            default: true,

            /*
            Übersetzungen, die zu diesem Dienst gehören, finden Sie hier. Der Schlüssel `zz`
            enthält Standardübersetzungen, die als Ausweichlösung verwendet werden, wenn für
            eine bestimmte Sprache keine Übersetzungen definiert sind.
            */
            translations: {
                zz: {
                    title: 'Matomo/Piwik'
                },
                en: {
                    description: 'Matomo is a simple, self-hosted analytics service.'
                },
                de: {
                    description: 'Matomo ist ein einfacher, selbstgehosteter Analytics-Service.'
                },
            },
            /*
            Der/die Zweck(e) dieses Dienstes, der/die auf der Einverständniserklärung
            aufgeführt wird/werden. Vergessen Sie nicht, Übersetzungen für alle Zwecke, die
            Sie hier aufführen, hinzuzufügen.
            */
            purposes: ['analytics'],

            cookies: [
                /*
                entweder nur einen Cookie-Namen oder einen regulären Ausdruck (regex) oder eine
                Liste bestehend aus einem Namen oder einem regulären Ausdruck, einem Pfad und
                einer Cookie-Domäne angeben. Die Angabe eines Pfades und einer Domäne ist
                erforderlich, wenn Sie Dienste haben, die Cookies für einen Pfad, der nicht "/"
                ist, oder eine Domäne, die nicht die aktuelle Domäne ist, setzen. Wenn Sie diese
                Werte nicht richtig setzen, kann das Cookie von Klaro nicht gelöscht werden, da
                es keine Möglichkeit gibt, auf den Pfad oder die Domäne eines Cookies in JS
                zuzugreifen. Beachten Sie, dass es nicht möglich ist, Cookies zu löschen, die
                auf einer Domäne eines Drittanbieters gesetzt wurden, oder Cookies, die das
                Attribut HTTPOnly haben: https://developer.mozilla.org/en-
                US/docs/Web/API/Document/cookie#new-cookie_domain
                */

                /*
                Diese Regel passt auf Cookies, die die Zeichenfolge '_pk_' enthalten und die auf
                den Pfad '/' und die Domäne 'klaro.kiprotect.com' gesetzt sind.
                */
                [/^_pk_.*$/, '/', 'klaro.kiprotect.com'],

                /*
                Dasselbe wie oben, nur für die Domäne 'localhost'.
                */
                [/^_pk_.*$/, '/', 'localhost'],

                /*
                Diese Regel trifft auf alle Cookies mit dem Namen 'piwik_ignore' zu, die auf dem
                Pfad '/' auf der aktuellen Domain gesetzt sind
                */
                'piwik_ignore',
            ],

            /*
            Sie können eine optionale Rückruf-Funktion definieren, die jedes Mal aufgerufen
            wird, wenn sich der Zustimmungsstatus für den gegebenen Dienst ändert. Der
            Zustimmungswert wird als erster Parameter an die Funktion übergeben
            (true=zustimmend). Die Konfiguration `service` wird als zweiter Parameter
            übergeben.
            */
            callback: function(consent, service) {
                console.log(
                    'User consent for service ' + service.name + ': consent=' + consent
                );
            },

            /*
            Wenn 'erforderlich' auf 'wahr' gesetzt ist, lässt Klaro nicht zu, dass dieser
            Dienst vom Benutzer deaktiviert wird. Verwenden Sie dies für Dienste, die für
            das Funktionieren Ihrer Website immer erforderlich sind (z.B. Warenkorb-
            Cookies).
            */
            required: false,

            /*
            Wenn 'optOut' auf 'true' gesetzt ist, lädt Klaro diesen Dienst, noch bevor der
            Benutzer seine ausdrückliche Zustimmung gegeben hat. Wir raten dringend davon
            ab.
            */
            optOut: false,

            /*
            Wenn 'onlyOnce' auf 'true' gesetzt ist, wird der Dienst nur einmal ausgeführt,
            unabhängig davon, wie oft der Benutzer ihn ein- und ausschaltet. Dies ist z.B.
            für die Verfolgung von Skripten relevant, die jedes Mal neue
            Seitenaufrufereignisse erzeugen würden, wenn Klaro sie aufgrund einer
            Zustimmungsänderung durch den Benutzer deaktiviert und wieder aktiviert.
            */
            onlyOnce: true,
        },
        {
            name: 'youtube',
            /*
            [no translation for key klaro.annotated-config.services.contextualConsentOnly]
            */
            contextualConsentOnly: true,
        },
    ],

    /*
    Sie können eine optionale Rückruf-Funktion definieren, die jedes Mal aufgerufen
    wird, wenn sich der Zustimmungsstatus für einen bestimmten Dienst ändert. Der
    Zustimmungswert wird als erster Parameter an die Funktion übergeben
    (true=zustimmend). Die Konfiguration `service` wird als zweiter Parameter
    übergeben.
    */
    callback: function(consent, service) {
        console.log(
            'User consent for service ' + service.name + ': consent=' + consent
        );
    },

};