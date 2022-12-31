/* eslint-disable */
const fs = require('fs');
const parser = require('csv-parse');

themes = [];

function idGetter (arr) {
    const regex = /\/[0-9]+/gm;
    for (i of arr) {
        while ((m = regex.exec(i[2])) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            
            // The result can be accessed through the `m`-variable.
            m.forEach((match) => {
                i[2] = `${match}`;
                i[2] = i[2].substring(1, i[2].length);
                i[2] = parseInt(i[2]);
            });
        }
      }
}

function themeType (inputTheme) {
    switch (true) {
        case inputTheme.includes('OP'):
            return 'op';
        case inputTheme.includes('ED'):
            return 'ed';
        default:
            return '';
    }
}

function objectify (themeArray) {
    let objectArray = [];
    themeArray.forEach((theme,index) => {
        objectArray[index] = {
            anime: theme[0],
            title: theme[1],
            themeType: themeType(theme[3]),
            anilistID: theme[2],
            themeNo: theme[3],
            link: theme[4],
        };
    });
    return objectArray;
}

async function readThemes(file) {
    let objectOut = [];
    await new Promise(function (resolve,reject) {
        fs.readFile(file,'utf-8', (err,fileData) => {
            if (err) throw err;
            parser(fileData, {
                skip_empty_lines: true,
            }, function (error,output) {
                if (error) throw error;
                // idGetter(output);
                objectOut = objectify(output);
                resolve(objectOut);
            });
        });
    });
    return objectOut;
}

module.exports = {
	readThemes,
};
