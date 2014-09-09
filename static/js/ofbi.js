require  ([ "jquery"],
function  ( $       ) {
    /*
     * The name selector looks as follows:
     * <span
     *     class="schalter"
     *     data-prefix1=""
     *     data-pattern1="DU"
     *     data-suffix1=""
     *     data-prefix2=""
     *     data-pattern2="Unser Herr"
     *     data-suffix2=""
     *     v-- this element is added once we do a replacement
     *     data-original="<a class="name"><span>Herr</span></a>">
     *         <a class="name">
     *             <span>Herr</span>
     *         </a>
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
            return "<a class=name><span>" + htmlspecialchars (ersatzlesung) + "</span></a>";
        }

        /*
         * Returns whether the given element is a genitive.
         */
        this.isGenitiv = function (obj) {
            return /^(?:[Uu]nseres|[Ee]ures) Herrn$/.test (this.pattern (obj));
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
            obj.innerHTML =  this.prefix (obj) + this.ersatzlesung (obj) + this.suffix (obj);
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
            obj.innerHTML = obj.getAttribute ("data-original");
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
    var Ersatzlesung = "gemischt";

    /*
     * Perform the replacement for the given name on all switches on the page.
     * Also sets the Ersatzlesung variable to its new value.
     */
    function replaceAllNames (neuerName) {
        $( ".schalter" ).each( function( index ) {
            Ersatzlesungen [neuerName].replaceName ( this );
        });
        close ();

        $( "#" + Ersatzlesung ).style.fontWeight = "normal";
        Ersatzlesung = neuerName.replace (/[\0\s\f\n\r\t\v]/, "_");
        setJavascriptFunctions();
    }

    /*
     * Enumerate the switches, set event handlers on them and fill in a title in each of them.
     */
    function setJavascriptFunctions () {
        $( ".name" ).each(function ( index ) {
            this.setAttribute ("id", "ersatzlesung" + index);
            this.setAttribute ("href", "Javascript:showErsatzlesungen('ersatzlesung" + index + "')");
            this.setAttribute ("title", "Hier steht im Urtext der Gottesname JHWH. Für weitere Information und zum Ändern der Ersatzlesung bitte klicken.");
        });
    }

    /*
     * Activate/Deactivate the replacement dialog on the given element.
     */
    function showErsatzlesungen (id) {
        var el = $( "#Ersatzlesungen" );
        if (el.style.visibility == "visible") {
            close ();
        } else {
            var obj = $( "#" + id );
            var left = obj.offsetLeft;
            var top = obj.offsetTop;
            for (var parent = obj.offsetParent; parent.tagName != "BODY"; parent = parent.offsetParent) {
                left += parent.offsetLeft;
                top += parent.offsetTop;
            }

            var maxleft = $( "html" ).get( 0 ).offsetWidth - el.offsetWidth - 10;
            if (maxleft <= 0) {
                el.style.left = "0";
                left = 0;
            } else if (maxleft < left) {
                el.style.left = maxleft + "px";
            } else {
                el.style.left = left + "px";
            }

            el.style.top = (top + obj.offsetHeight) + "px";
            el.style.visibility = "visible";
            $( "#beginErsatzlesungen" ).focus();
        }
        $( "#" + Ersatzlesung ).style.fontWeight="bold";
    }

    function changeLink (ersatzlesung) {
        return "<p><a id=\""  + htmlspecialchars (ersatzlesung.replace (/[\0\s\f\n\r\t\v]/, "_")) + "\" href='Javascript:replaceAllNames(\""  + htmlspecialchars (ersatzlesung) + "\")'>" + htmlspecialchars (ersatzlesung) + "</a></p>";
    }

    function close () {
        el = document.getElementById ("Ersatzlesungen");
        el.style.visibility = "hidden";
        el.style.left = 0;
        el.style.top = 0;
    }

    $(function () {
        var html = "<p><span id=beginErsatzlesungen tabindex=-1>Ersatzlesung auswählen:</span>";
        html += "<div>";
        var count = 0;
        for (var ersatzlesung in Ersatzlesungen) {
            if (count++ >= 5) {
                html += "</div><div>";
                count = 1;
            }
            html += changeLink (ersatzlesung);
        }
        html += "</div>";
        html += "<p>Hier steht im Urtext der Gottesname <a href=/wiki/?title=JHWH>JHWH</a>,<br/>dessen genaue Aussprache unbekannt ist und<br/>der im Christentum und Judentum meistens<br/>durch eine Ersatzlesung wiedergegeben wird.";
        html += "<p><a href='Javascript:close()'>schließen</a>";

        $( "#Ersatzlesungen" ).html( html );

        // Fill the data-original attribue in all switch elements.
        $( ".schalter" ).each( function ( index ) {
            this.setAttribute( "data-original", this.innerHTML);
        });

        setJavascriptFunctions();
    })();
});

