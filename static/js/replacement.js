require ([
    'event_utils'
], function (
    eventUtils
) {
    /*
     * The name selector looks as follows:
     * <span
     *     class="ofbi-schalter"
     *     data-prefix1=""
     *     data-pattern1="DU"
     *     data-suffix1=""
     *     data-prefix2=""
     *     data-pattern2="Unser Herr"
     *     data-suffix2=""
     *     v-- this element is added once we do a replacement
     *     data-original="<span>Herr</span>">
     *         ^Herr^
     * </span>
     *
     * 1 means ich/du/er replacements
     * 2 means unser/euer replacements
     */

    /*
     * This function encodes and returns the given text in an HTML safe way.
     */
    function htmlspecialchars (text) {
        var elem = document.createElement ("span");
        elem.appendChild (document.createTextNode (text || ''));
        return elem.innerHTML;
    }

    /*
     * Base class for all replacement types.
     * number = Replacement type. 1 = ich/du/er, 2 = unser/euer
     */
    function Ersatzlesung (number) {
        this.number = number;

        /*
         * Returns the pattern of this replacement.
         */
        this.pattern = function (obj) {
            return htmlspecialchars (obj.getAttribute ("data-pattern" + this.number));
        }
        /*
         * Returns the prefix of this replacement.
         */
        this.prefix = function (obj) {
            return htmlspecialchars (obj.getAttribute ("data-prefix" + this.number));
        }
        /*
         * Returns the suffix of this replacement.
         */
        this.suffix = function (obj) {
            return htmlspecialchars (obj.getAttribute ("data-suffix" + this.number));
        }

        /*
         * Puts link and span elements around the argument and returns it.
         */
        this.span_name = function (ersatzlesung) {
            return htmlspecialchars (ersatzlesung);
        }

        /*
         * Returns whether the given element is a genitive.
         */
        this.isGenitiv = function (obj) {
            return /^(?:[Uu]nseres|[Ee]ures) Herrn$/.test (obj.getAttribute ("data-pattern2"));
        }

        /*
         * Returns the respective replacement.
         */
        this.ersatzlesung = function (obj) {
            return this.span_name (this.pattern (obj));
        }

        /*
         * Perform this replacement on the given element.
         */
        this.replaceName = function (obj) {
            obj.innerHTML = '⸂' + this.prefix (obj) + this.ersatzlesung (obj) + this.suffix (obj) + '⸃';
        }
    }

    /*
     * Here follows a list of all possible replacement types.
     */

    ErsatzlesungName.prototype = new Ersatzlesung (1);
    function ErsatzlesungName (name) {
        this.name = name;
        this.ersatzlesung = function (obj) {
            if (this.isGenitiv (obj)) {
                return this.span_name (this.name) + "s";
            } else {
                return this.span_name (this.name);
            }
        }
    }

    ErsatzlesungGott.prototype = new Ersatzlesung (1);
    function ErsatzlesungGott () {
        this.ersatzlesung = function (obj) {
            if (this.isGenitiv (obj)) {
                return this.span_name ("Gottes");
            } else {
                return this.span_name ("Gott");
            }
        }
    }

    ErsatzlesungUnserGott.prototype = new Ersatzlesung (2);
    function ErsatzlesungUnserGott () {
        this.ersatzlesung = function (obj) {
            if (this.isGenitiv (obj)) {
                return this.span_name (this.pattern (obj).replace (/ Herrn$/, " Gottes"));
            } else {
                return this.span_name (this.pattern (obj).replace (/ Herrn$/, " Gott").replace (/ Herr$/, " Gott"));
            }
        }
    }

    ErsatzlesungDer.prototype = new Ersatzlesung (2);
    function ErsatzlesungDer (name, r) {
        this.name = name;
        this.r = r;
        this.ersatzlesung = function (obj) {
            if ("DU" == this.pattern (obj)) {
                return this.span_name (this.name + this.r);
            } else {
                return this.span_name (
                        this.pattern (obj)
                        .replace(/^(?:unser|euer) /, "der ").replace(/^(?:Unser|Euer) /, "Der ")
                        .replace (/^(?:unsere|eure)([nms]) /, "de$1 ").replace (/^(?:Unsere|Eure)([nms]) /, "De$1 ")
                        .replace (/ Herrn$/, " " + this.name + "n").replace (/ Herr$/, " " + this.name)
                        );
            }
        }
    }

    ErsatzlesungHebräisch.prototype = new Ersatzlesung (2);
    function ErsatzlesungHebräisch (name) {
        this.name = name;
        this.ersatzlesung = function (obj) {
            if (this.isGenitiv (obj)) {
                return "von " + this.span_name (this.name);
            } else {
                return this.span_name (this.name);
            }
        }
    }

    function ErsatzlesungOriginal () {
        this.replaceName = function (obj) {
            obj.innerHTML = obj.getAttribute( 'data-original' );
        }
    }

    /*
     * Map of all replacement types.
     */
    Ersatzlesungen = {
        "gemischt" : new ErsatzlesungOriginal,
        "יהוה" : new ErsatzlesungHebräisch ("יהוה"),
        "JHWH" : new ErsatzlesungName ("JHWH"),
        "Jahwe" : new ErsatzlesungName ("Jahwe"),
        "Jaho" : new ErsatzlesungName ("Jaho"),

        "Gott" : new ErsatzlesungGott,
        "der Herr" : new ErsatzlesungDer ("Herr", ""),
        "der Ewige" : new ErsatzlesungDer ("Ewige", "r"),
        "Ich/Du/Er" : new Ersatzlesung (1),
        "Ich-Bin-Da" : new ErsatzlesungName ("Ich-Bin-Da"),

        "Unser/Euer Gott" : new ErsatzlesungUnserGott,
        "Unser/Euer Herr" : new Ersatzlesung (2),
        "Adonai" : new ErsatzlesungHebräisch ("Adonai"),
        "Ha-Schem" : new ErsatzlesungHebräisch ("Ha-Schem"),
    };
    /*
     * Currently selected replacement. Will be highlighted in dropdown.
     */
    var currentErsatzlesung = "ofbi-replace-gemischt";

    function replaceClassNameForErsatzlesung( ersatzlesung ) {
        return 'ofbi-replace-' + ersatzlesung.replace( /[\0\s\f\n\r\t\v\/]/g, "-" );
    }

    /*
     * Perform the replacement for the given name on all switches on the page.
     * Also sets the Ersatzlesung variable to its new value.
     */
    function replaceAllNames (div, neuerName) {
        [].forEach.call( document.querySelectorAll( '.ofbi-schalter' ), function( elem, index ) {
            Ersatzlesungen [neuerName].replaceName ( elem );
        });

        currentErsatzlesung = replaceClassNameForErsatzlesung( neuerName );
    }

    eventUtils.onReady( function() {
        // Fill the data-original attribue in all switch elements.
        [].forEach.call( document.querySelectorAll( '.ofbi-schalter' ), function ( elem ) {
            elem.setAttribute( "data-original", elem.innerHTML );
            elem.addEventListener( 'click', function() {
                var div = document.createElement('div');
                // Fill in the replacements in the replacement dropdown.
                var html = "<div>";
                var count = 0;
                for( var ersatzlesung in Ersatzlesungen ) {
                    var id_tag, replacement_name;
                    if( count++ >= 5 ) {
                        html += "</div><div>";
                        count = 1;
                    }
                    class_tag = replaceClassNameForErsatzlesung( ersatzlesung );
                    replacement_name = htmlspecialchars( ersatzlesung );
                    html += '<p><a class="' + class_tag + '">' + replacement_name + '</a></p>';
                }
                html += '</div>';
                html += '<p><small>Hier steht im Urtext der Gottesname <a href=/wiki/JHWH>JHWH</a>,';
                html += '<br/>dessen genaue Aussprache unbekannt ist und';
                html += '<br/>der im Christentum und Judentum meistens';
                html += '<br/>durch eine Ersatzlesung wiedergegeben wird.</small></p>';
                div.innerHTML = html;
                div.querySelector( '.' + currentErsatzlesung ).style.fontWeight = 'bold';

                // Now that the dropdown content is duplicated to every button we set the handlers.
                for( var ersatzlesung in Ersatzlesungen ) {
                    var class_tag = replaceClassNameForErsatzlesung( ersatzlesung );
                    div.querySelector( '.' + class_tag ).addEventListener('click', function() {
                        var closureErsatzlesung = ersatzlesung;
                        var closureDiv = div;
                        return function() {
                            replaceAllNames( closureDiv, closureErsatzlesung );
                            closureDiv.parentElement.removeChild(closureDiv);
                        }
                    }() );
                }
                div.style.position = 'absolute';
                div.style.border = '1px solid #ccc';
                div.style.borderRadius = '1em';
                div.style.padding = '1em';
                div.style.zIndex = '99';
                div.style.background = 'rgba(255,255,255,.8)';
                elem.parentElement.insertBefore(div, elem);
            });
        });
    });
});

