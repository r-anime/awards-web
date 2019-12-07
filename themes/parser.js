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

async function readThemes(file) {

    return new Promise(function (resolve,reject) {
        fs.readFile(file,'utf-8', (err,fileData) => {
            if (err) throw err;
            parser(fileData, {
                comment: '#',
                skip_empty_lines: true,
            }, function (err,output) {
                if (err) throw err;
                idGetter(output);
                resolve(output);
            });
        });
    });
}

function getThemes(file) {
    var promise = readThemes(file);
    promise.then((result) => {
        themes = result;
        console.log(themes);
    });
    return themes;
}

module.exports = {
    getThemes,
}