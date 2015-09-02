
                CLDR v1.6 (July 2, 2008)

This directory contains the CLDR data files in form of PHP scripts.
They are obtained by extracting the CLDR data (http://www.unicode.org/cldr/)
with the script "tools/cldr/build.php".

Only the data relevant to date and number formatting are extracted.
Each PHP file contains an array representing the data for a particular
locale. Data inherited from parent locales are also in the array.
