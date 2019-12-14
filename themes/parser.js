/* eslint-disable */
const fs = require('fs');
const parser = require('csv-parse');

themes = [];

function idGetter (arr) {
    const regex = /(?!\/)\d+(?=\/)/gm;
    for (i of arr) {
        while ((m = regex.exec(i[1])) !== null) {
            // This is necessary to avoid infinite loops with zero-width matches
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            
            // The result can be accessed through the `m`-variable.
            m.forEach((match) => {
                i[1] = `${match}`;
            });
        }
      }
}

function themeType (file) {
    switch (true) {
        case file.includes('OP.csv'):
            return 'op';
        case file.includes('ED.csv'):
            return 'ed';
        case file.includes('ED.csv'):
            return 'ost';
    }
}

function themeNo (num, type) {
    if (type === 'ost') {
        return ``;
    }
    else if (num != '' && num.includes('.')) {
        const arr = num.split('.');
        return `${type.toUpperCase()}${arr[0]} v${arr[1]}`;
    }
    else if (num === '') {
        return `${type.toUpperCase()}1`;
    }
    else {
        return `${type.toUpperCase()}${num}`;
    }
}

function objectify (themeArray,file) {
    let objectArray = [];
    let type = themeType(file);
    themeArray.forEach((theme,index) => {
        objectArray[index] = {
            anime: theme[0],
            title: theme[1],
            themeType: type,
            anilistID: parseInt(theme[2]),
            themeNo: themeNo(theme[3],type),
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
                comment: '#',
                skip_empty_lines: true,
            }, function (err,output) {
                if (err) throw err;
                idGetter(output);
                objectOut = objectify(output,file);
                resolve(objectOut);
            });
        });
    });
    return objectOut;
}

module.exports = {
	readThemes,
};
