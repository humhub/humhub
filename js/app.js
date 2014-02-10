/**
 * Holds all already loaded javascript libaries
 * 
 * @type HashTable
 */
var currentLoadedJavaScripts = new HashTable();


/**
 * Looks for script tags inside the given string and checks if the files
 * are already loaded.
 * 
 * When already loaded, the scripts will ignored.
 * 
 * @returns {undefined}
 */
function parseHtml(htmlString) {
    var re = /<script type="text\/javascript" src="([\s\S]*?)"><\/script>/gm;

    var match;
    while (match = re.exec(htmlString)) {

        js = match[1];

        if (currentLoadedJavaScripts.hasItem(js)) {
            // Remove Script Tag
            //console.log("Ignore load of : "+js);
            htmlString = htmlString.replace(match[0], "");

        } else {
            // Let Script Tag
            //console.log("First load of: "+js);
            currentLoadedJavaScripts.setItem(js, 1);

        }

    }
    return htmlString;
}



/**
 * Hashtable
 * 
 * Javscript Class which represents a hashtable. 
 * 
 * @param {type} obj
 * @returns {HashTable}
 */
function HashTable(obj)
{
    this.length = 0;
    this.items = {};
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            this.items[p] = obj[p];
            this.length++;
        }
    }

    this.setItem = function(key, value)
    {
        var previous = undefined;
        if (this.hasItem(key)) {
            previous = this.items[key];
        }
        else {
            this.length++;
        }
        this.items[key] = value;
        return previous;
    }

    this.getItem = function(key) {
        return this.hasItem(key) ? this.items[key] : undefined;
    }

    this.hasItem = function(key)
    {
        return this.items.hasOwnProperty(key);
    }

    this.removeItem = function(key)
    {
        if (this.hasItem(key)) {
            previous = this.items[key];
            this.length--;
            delete this.items[key];
            return previous;
        }
        else {
            return undefined;
        }
    }

    this.keys = function()
    {
        var keys = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                keys.push(k);
            }
        }
        return keys;
    }

    this.values = function()
    {
        var values = [];
        for (var k in this.items) {
            if (this.hasItem(k)) {
                values.push(this.items[k]);
            }
        }
        return values;
    }

    this.each = function(fn) {
        for (var k in this.items) {
            if (this.hasItem(k)) {
                fn(k, this.items[k]);
            }
        }
    }

    this.clear = function()
    {
        this.items = {}
        this.length = 0;
    }
}