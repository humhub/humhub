
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