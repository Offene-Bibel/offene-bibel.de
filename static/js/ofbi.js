function htmlspecialchars (text) {
    var elem = document.createElement ("span");
    elem.appendChild (document.createTextNode (text || ''));
    return elem.innerHTML;
}

function Ersatzlesung (number) {
    this.number = number;

    this.pattern = function (obj, number) {
        return htmlspecialchars (obj.getAttribute ("data-pattern" + number));
    }
    this.prefix = function (obj) {
        return htmlspecialchars (obj.getAttribute ("data-prefix" + this.number));
    }
    this.suffix = function (obj) {
        return htmlspecialchars (obj.getAttribute ("data-suffix" + this.number));
    }

    this.span_name = function (ersatzlesung) {
        return "<a class=name><span>" + htmlspecialchars (ersatzlesung) + "</span></a>";
    }

    this.isGenitiv = function (obj) {
        return /^(?:[Uu]nseres|[Ee]ures) Herrn$/.test (this.pattern (obj, 2));
    }

    this.ersatzlesung = function (obj) {
        return this.span_name (this.pattern (obj, this.number));
    }

    this.replaceName = function (obj) {
        obj.innerHTML =  this.prefix (obj) + this.ersatzlesung (obj) + this.suffix (obj);
    }
}

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
            return this.span_name (this.pattern (obj, 2).replace (/ Herrn$/, " Gottes"));
        } else {
            return this.span_name (this.pattern (obj, 2).replace (/ Herrn$/, " Gott").replace (/ Herr$/, " Gott"));
        }
    }
}

ErsatzlesungDer.prototype = new Ersatzlesung (2);
function ErsatzlesungDer (name, r) {
    this.name = name;
    this.r = r;
    this.ersatzlesung = function (obj) {
        if ("DU" == this.pattern (obj, 1)) {
            return this.span_name (this.name + this.r);
        } else {
            return this.span_name (
                    this.pattern (obj, 2)
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
var Ersatzlesung = "gemischt";

function replaceAllNames (neuerName) {
    var schalterArr = document.getElementsByClassName ("schalter");
    for (var i = 0; i < schalterArr.length; i++) {
        Ersatzlesungen [neuerName].replaceName (schalterArr [i]);
    }
    close ();

    document.getElementById (Ersatzlesung).style.fontWeight="normal";
    Ersatzlesung = neuerName.replace (/[\0\s\f\n\r\t\v]/, "_");
    setJavascriptFunctions();
}

function setOriginalName () {
    var schalterArr = document.getElementsByClassName ("schalter");
    var i = schalterArr.length;
    while (i--) {
        schalterArr [i].setAttribute ("data-original", schalterArr [i].innerHTML);
    }
}

function setJavascriptFunctions () {
    var nameArr = document.getElementsByClassName ("name");
    var i = nameArr.length;
    while (i--) {
        nameArr [i].setAttribute ("id", "ersatzlesung" + i);
        nameArr [i].setAttribute ("href", "Javascript:showErsatzlesungen('ersatzlesung" + i + "')");
        nameArr [i].setAttribute ("title", "Hier steht im Urtext der Gottesname JHWH. Für weitere Information und zum Ändern der Ersatzlesung bitte klicken.");
    }
}

function showErsatzlesungen (id) {
    var el = document.getElementById ("Ersatzlesungen");
    if (el.style.visibility == "visible") {
        close ();
    } else {
        var obj = document.getElementById (id);
        var left = obj.offsetLeft;
        var top = obj.offsetTop;
        for (var parent = obj.offsetParent; parent.tagName != "BODY"; parent = parent.offsetParent) {
            left += parent.offsetLeft;
            top += parent.offsetTop;
        }

        var maxleft = document.getElementsByTagName("html")[0].offsetWidth - document.getElementById ("Ersatzlesungen").offsetWidth - 10;
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
        document.getElementById ("beginErsatzlesungen").focus();
    }
    document.getElementById (Ersatzlesung).style.fontWeight="bold";
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

(function () {
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

    document.getElementById ("Ersatzlesungen").innerHTML = html;

    setOriginalName();
    setJavascriptFunctions();
})();

